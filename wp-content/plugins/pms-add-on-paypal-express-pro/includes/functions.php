<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

/*
 * Add PayPal Express Checkout to the payment gateways array
 *
 */
function pms_payment_gateways_paypal_express( $payment_gateways ) {

    $payment_gateways['paypal_express'] = array(
        'display_name_user' => 'PayPal',
        'display_name_admin'=> 'PayPal Express Checkout',
        'class_name'        => 'PMS_Payment_Gateway_PayPal_Express'
    );

    return $payment_gateways;

}
add_filter( 'pms_payment_gateways', 'pms_payment_gateways_paypal_express' );


/*
 * Add PayPal Pro to the payment gateways array
 *
 */
function pms_payment_gateways_paypal_pro( $payment_gateways ) {

    $payment_gateways['paypal_pro'] = array(
        'display_name_user' => 'Credit / Debit Card',
        'display_name_admin'=> 'PayPal Payments Pro',
        'class_name'        => 'PMS_Payment_Gateway_PayPal_Pro'
    );

    return $payment_gateways;

}
add_filter( 'pms_payment_gateways', 'pms_payment_gateways_paypal_pro' );


/*
 * Add data-type="credit_card" attribute to the pay_gate hidden and radio input for PayPal Pro
 *
 */
function pms_payment_gateway_input_data_type_paypal_pro( $value, $payment_gateway ) {

    if( $payment_gateway == 'paypal_pro' ) {
        $value = str_replace( '/>', 'data-type="credit_card" />', $value );
    }

    return $value;

}
add_filter( 'pms_output_payment_gateway_input_radio', 'pms_payment_gateway_input_data_type_paypal_pro', 10, 2 );
add_filter( 'pms_output_payment_gateway_input_hidden', 'pms_payment_gateway_input_data_type_paypal_pro', 10, 2 );


/*
 * Add payment types for PayPal Express Checkout
 */
function pms_payment_types_paypal_express( $types ) {

    $types['recurring_payment_profile_created'] = __( 'PayPal Recurring Initial Payment', 'paid-member-subscriptions' );
    $types['expresscheckout']                   = __( 'PayPal Express - Checkout Payment', 'paid-member-subscriptions' );
    $types['recurring_payment']                 = __( 'PayPal Recurring Payment', 'paid-member-subscriptions' );
    $types['web_accept_paypal_pro']             = __( 'PayPal Pro - Direct Payment', 'paid-member-subscriptions');

    return $types;

}
add_filter( 'pms_payment_types', 'pms_payment_types_paypal_express' );


/*
* Function that validates the entered credit card number
*
* @returns : false is cc invalid, card type if cc is valid
*
*/

function pms_validate_cc_number($number) {

    // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
    $number=preg_replace('/\D/', '', $number);

    /* Validate; return value is card type if valid. */
    $card_type = "";
    $card_regexes = array(
        "/^4\d{12}(\d\d\d){0,1}$/" => "visa",
        "/^5[12345]\d{14}$/"       => "mastercard",
        "/^3[47]\d{13}$/"          => "amex",
        "/^6011\d{12}$/"           => "discover",
        "/^30[012345]\d{11}$/"     => "diners",
        "/^3[68]\d{12}$/"          => "diners",
    );

    foreach ($card_regexes as $regex => $type) {
        if (preg_match($regex, $number)) {
            $card_type = $type;
            break;
        }
    }

    if (!$card_type) {
        return false;
    }

    /*  mod 10 checksum algorithm  */
    $revcode = strrev($number);
    $checksum = 0;

    for ($i = 0; $i < strlen($revcode); $i++) {

        $current_num = intval($revcode[$i]);
        if($i & 1) {  /* Odd  position */
            $current_num *= 2;
        }

        /* Split digits and add. */
        $checksum += $current_num % 10;
        if ($current_num >  9) {
            $checksum += 1;
        }
    }

    if ($checksum % 10 == 0) {
        return $card_type;
    } else {
        return false;
    }

}



/*
 * Function that adds the recurring info to the payment data
 *
 */
if( !function_exists( 'pms_recurring_register_payment_data' ) ) {

    function pms_recurring_register_payment_data( $payment_data, $payments_settings ) {

        // Unlimited plans cannot be recurring
        if( $payment_data['user_data']['subscription']->duration == 0 )
            return $payment_data;

        // Handle recurring
        if( (isset( $_POST['pms_recurring'] ) && $_POST['pms_recurring'] == 1) || ( isset( $payments_settings['recurring'] ) && $payments_settings['recurring'] == 2 ) ) {
            $payment_data['recurring'] = 1;
        } else {
            $payment_data['recurring'] = 0;
        }

        return $payment_data;

    }
    //add_filter( 'pms_register_payment_data', 'pms_recurring_register_payment_data', 10, 2 );

}


