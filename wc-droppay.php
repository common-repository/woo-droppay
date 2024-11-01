<?php
if (!defined('ABSPATH')) {
  exit;
}

class WC_Droppay extends WC_Payment_Gateway
{
    private $logger;

    public function __construct()
    {
        $this->plugin_name          = 'droppay';
        $this->id                   = 'droppay';
        $this->order_key            = '';
        $this->icon                 = WC_HTTPS::force_https_url(plugins_url('/logo.png', __FILE__));
        $this->droppayUrl           = 'https://checkout.drop-pay.com/v1/authorization/';
        $this->has_fields           = true;
        $this->method_title         = __('DropPay', 'woo-droppay');
        $this->order_button_text    = __('Proceed to DropPay', 'woo-droppay');
        $this->method_description   = __('With DropPay you can send and receive money with your smartphone and make payments safely.', 'woo-droppay');
        $this->has_fields           = false;
        $this->supports             = array('products', 'refunds');
        $this->title                = __('DropPay', 'woo-droppay');
        $this->description          = $this->method_description;

        $this->init_form_fields();
        $this->init_settings();

        if (class_exists('DroppayLogger')) {
            $this->logger = new DroppayLogger();
        }

        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        add_action('woocommerce_update_options_payment_gateways_droppay', array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts' ) );
    }

    /**
     * @return string
     */
    public function get_id()
    {
        return $this->id;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'woo-droppay'),
                'label'       => __('Enable DropPay', 'woo-droppay'),
                'type'        => 'checkbox',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => __( 'DropPay', 'woo-droppay' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( 'DropPay', 'woocommerce' ),
                'desc_tip'    => true,
              ),
            'applicationKey' => array(
                'title'       => __('Application Key', 'woo-droppay'),
                'type'        => 'text'
            ),
            'applicationSecret' => array(
                'title'       => __('Application Secret', 'woo-droppay'),
                'type'        => 'text'
            ),
            //        'method' => array(
            //            'title'       => __('Method', 'woo-droppay'),
            //            'type'        => 'select'
            //        )
            );
    }

    public function payment_scripts()
    {
        if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
            return;
        }
        
        wp_enqueue_script( 'woocommerce_droppay_js', WC_HTTPS::force_https_url(plugins_url( '/assets/js/droppay-checkout.js', __FILE__ ) ), array(), null, true );
        wp_enqueue_style( 'woocommerce_droppay_css', WC_HTTPS::force_https_url(plugins_url( '/assets/css/droppay.css', __FILE__ ) ) );
    }

    /**
	 * Payment form on checkout page
	 */
    public function payment_fields( )
    {

        $cart = WC()->cart;
        $total = $cart->total;

        // If paying from order, we need to get total from order not cart.
		if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) {
			$order = wc_get_order( wc_get_order_id_by_order_key( wc_clean( $_GET['key'] ) ) );
			$total = $order->get_total();
		}

		echo '<script id="droppay-script">
                var cont = document.getElementById("order_review");
                if ( (document.getElementById("droppay-container")) === null ) {
                    var iDiv = document.createElement("div");
                    iDiv.id = "droppay-container";
                    cont.appendChild(iDiv);
                }
                </script>
                
                <script id="DropPay" src="https://checkout.drop-pay.com/js/droppay-checkout.js" type="text/javascript" ' .
											'data-checkout-key="' . esc_attr( $this->applicationKey ) . '" 
											 data-amount="' . esc_attr( round( $total, 2 ) ) . '" ' .
											'data-policy="CHARGEABLE" data-max-charges="1" ' .
											'data-product-description="' . esc_attr( __('Payment made on ') . get_site_url()) . '" ' .
											'data-loopback-uri="" ' .
											'data-locale="auto" ' .
                                            'data-action="'. esc_attr( get_site_url()) .'/checkout"' .
											'data-button-wrapper="#droppay-container" ' .
											'data-theme="auto"></script>';

            if ( $this->description ) {
                echo apply_filters( 'wc_droppay_description', wpautop( wp_kses_post( $this->description ) ) );
            }

	}

    public function process_payment( $order_id )
    {
        $order = wc_get_order( $order_id );

        // Get post parameters
        $authorizationId = sanitize_text_field( $_POST['dp_authorization_id'] );
        $authorizationStatus = sanitize_text_field( $_POST['dp_authorization_status'] );

        $authZ = DropPayCheckout::droppay_payment($order, $authorizationId, $authorizationStatus, $this->applicationSecret);

        if ($authZ) {

            $order->update_status( 'completed', __( 'DropPay payment successfull', 'woocommerce' ) );
            // Remove cart.
            WC()->cart->empty_cart();

            $this->logger->info( 'droppay', 'Order Completed: #' . $order->get_id() );
            //Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        } else {
            $order->update_status( 'failed', __( 'DropPay payment failed', 'woocommerce' ) );
            $this->logger->info( 'droppay', 'Order Failed: #' . $order->get_id() );
            // Return error
            return array(
                'result' => 'error',
                'redirect' => $this->get_return_url($order),
            );
        }
    }

}
