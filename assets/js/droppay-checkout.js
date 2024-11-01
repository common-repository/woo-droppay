jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle DropPay payment forms.
	 */
	var wc_droppay_form = {

		/**
		 * Initialize e handlers and UI state.
		 */
		init: function( form ) {
            $( form ).on( 'click', 'li.wc_payment_method', this.switchButton );
            $( form ).on( 'DOMNodeInserted', '#droppay-container button', this.afterButtonIsAdded );
		},

        isDroppayChosen: function() {
            return $( '#payment_method_droppay' ).is( ':checked' );
        },

        validateForm: function( e ) {
            if (! wc_droppay_form.isDropPayModalNeeded() ) {
                e.stopImmediatePropagation();
                $('#place_order').submit();
		    }
        },

        isDropPayModalNeeded: function() {
            var $required_inputs;

            // Don't affect submission if modal is not needed.
            if ( ! wc_droppay_form.isDroppayChosen() ) {
                return false;
            }

            // Don't open modal if required fields are not complete
            if ( $( 'input#terms' ).length === 1 && $( 'input#terms:checked' ).length === 0 ) {
                return false;
            }

            // check to see if we need to validate shipping address
            if ( $( '#ship-to-different-address-checkbox' ).is( ':checked' ) ) {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required, .woocommerce-shipping-fields .validate-required' );
            } else {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required' );
            }

            if ( $required_inputs.length ) {
                var required_error = false;

                $required_inputs.each( function() {
                    if ( $( this ).find( 'input.input-text, select' ).not( $( '#account_password, #account_username' ) ).val() === '' ) {
                        required_error = true;
                    }

                    var emailField = $( this ).find( '#billing_email' );

                    if ( emailField.length ) {
                        var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

                        if ( ! re.test( emailField.val() ) ) {
                            required_error = true;
                        }
                    }
                });

                if ( required_error ) {
                    return false;
                }
            }

            return true;
        },

        switchButton: function() {
		    var form = $( "form.checkout" );
			if ( wc_droppay_form.isDroppayChosen()) {
                var woocommerceButton = $('div.place-order');
                woocommerceButton.css("display", "none");
                var droppayButtonContainer = $('#droppay-container');
                droppayButtonContainer.css("display", "block");
                return true;
            } else {
                var droppayButtonContainer = $('#droppay-container');
                droppayButtonContainer.css("display", "none");
                var woocommerceButton = $('div.place-order');
                woocommerceButton.css("display", "block");
                return true;
            }
		},

        afterButtonIsAdded: function () {
            var droppayEle = $("#droppay-container").get(0);
            droppayEle.addEventListener("click", wc_droppay_form.validateForm , true);
            var droppayButton = $('#droppay-container button');

            var txt = document.createElement("p");
            txt.innerHTML = "Clicca il bottone o inquadra il codice QR!";
            droppayButton.before(txt);

            var txt2 = document.createElement("p");
            txt2.setAttribute('id', 'note-droppay');
            txt2.innerHTML = "Con <span class='orange'>DropPay</span> puoi <span class='gray'>pagare</span> direttamente dal tuo <span class='gray'>smartphone</span>.";
            droppayButton.after(txt2);

            var txt3 = document.createElement("p");
            txt3.setAttribute('id', 'note-droppay');
            txt3.innerHTML = "<span class='orange'>Scarica l’app</span>, <span class='gray'>registrati</span> e <span class='gray'>acquista</span> in sicurezza.";
            $('#note-droppay').after(txt3);

            //smartphone check
            if ($(window).width() <= 600) {
                txt.innerHTML = "Per completare l’acquisto clicca sul bottone, poi ritorna su questa pagina per completare l\'ordine.";
            }
        }
    };

    wc_droppay_form.init( $( "form.checkout" ) );
});