/**
 * Returns an array with the API username, API password and API signature of the PayPal business account
 * if they all exist, if not will return false
 *
 * @return mixed array or bool false
 *
 */
if( !function_exists( 'pms_get_paypal_api_credentials' ) ) {

    function pms_get_paypal_api_credentials() {

        $pms_settings = get_option( 'pms_settings', array() );

        if( empty( $pms_settings['payments']['gateways']['paypal'] ) )
            return false;

        $pms_settings = $pms_settings['payments']['gateways']['paypal'];

        if( pms_is_payment_test_mode() )
            $sandbox_prefix = 'test_';
        else
            $sandbox_prefix = '';

        $api_credentials = array(
            'username'  => $pms_settings[$sandbox_prefix . 'api_username'],
            'password'  => $pms_settings[$sandbox_prefix . 'api_password'],
            'signature' => $pms_settings[$sandbox_prefix . 'api_signature']
        );

        $api_credentials = array_map( 'trim', $api_credentials );

        if( count( array_filter($api_credentials) ) == count($api_credentials) )
            return $api_credentials;
        else
            return false;

    }
}


/*
 * Display a warning to the administrators if the API credentials are missing in the
 * register page
 *
 */
if( !function_exists( 'pms_paypal_api_credentials_admin_warning' ) ) {

    function pms_paypal_api_credentials_admin_warning() {

        if( !current_user_can( 'manage_options' ) )
            return;

        $are_active = array_intersect( array( 'paypal_express', 'paypal_pro' ), pms_get_active_payment_gateways() );

        if( pms_get_paypal_api_credentials() == false && !empty( $are_active ) ) {

            echo '<div class="pms-warning-message-wrapper">';
                echo '<p>' . sprintf( __( 'Your PayPal API settings are missing. In order to make payments you will need to add your API credentials %1$s here %2$s.', 'paid-member-subscriptions' ), '<a href="' . admin_url( 'admin.php?page=pms-settings-page&nav_tab=payments#pms-settings-payment-gateways' ) .'" target="_blank">', '</a>' ) . '</p>';
                echo '<p><em>' . __( 'This message is visible only by Administrators.', 'paid-member-subscriptions' ) . '</em></p>';
            echo '</div>';

        }

    }
    add_action( 'pms_register_form_top', 'pms_paypal_api_credentials_admin_warning' );
    add_action( 'pms_new_subscription_form_top', 'pms_paypal_api_credentials_admin_warning' );
    add_action( 'pms_upgrade_subscription_form_top', 'pms_paypal_api_credentials_admin_warning' );
    add_action( 'pms_renew_subscription_form_top', 'pms_paypal_api_credentials_admin_warning' );
    add_action( 'pms_retry_payment_form_top', 'pms_paypal_api_credentials_admin_warning' );

}


/*
 * Adds the value of the payment_profile_id received from the payment gateway in the database to a
 * users subscription information
 *
 */
if( !function_exists('pms_member_add_payment_profile_id') ) {
    function pms_member_add_payment_profile_id( $user_id = 0, $subscription_plan_id = 0, $payment_profile_id = '' ) {

        if( empty($user_id) || empty($subscription_plan_id) || empty($payment_profile_id) )
            return false;

        global $wpdb;

        $result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', array( 'payment_profile_id' => $payment_profile_id ), array( 'user_id' => $user_id, 'subscription_plan_id' => $subscription_plan_id ) );

        if( $result === false )
            return false;
        else
            return true;
    }
}


/*
 * Returns the value of the payment_profile_id of a member subscription if it exists
 *
 * @param int $user_id
 * @param int $subscription_plan_id
 *
 * @return mixed string | null
 *
 */
