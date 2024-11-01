<?php
/*
Copyright (C) 2017 Zero11

DropPay Webhook
Charge Orders
 */

class DropPayCheckout
{

    static function droppay_payment($order, $authorizationId, $authorizationStatus, $secretKey)
    {
        if (class_exists('DroppayLogger')) {
            $logger = new DroppayLogger();
        }

        $baseUrl = "https://checkout.drop-pay.com/v1/authorization/";
        //CHECK: Read the status of Authorization and obtain a fresh Pay Token
        $urlCheck = $baseUrl . $authorizationId . '/check';

        $response = wp_remote_get($urlCheck,
            array(
                'method' => 'GET',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array('X-DropPay-Checkout-PrivateKey' => $secretKey),
                'body' => null,
                'cookies' => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $logger->error( 'droppay', 'Check Response of Order: ' . print_r($response, true));
            echo "Something went wrong: $error_message";
            return false;
        } 

        $response = json_decode($response['body'], true);
        $IdCheck = $response['id'];
        $statusCheck = $response['status'];
        $chargeAmountCheck = $response['charge_amount'];
        $descriptionCheck = $response['description'];
        $customerCheck = $response['merchant_custom_id'];
        $payToken = $response['pay_token']['val'];

        //Check Webhook Auth
        if (!$authorizationStatus) {
            $logger->error( 'droppay', 'Invalid Authorization from DropPay: ' . $authorizationStatus);
            return false;
        }

        // Check status
        if ($IdCheck != $authorizationId || $statusCheck != 'GRANTED') {
            $logger->error( 'droppay', 'Check Status of Order #' . $order->get_id() . ' Failed' );
            $logger->error( 'droppay', 'Check Response: ' . print_r($response, true) );
            return false;
        }

        // Check that response and session parameters are equal
        if ($order->get_total() != $chargeAmountCheck) {
            $logger->error( 'droppay', 'Check of DropPay response found different data from those saved on session' );
            $logger->error( 'droppay', 'Check Response: ' . print_r($response, true) );
            $logger->error( 'droppay', 'Order Price: ' . $order->get_total() );
            return false;
        }

        $logger->info( 'droppay', 'Status GRANTED for order: #' . $order->get_id() );
        $logger->info( 'droppay', 'Check Response: ' . print_r($response, true) );
        $logger->info( 'droppay', 'Starting Charge Action' );

        //CHARGE: Debit user's DropPay Account with the previously obtained fresh Pay Token
        $urlCharge = $baseUrl . $authorizationId . '/charge';
        $fields = array(
            'description' => $descriptionCheck,
            'amount' => $chargeAmountCheck,
            'pay_token_val' => $payToken
        );
        $fields_string = json_encode($fields);
    
        $response = wp_remote_post($urlCharge,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(
                    'X-DropPay-Checkout-PrivateKey' => $secretKey,
                    'Content-Type' => 'application/json'
                ),
                'body' => $fields_string,
                'cookies' => array()
            )
        );


        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $logger->error( 'droppay', 'Charge Response of Order #' . $order->get_id() . ': ' . print_r($response, true));
            echo "Something went wrong: $error_message";
            return false;
        }

        $response2 = json_decode($response['body'], true);
        $AuthorizationIdCharge = $response2['authorization_id'];
        $statusCharge = $response2['status'];
        $chargeAmountCharge = $response2['amount'];
        $descriptionCharge = $response2['description'];
        $payTokenCharge = $response2['pay_token_val'];

        // Check charge status
        if ($statusCharge != 'DONE') {
            $logger->error( 'droppay', 'Charge of Order #' . $order->get_id() . ' Failed' );
            $logger->error( 'droppay', 'Charge Response: ' . print_r($response2, true) );
            return false;
        }

        // Check that charge response and session parameters are equal
        if ($AuthorizationIdCharge != $authorizationId || $payTokenCharge != $payToken || $chargeAmountCharge != $chargeAmountCheck || $descriptionCharge != $descriptionCheck) {
            $logger->error( 'droppay', 'Charge of DropPay response found different data from those of the Check Request' );
            $logger->error( 'droppay', 'Charge Response: ' . print_r($response2, true) );
            return false;
        }

        $logger->info( 'droppay', 'CHARGED order: #' . $order->get_id() );
        $logger->info( 'droppay', 'Charge Response: ' . print_r($response2, true) );

        return true;
    }

}
?>