if( !function_exists('pms_member_get_payment_profile_id') ) {
    function pms_member_get_payment_profile_id( $user_id = 0, $subscription_plan_id = 0 ) {

        if( empty($user_id) || empty($subscription_plan_id) )
            return NULL;

        global $wpdb;

        $result = $wpdb->get_var( "SELECT payment_profile_id FROM {$wpdb->prefix}pms_member_subscriptions WHERE user_id = {$user_id} AND subscription_plan_id = {$subscription_plan_id}" );

        // In case we do not find it, it could be located in the api failed canceling
        // errors
        if( is_null($result) ) {

            $api_failed_attempts = get_option( 'pms_api_failed_attempts', array() );

            if( isset( $api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id'] ) )
                $result = $api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id'];

        }

        return $result;

    }
}


/*
 * Returns the member subscription details given the PayPal payment profile id
 *
 * @param string $payment_profile_id
 *
 * @return mixed array | null
 *
 */
if( !function_exists('pms_get_member_subscription_by_payment_profile_id') ) {

    function pms_get_member_subscription_by_payment_profile_id( $payment_profile_id = '' ) {

        if( empty( $payment_profile_id ) )
            return null;

        $payment_profile_id = sanitize_text_field( $payment_profile_id );

        global $wpdb;

        $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pms_member_subscriptions WHERE payment_profile_id LIKE {$payment_profile_id}", ARRAY_A );

        return $result;

    }

}


/*
 * Checks to see if the payment profile id provided is one supported by
 * PayPal
 *
 * @param string $payment_profile_id
 *
 * @return bool
 *
 */
if( !function_exists('pms_is_paypal_payment_profile_id') ) {

    function pms_is_paypal_payment_profile_id( $payment_profile_id = '' ) {

        if( strpos( $payment_profile_id, 'I-' ) !== false )
            return true;
        else
            return false;

    }

}


/*
 * Function that outputs the automatic renewal option in the front-end for the user/customer to see
 *
 */
if( !function_exists( 'pms_ppsrp_renewal_option' ) && !function_exists( 'pms_renewal_option_field' ) ) {

    function pms_renewal_option_field( $output, $include, $exclude_id_group, $member, $pms_settings ) {

        // Get all subscription plans
        if( empty( $include ) )
            $subscription_plans = pms_get_subscription_plans();
        else {
            if( !is_object( $include[0] ) )
                $subscription_plans = pms_get_subscription_plans( true, $include );
            else
                $subscription_plans = $include;
        }

        // Calculate the amount for all subscription plans
        $amount = 0;
        foreach( $subscription_plans as $subscription_plan ) {
            $amount += $subscription_plan->price;
        }

        if( !$member && isset( $pms_settings['payments']['recurring'] ) && $pms_settings['payments']['recurring'] == 1 && $amount != 0 ) {

            $output .= '<div class="pms-subscription-plan-auto-renew">';
                $output .= '<label><input name="pms_recurring" type="checkbox" value="1" ' . ( isset( $_REQUEST['pms_recurring'] ) ? 'checked="checked"' : '' ) . ' />' . apply_filters( 'pms_auto_renew_label', __( 'Automatically renew subscription', 'paid-member-subscriptions' ) ) . '</label>';
            $output .= '</div>';

        }

        return apply_filters( 'pms_renewal_option_field', $output );

    }
    add_filter( 'pms_output_subscription_plans', 'pms_renewal_option_field', 20, 5 );

}


/*
 * Check for PayPal Standard Recurring Payments Add-On to see if it is activated
 * For recurring payments on cancellation we need to cancel the subscription in PayPal as well,
 * and the recurring add-on also handles this operations
 *
 * As we don't want any conflicts
 *
 */
function pms_check_paypal_confirm_cancel_subscription_hooks() {

    $active_plugins = get_option( 'active_plugins' );
    $found          = false;


    // Search for standard recurring add-on
    foreach( $active_plugins as $active_plugin ) {

        if( strpos( $active_plugin, 'pms-add-on-paypal-standard-recurring-payments' ) !== false )
            $found = true;

    }

    if( $found )
        remove_filter( 'pms_confirm_cancel_subscription', 'pms_ppsrp_confirm_cancel_subscription', 10 );

}
add_action( 'init', 'pms_check_paypal_confirm_cancel_subscription_hooks' );


/*
 * Hooks to 'pms_confirm_cancel_subscription' from PMS to change the default value provided
 * Makes an api call to PayPal to cancel the subscription, if is successful returns true,
 * but if not returns an array with 'error'
 *
 * @param bool $confirmation
 * @param int $user_id
 * @param int $subscription_plan_id
 *
 * @return mixed    - bool true if successful, array if not
 *
 */
if( !function_exists( 'pms_paypal_confirm_cancel_subscription' ) ) {

    function pms_paypal_confirm_cancel_subscription( $confirmation, $user_id, $subscription_plan_id ) {

        // Get payment_profile_id
        $payment_profile_id = pms_member_get_payment_profile_id( $user_id, $subscription_plan_id );

        // Continue only if the profile id is a PayPal one
        if( !pms_is_paypal_payment_profile_id($payment_profile_id) )
            return $confirmation;

        // Instantiate the payment gateway with data
        $payment_data = array(
            'user_data' => array(
                'user_id'       => $user_id,
                'subscription'  => pms_get_subscription_plan( $subscription_plan_id )
            )
        );

        $paypal_express = pms_get_payment_gateway( 'paypal_express', $payment_data );

        // Cancel the subscription and return the value
        $confirmation = $paypal_express->process_cancel_subscription( $payment_profile_id );

        if( !$confirmation )
            $confirmation = array( 'error' => $paypal_express->get_cancel_subscription_error() );

        return $confirmation;

    }
    add_filter( 'pms_confirm_cancel_subscription', 'pms_paypal_confirm_cancel_subscription', 10, 3 );

}


/*
 * Hook to 'pms_paypal_express_before_upgrade_subscription' to cancel the active subscription
 * from PayPal
 *
 */
if( !function_exists( 'pms_paypal_cancel_subscription_before_upgrade' ) ) {

    function pms_paypal_cancel_subscription_before_upgrade( $member_subscription_id, $payment_data, $post_data ) {

        $user_id              = $payment_data['user_id'];
        $subscription_plan_id = $member_subscription_id;

        // Get payment_profile_id
        $payment_profile_id = pms_member_get_payment_profile_id( $user_id, $subscription_plan_id );

        if( empty($payment_profile_id) || !pms_is_paypal_payment_profile_id($payment_profile_id) )
            return;

        // Instantiate the payment gateway with data
        $payment_data = array(
            'user_data' => array(
                'user_id'       => $user_id,
                'subscription'  => pms_get_subscription_plan( $subscription_plan_id )
            )
        );

        $paypal_express = pms_get_payment_gateway( 'paypal_express', $payment_data );

        // Cancel the subscription and return the value
        $confirmation = $paypal_express->process_cancel_subscription( $payment_profile_id );

        // If something went wrong repeat cancellation api call to PayPal every hour until the subscription gets cancelled successfully
        if( !$confirmation )
            wp_schedule_event( time() + 60 * 60, 'hourly', 'pms_api_retry_cancel_paypal_subscription', array( $user_id, $subscription_plan_id ) );



    }
    add_action( 'pms_paypal_express_before_upgrade_subscription', 'pms_paypal_cancel_subscription_before_upgrade', 10, 3 );

}


/*
 * Cron job that executes if a subscription did not get cancelled successfully
 * It will fire one every hour until the subscription gets cancelled
 *
 */
if( !function_exists( 'pms_api_retry_cancel_paypal_subscription' ) ) {

    function pms_api_retry_cancel_paypal_subscription( $user_id, $subscription_plan_id ) {

        // Get payment_profile_id
        $payment_profile_id = pms_member_get_payment_profile_id( $user_id, $subscription_plan_id );

        if( empty($payment_profile_id) || !pms_is_paypal_payment_profile_id($payment_profile_id) )
            return;

        // Instantiate the payment gateway with data
        $payment_data = array(
            'user_data' => array(
                'user_id'       => $user_id,
                'subscription'  => pms_get_subscription_plan( $subscription_plan_id )
            )
        );

        $paypal_express = pms_get_payment_gateway( 'paypal_express', $payment_data );

        // Cancel the subscription and return the value
        $confirmation = $paypal_express->process_cancel_subscription( $payment_profile_id );
        $error        = $paypal_express->get_cancel_subscription_error();

        // If all is good clear the schedule
        if( $confirmation && !empty($error) ) {

            // Removed information
            if( isset( $api_failed_attempts[$user_id][$subscription_plan_id] ) )
                unset( $api_failed_attempts[$user_id][$subscription_plan_id] );

            // Clear schedule if it exists
            if( wp_get_schedule( 'pms_api_retry_cancel_paypal_subscription', array( $user_id, $subscription_plan_id ) ) )
                wp_clear_scheduled_hook( 'pms_api_retry_cancel_paypal_subscription', array( $user_id, $subscription_plan_id ) );

            update_option( 'pms_api_failed_attempts', $api_failed_attempts );


            do_action( 'pms_api_cancel_paypal_subscription_upgrade_successful', $user_id, $subscription_plan_id, 'update', $confirmation, $error );

        } else {

            // Add the retry to the list
            $api_failed_attempts[$user_id][$subscription_plan_id]['retries'][] = array(
                'time'  => time(),
                'error' => $error
            );

            // Increment retry count
            if( !isset($api_failed_attempts[$user_id][$subscription_plan_id]['retry_count']) )
                $api_failed_attempts[$user_id][$subscription_plan_id]['retry_count'] = 1;
            else
                $api_failed_attempts[$user_id][$subscription_plan_id]['retry_count']++;

            // Add the payment profile id
            if( !isset($api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id']) )
                $api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id'] = $payment_profile_id;

            update_option( 'pms_api_failed_attempts', $api_failed_attempts );


            do_action( 'pms_api_cancel_paypal_subscription_upgrade_unsuccessful', $user_id, $subscription_plan_id, 'update', $confirmation, $error );

        }


    }
    add_action( 'pms_api_retry_cancel_paypal_subscription', 'pms_api_retry_cancel_paypal_subscription', 10, 2 );

}