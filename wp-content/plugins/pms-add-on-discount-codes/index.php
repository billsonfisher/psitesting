<?php
/**
 * Plugin Name: Paid Member Subscriptions - Discount Codes Add-on
 * Plugin URI: http://www.cozmoslabs.com/wordpress-paid-member-subscriptions/
 * Description: Easily create discount codes for Paid Member Subscriptions plugin.
 * Version: 1.2.3
 * Author: Cozmoslabs, Adrian Spiac
 * Author URI: http://www.cozmoslabs.com/
 * Text Domain: pms-add-on-discount-codes
 * License: GPL2
 *
 * == Copyright ==
 * Copyright 2017 Cozmoslabs (www.cozmoslabs.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

/**
 * Include the files needed
 *
 */

// Discount code object class
if( file_exists( plugin_dir_path( __FILE__ ). 'functions-discount.php' ) )
    include_once( plugin_dir_path( __FILE__ ) . 'functions-discount.php' );

if( file_exists( plugin_dir_path( __FILE__ ). 'class-discount-code.php' ) )
    include_once( plugin_dir_path( __FILE__ ) . 'class-discount-code.php' );

// Discount Codes custom post type
if( file_exists( plugin_dir_path( __FILE__ ) . 'class-admin-discount-codes.php' ) )
    include_once( plugin_dir_path( __FILE__ ). 'class-admin-discount-codes.php' );

// Meta box for discount codes cpt
if( file_exists( plugin_dir_path( __FILE__ ) . 'class-metabox-discount-codes-details.php' ) )
    include_once( plugin_dir_path( __FILE__ ) . 'class-metabox-discount-codes-details.php' );


/**
 * Adding Admin scripts
 *
 */
function pms_dc_add_admin_scripts(){

    // If the file exists where it should be, enqueue it
    if( file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/cpt-discount-codes.js' ) )
        wp_enqueue_script( 'pms-discount-codes-js', plugin_dir_url( __FILE__ ) . 'assets/js/cpt-discount-codes.js', array( 'jquery','jquery-ui-datepicker' ), PMS_VERSION );

    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), PMS_VERSION );

    // add back-end css for Discount Codes cpt
    wp_enqueue_style( 'pms-dc-style-back-end', plugin_dir_url( __FILE__ ) . 'assets/css/style-back-end.css' );

}
add_action('pms_cpt_enqueue_admin_scripts_pms-discount-codes','pms_dc_add_admin_scripts');


/**
 * Adding Front-end scripts
 *
 */
function pms_dc_add_frontend_scripts(){

    if( file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/frontend-discount-code.js' ) ) {

        wp_enqueue_script('pms-frontend-discount-code-js', plugin_dir_url(__FILE__) . 'assets/js/frontend-discount-code.js', array('jquery'), PMS_VERSION );

        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'pms-frontend-discount-code-js', 'pms_discount_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

    // add front-end CSS for discount code box
    if ( file_exists( plugin_dir_path(__FILE__) . 'assets/css/style-front-end.css') ) {
        wp_enqueue_style('pms-dc-style-front-end', plugin_dir_url(__FILE__). 'assets/css/style-front-end.css' );
    }


}
add_action('wp_enqueue_scripts','pms_dc_add_frontend_scripts');


/**
 * Positioning the Discount Codes label under Payments in PMS submenu
 *
 */
function pms_dc_submenu_order( $menu_order){
    global $submenu;

    if ( isset($submenu['paid-member-subscriptions']) ) {

        foreach ($submenu['paid-member-subscriptions'] as $key => $value) {
            if ($value[2] == 'edit.php?post_type=pms-discount-codes') $discounts_key = $key;
            if ($value[2] == 'pms-payments-page') $payments_key = $key;
        }

        if (isset($payments_key) && isset($discounts_key)) {
            $discounts_value = $submenu['paid-member-subscriptions'][$discounts_key];

            if ($payments_key > $discounts_key) $payments_key--;
            unset($submenu['paid-member-subscriptions'][$discounts_key]);

            $array1 = array_slice($submenu['paid-member-subscriptions'], 0, $payments_key);
            $array2 = array_slice($submenu['paid-member-subscriptions'], $payments_key);
            array_push($array1, $discounts_value);

            $submenu['paid-member-subscriptions'] = array_merge($array1, $array2);

        }
    }

    return $menu_order;

}
add_filter('custom_menu_order','pms_dc_submenu_order');


/**
 * Output discount code box on the front-end
 *
 * */
function pms_dc_output_discount_box( $output, $include, $exclude_id_group, $member, $pms_settings, $subscription_plans ){

    // Don't display the discount field on account pages
    if( !empty( $member ) )
        return $output;

    if( empty( $subscription_plans ) )
        return $output;

    // Calculate the total price of the subscription plans
    $total_price = 0;
    foreach( $subscription_plans as $subscription_plan )
        $total_price += (int)$subscription_plan->price;

    // Return the discount code field only if we have paid plans
    if( $total_price !== 0 ) {
        $discount_output  = '<div id="pms-subscription-plans-discount">';
        $discount_output .= '<label for="pms_subscription_plans_discount">' . apply_filters('pms_form_label_discount_code', __('Discount Code: ', 'pms-add-on-discount-codes')) . '</label>';
        $discount_output .= '<input id="pms_subscription_plans_discount_code" name="discount_code" placeholder="' . apply_filters( 'pms_form_input_placeholder_discount_code', __( 'Enter discount', 'pms-add-on-discount-codes' ) ) . '" type="text" value="' . ( !empty( $_POST['discount_code'] ) ? esc_attr( $_POST['discount_code'] ) : '' ) . '" />';
        $discount_output .= '<input id="pms-apply-discount" class="pms-submit button" type="submit" value="' . apply_filters( 'pms_form_submit_discount_code', __( 'Apply', 'pms-add-on-discount-codes' ) ) . '">';
        $discount_output .= '</span>';
        $discount_output .= '</div>';

        $message_output  = '<div id="pms-subscription-plans-discount-messages-wrapper">';
            $message_output .= '<div id="pms-subscription-plans-discount-messages" ' . (pms_errors()->get_error_message('discount_error') ? 'class="pms-discount-error"' : '') . '>';
            $message_output .= pms_errors()->get_error_message('discount_error');
            $message_output .= '</div>';

            $message_output .= '<div id="pms-subscription-plans-discount-messages-loading">';
            $message_output .= __( 'Applying discount code. Please wait...', 'pms-add-on-discount-codes' );
            $message_output .= '</div>';
        $message_output .= '</div>';

        $output .= $discount_output . $message_output;
    }

    return $output;
}
add_filter('pms_output_subscription_plans','pms_dc_output_discount_box', 25, 6 );


/**
 * Function that returns the front-end discount code errors or success message
 *
 */
function pms_dc_output_apply_discount_message() {

    $response     = array(); // initialize response
    $code         = '';
    $subscription = '';
    $user_checked_auto_renew = false;

    // Clean-up and setup data
    if( !empty( $_POST['code'] ) )
        $code = sanitize_text_field( trim( $_POST['code'] ) );

    if( !empty( $_POST['subscription'] ) )
        $subscription = (int)trim( $_POST['subscription'] );

    // User checked the auto-renew checkbox
    if( !empty( $_POST['recurring'] ) )
        $user_checked_auto_renew = true;


    // Assemble the response
    if ( !empty( $code ) && !empty( $subscription ) ) {

        $error = pms_dc_get_discount_error( $code, $subscription );

        // Setup user message
        if( ! empty( $error ) )
            $response['error']['message'] = $error;
        else
            $response['success']['message'] = pms_dc_apply_discount_success_message( $code, $subscription, $user_checked_auto_renew );

        // Determine wether the discount code is a partial discount or a full discount
        $response['is_full_discount'] = pms_dc_check_is_full_discount( $code, $subscription, $user_checked_auto_renew );

    }

    wp_send_json($response);

}
add_action( 'wp_ajax_pms_discount_code', 'pms_dc_output_apply_discount_message' );
add_action( 'wp_ajax_nopriv_pms_discount_code', 'pms_dc_output_apply_discount_message' );


/**
 * Function that returns the success message and the billing amount when the discount was successfully applied
 *
 * @param string $code - The entered discount code
 * @param string $subscription - Subscription plan id
 * @param bool $user_checked_auto_renew - Whether or not the user checked the "Automatically renew subscription" checkbox
 * @return string
 */
function pms_dc_apply_discount_success_message( $code, $subscription, $user_checked_auto_renew ) {

    $response = __('Discount successfully applied! ', 'pms-add-on-discount-codes');

    if ( !empty( $code ) && !empty( $subscription ) ) {

        //Get Discount object
        $discount = pms_get_discount_by_code( $code );

        // Get Subscription plan object
        $subscription_plan = pms_get_subscription_plan( $subscription );

        // Get currency symbol
        $currency_symbol = pms_get_currency_symbol( pms_get_active_currency() );

        // Check if subscription payment will be recurring
        $is_recurring = pms_dc_subscription_is_recurring( $subscription_plan, $user_checked_auto_renew );

        $initial_payment = (float)$subscription_plan->price;

        // Take into account the Sign-up Fee as well
        if ( !empty( $subscription_plan->sign_up_fee ) ) {

            // Check if there is a Free Trial period
            if ( !empty( $subscription_plan->trial_duration ) ){
                $initial_payment = $subscription_plan->sign_up_fee;
            }
            else {
                $initial_payment += (float)$subscription_plan->sign_up_fee;
            }
        }

        // Apply discount to initial amount
        $initial_payment = pms_calculate_discounted_amount( $initial_payment, $discount );

        if ($is_recurring){

            $recurring_payment = (float)$subscription_plan->price;

            // Check if we need to apply discount to recurring payments as well
            if ( !empty( $discount->recurring_payments ) ) {

                $recurring_payment = pms_calculate_discounted_amount( $subscription_plan->price, $discount );
            }

        }

        /**
         * Start building the response
         */

        // Properly display the subscription plan duration
        $duration = '';
        if( $subscription_plan->duration > 0) {

            switch ($subscription_plan->duration_unit) {
                case 'day':
                    $duration = sprintf( _n( 'day', '%s days', $subscription_plan->duration, 'pms-add-on-discount-codes' ), $subscription_plan->duration );
                    break;
                case 'week':
                    $duration = sprintf( _n( 'week', '%s weeks', $subscription_plan->duration, 'pms-add-on-discount-codes' ), $subscription_plan->duration );
                    break;
                case 'month':
                    $duration = sprintf( _n( 'month', '%s months', $subscription_plan->duration, 'pms-add-on-discount-codes' ), $subscription_plan->duration );
                    break;
                case 'year':
                    $duration = sprintf( _n( 'year', '%s years', $subscription_plan->duration, 'pms-add-on-discount-codes' ), $subscription_plan->duration );
                    break;
            }
        }

        // Properly display the free trial duration
        $trial_duration = '';
        if( $subscription_plan->trial_duration > 0) {

            switch ($subscription_plan->trial_duration_unit) {
                case 'day':
                    $trial_duration = sprintf( _n( '%s day', '%s days', $subscription_plan->trial_duration, 'pms-add-on-discount-codes' ), $subscription_plan->trial_duration );
                    break;
                case 'week':
                    $trial_duration = sprintf( _n( '%s week', '%s weeks', $subscription_plan->trial_duration, 'pms-add-on-discount-codes' ), $subscription_plan->trial_duration );
                    break;
                case 'month':
                    $trial_duration = sprintf( _n( '%s month', '%s months', $subscription_plan->trial_duration, 'pms-add-on-discount-codes' ), $subscription_plan->trial_duration );
                    break;
                case 'year':
                    $trial_duration = sprintf( _n( '%s year', '%s years', $subscription_plan->trial_duration, 'pms-add-on-discount-codes' ), $subscription_plan->trial_duration );
                    break;
            }
        }


        // Handle Free Trial
        $response_trial = '';
        if ( !empty($subscription_plan->trial_duration) ){
            $response_trial = sprintf(__(' after %s ' , 'pms-add-on-discount-codes'), $trial_duration);
        }


        // Handle initial payment response
        // Set currency position according to the PMS Settings page
        $initial_payment_price = ( function_exists('pms_format_price') && function_exists('pms_get_active_currency') ) ? pms_format_price($initial_payment, pms_get_active_currency()) : $initial_payment . $currency_symbol;

        $response_initial_payment = sprintf( __(' is %s', 'pms-add-on-discount-codes'), $initial_payment_price );
        $response_recurring_payment = '.';

        // Handle recurring response
        if ( ($is_recurring) && ($recurring_payment != 0) ) {

            if ( $initial_payment == $recurring_payment ) {
                $response_recurring_payment = sprintf(__(' every %s.', 'pms-add-on-discount-codes'), $duration);
            }
            else {

                // Set currency position according to the PMS Settings page
                $recurring_payment_price = ( function_exists('pms_format_price') && function_exists('pms_get_active_currency') ) ? pms_format_price($recurring_payment, pms_get_active_currency()) : $recurring_payment_price = $recurring_payment . $currency_symbol;;
                $response_recurring_payment = sprintf(__(', then %s %s every %s.', 'pms-add-on-discount-codes'), $response_trial, $recurring_payment_price, $duration);
            }

        }

        // Final response
        if ( ($is_recurring) && ($initial_payment == $recurring_payment) )
            $response .= __('Amount to be charged ') . $response_trial . $response_initial_payment . $response_recurring_payment;
        else
            $response .= __('Amount to be charged ') . $response_initial_payment . $response_recurring_payment;

    }

    /**
     * Filter discount applied successfully message.
     *
     * @param string $code The entered discount code
     * @param string $subscription The subscription plan id
     */
    return apply_filters('pms_dc_apply_discount_success_message', $response, $code, $subscription);
}


/**
 * Determines whether the discount
 *
 * @param string $code
 * @param int    $subscription_plan_id
 * @param bool   $user_checked_auto_renew - Whether or not the user checked the "Automatically renew subscription" checkbox
 *
 * @return bool
 *
 */
function pms_dc_check_is_full_discount( $code = '', $subscription_plan_id = 0, $user_checked_auto_renew = false ) {

    if( empty( $code ) )
        return false;

    if( empty( $subscription_plan_id ) )
        return false;

    $discount_code     = pms_get_discount_by_code( $code );
    $subscription_plan = pms_get_subscription_plan( $subscription_plan_id );

    $checkout_is_recurring = pms_dc_subscription_is_recurring( $subscription_plan, $user_checked_auto_renew );

    $discounted_amount = pms_calculate_discounted_amount( $subscription_plan->price, $discount_code );

    // If the checkout creates a subscription with recurring payments
    if( $checkout_is_recurring ) {

        if( ! empty( $discount_code->recurring_payments ) ) {

            if( $discounted_amount == 0 )
                return true;

        }

    }

    // If the checkout doesn't create a subscription with recurring payments
    if( ! $checkout_is_recurring ) {

        if( empty( $subscription_plan->trial_duration ) ) {

            if( $discounted_amount == 0 )
                return true;

        }

    }

    return false;

}


/**
 * Function that checks if a given subscription is recurring, taking into consideration also if the user checked the "Automatically renew subscription" checkbox
 *
 * @param PMS_Subscription_Plan $subscription_plan - The subscription plan object
 * @param bool                  $user_checked_auto_renew - Whether or not the user checked the "Automatically renew subscription" checkbox
 *
 * @return bool
 *
 */
function pms_dc_subscription_is_recurring( $subscription_plan, $user_checked_auto_renew ){

    // Subscription plan is never ending
    if( empty( $subscription_plan->duration ) )
        return false;

    // Subscription plan has options: always recurring
    if( $subscription_plan->recurring == 2 )
        return true;

    // Subscription plan has option: never recurring
    if( $subscription_plan->recurring == 3 )
        return false;

    // Subscription plan has options: customer opts in
    if( $subscription_plan->recurring == 1 )
       return $user_checked_auto_renew;


    // Subscription plan has option: settings default
    if( empty( $subscription_plan->recurring ) ) {

        $settings           = get_option( 'pms_settings', array() );
        $settings_recurring = empty( $settings['payments']['recurring'] ) ? 0 : (int)$settings['payments']['recurring'];

        if( empty( $settings_recurring ) )
            return false;

        // Settings has option: always recurring
        if( $settings_recurring == 2 )
            return true;

        // Settings has option: never recurring
        if( $settings_recurring == 3 )
            return false;

        // Settings has option: customer opts in
        if( $settings_recurring == 1 )
            return $user_checked_auto_renew;

    }

}

/**
 * Function that checks for and returns the discount errors
 * @param string $code The discount code entered
 * @param string $subscription The subscription plan ID
 * @return string
 */
function pms_dc_get_discount_error( $code, $subscription){

    if ( !empty($code) ) {
        // Get all the discount data
        $discount_meta = PMS_Discount_Codes_Meta_Box::get_discount_meta_by_code( $code );

        if ( !empty($discount_meta) ) { //discount is active

            $discount_subscriptions = array();
            if (!empty($discount_meta['pms_discount_subscriptions']))
                $discount_subscriptions = explode( ',' , $discount_meta['pms_discount_subscriptions'][0] );

            if ( empty($subscription) )
                return __('Please select a subscription plan and try again.', 'pms-add-on-discount-codes');

            if ( !in_array( $subscription, $discount_subscriptions) ) {
                //discount not valid for this subscription
                return __('The discount is not valid for this subscription plan.', 'pms-add-on-discount-codes');
            }

            if ( !empty($discount_meta['pms_discount_start_date'][0]) && (strtotime($discount_meta['pms_discount_start_date'][0]) > time()) ) {
                //start date is in the future
                return __('The discount code you entered is not active yet.', 'pms-add-on-discount-codes');
            }

            if ( !empty($discount_meta['pms_discount_expiration_date'][0]) && (strtotime($discount_meta['pms_discount_expiration_date'][0]) <= time()) ) {
                //expiration date has passed
                return __('The discount code you entered has expired.', 'pms-add-on-discount-codes');
            }

            if ( !empty($discount_meta['pms_discount_max_uses'][0]) && isset($discount_meta['pms_discount_uses'][0]) && ( $discount_meta['pms_discount_max_uses'][0] <= $discount_meta['pms_discount_uses'][0]) ) {
                //all uses for this discount have been consumed
                return __('The discount code maximum uses have been reached.', 'pms-add-on-discount-codes');
            }

            /**
             * Hook for adding custom validation for discount codes
             *
             * @param string $code The discount code entered
             * @param string $subscription The subscription plan ID
             * @param array $discount_meta The discount code details
             * @return string
             */
            do_action('pms_dc_get_discount_error', $code, $subscription, $discount_meta );

        }
        else {
            // Entered discount code was not found or is inactive
            return __('The discount code you entered is invalid.', 'pms-add-on-discount-codes');
            }
    }
    return '';
}


/**
 * Validates the discount code on the different form
 *
 */
function pms_dc_add_form_discount_error(){

    if ( !empty($_POST['discount_code']) && !empty($_POST['subscription_plans']) ) {

        $code                 = sanitize_text_field( trim( $_POST['discount_code'] ) );
        $subscription_plan_id = (int)$_POST['subscription_plans'];

        $error = pms_dc_get_discount_error( $code, $subscription_plan_id );

        if ( !empty($error) ) {
            pms_errors()->add('discount_error', $error);
        }
    }
}
add_action( 'pms_register_form_validation',                   'pms_dc_add_form_discount_error' );
add_action( 'pms_new_subscription_form_validation',           'pms_dc_add_form_discount_error' );
add_action( 'pms_upgrade_subscription_form_validation',       'pms_dc_add_form_discount_error' );
add_action( 'pms_renew_subscription_form_validation',         'pms_dc_add_form_discount_error' );
add_action( 'pms_retry_payment_subscription_form_validation', 'pms_dc_add_form_discount_error' );


/**
 * Checks to see if the checkout has a full discount applied and handles the validations
 * for this case.
 *
 * In case there is a full discount the "pay_gate" element is not sent in the $_POST. This case is similar
 * for free plans. If the "pay_gate" elements is missing Paid Member Subscriptions does some validations
 * to see if the selected subscription plan is free. If it is not, it adds some errors.
 *
 * In the case of a full discount the errors will be present, because this validations is done very early in the
 * execution. We will remove this errors if the discount is a full one.
 *
 */
function pms_dc_process_checkout_validation_payment_gateway() {

    if( ! empty( $_POST['pay_gate'] ) )
        return;

    if ( empty( $_POST['discount_code'] ) )
        return;

    $payment_gateway_errors = pms_errors()->get_error_message( 'payment_gateway' );

    if( empty( $payment_gateway_errors ) )
        return;

    $code          = sanitize_text_field( $_POST['discount_code'] );
    $discount_code = pms_get_discount_by_code( $code );

    if( false == $discount_code )
        return;

    // User checked auto-renew checkbox on checkout
    $user_checked_auto_renew = ( ! empty( $_POST['recurring'] ) ? true : false );

    // Get selected subscription plan id
    $subscription_plan_id    = ( ! empty( $_POST['subscription_plans'] ) ? (int)$_POST['subscription_plans'] : 0 );

    // Check if is full discount applied
    $is_full_discount = pms_dc_check_is_full_discount( $code, $subscription_plan_id, $user_checked_auto_renew );

    // If the discount is full, remove the errors for the payment gateways
    if( $is_full_discount )
        pms_errors()->remove( 'payment_gateway' );
    
}
add_action( 'pms_process_checkout_validations', 'pms_dc_process_checkout_validation_payment_gateway' );


/**
 * Function that returns payment data after applying the discount code (if there are no discount errors)
 *
 *
 */
function pms_dc_register_payment_data_after_discount( $payment_data, $payments_settings ) {

    if ( empty( $_POST['discount_code'] ) )
        return $payment_data;

    $discount = pms_get_discount_by_code( $_POST['discount_code'] );

    if( false == $discount )
        return $payment_data;


    $payment_data['sign_up_amount'] = pms_calculate_discounted_amount( $payment_data['amount'], $discount );

    if( false == $payment_data['recurring'] )
        $payment_data['amount'] = $payment_data['sign_up_amount'];

    if( true == $payment_data['recurring'] && ! empty( $discount->recurring_payments ) )
        $payment_data['amount'] = $payment_data['sign_up_amount'];


    // Save corresponding discount code for the payment in the db
    if ( class_exists( 'PMS_Payment' ) ) {

        /**
         * Add the discount code to the payment_data
         *
         */
        $payment_data['discount_code'] = $discount->code;

        $payment = pms_get_payment( $payment_data['payment_id'] );
        $payment->update( array( 'discount_code' => $discount->code ) );
    }

    // Update payment amount if it was discounted
    if( ! is_null( $payment_data['sign_up_amount'] ) ) {

        $payment = pms_get_payment( $payment_data['payment_id'] );

        $data = array(
            'amount' => $payment_data['sign_up_amount'],
            'status' => ( $payment_data['sign_up_amount'] == 0 ? 'completed' : $payment->status )
        );

        $payment->update( $data );

    }

    return $payment_data;

}
add_filter( 'pms_register_payment_data', 'pms_dc_register_payment_data_after_discount', 11, 2 );


/**
 * Modifies the billing amount on the checkout subscription data to the discounted value
 *
 * @param array $subscription_data
 * @param array $checkout_data
 *
 * @return array
 *
 */
function pms_dc_modify_subscription_data_billing_amount( $subscription_data = array(), $checkout_data = array() ) {

    if ( empty( $_POST['discount_code'] ) )
        return $subscription_data;

    if( empty( $subscription_data ) )
        return array();

    if( ! $checkout_data['is_recurring'] )
        return $subscription_data;

    // Get discount
    $discount = pms_get_discount_by_code( $_POST['discount_code'] );

    if( false == $discount )
        return $subscription_data;

    if( empty( $discount->recurring_payments ) )
        return $subscription_data;


    /**
     * If the subscription has a set billing amount, calculate the discounted price from it
     * and modify the billing amount with the discounted one
     *
     */
    if( ! empty( $subscription_data['payment_gateway'] ) && ! empty( $subscription_data['billing_amount'] ) ) {

        $discounted_amount = pms_calculate_discounted_amount( $subscription_data['billing_amount'], $discount );

        $subscription_data['billing_amount'] = $discounted_amount;

    /**
     * If the subscription does not have a billing amount set, calculate if based on the attached
     * subscription plan's price
     *
     */
    } else {

        $subscription_plan = pms_get_subscription_plan( $subscription_data['subscription_plan_id'] );

        $discounted_amount = pms_calculate_discounted_amount( $subscription_plan->price, $discount );

    }

    /**
     * If the recurring discounted amount is zero (full discount), it means basically that 
     * no payments should be made for this subscription and it should be set as unlimited
     *
     */
    if( $discounted_amount == 0 ) {

        $subscription_data['expiration_date'] = '';
        $subscription_data['status']          = 'active';

    }

    return $subscription_data;

}
add_filter( 'pms_process_checkout_subscription_data', 'pms_dc_modify_subscription_data_billing_amount', 10, 2 );


/**
 * Function that updates discount data after it has been used
 *
 * 
 */
function pms_dc_update_discount_data_after_use( $payment_id, $data, $old_data ) {

    // Get discount code used for the payment
    if( !empty( $data['status'] ) && $data['status'] == 'completed' ) {
        if( !empty( $payment_id ) && function_exists( 'pms_get_payment' ) ) {
            $payment = pms_get_payment( $payment_id );
            $code    = $payment->discount_code;
        }
    }

    if ( !empty($code) ) { // the payment used a discount code

        $discount_meta = PMS_Discount_Codes_Meta_Box::get_discount_meta_by_code( $code );

        if ( !empty($discount_meta) ) {  // the discount code exists

            if ( isset($discount_meta['pms_discount_uses'][0]) )
                $discount_meta['pms_discount_uses'][0]++;
            else
                $discount_meta['pms_discount_uses'][0] = 1;

            $discount_ID = PMS_Discount_Codes_Meta_Box::get_discount_ID_by_code( $code );

            if ( !empty($discount_ID) ) {
                update_post_meta($discount_ID, 'pms_discount_uses', $discount_meta['pms_discount_uses'][0]);

                if( ! empty( $discount_meta['pms_discount_max_uses'][0] ) && $discount_meta['pms_discount_uses'][0] >= $discount_meta['pms_discount_max_uses'][0])
                    PMS_Discount_Code::deactivate($discount_ID);
            }
            
        }
    }

}
add_action( 'pms_payment_update', 'pms_dc_update_discount_data_after_use', 10, 3 );


if( class_exists( 'pms_PluginUpdateChecker' ) ) {
    $slug = 'discount-codes';
    $localSerial = get_option( $slug . '_serial_number');
    $pms_nmf_update = new pms_PluginUpdateChecker('http://updatemetadata.cozmoslabs.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLPMSDC', __FILE__, $slug );
}

function pms_dc_init_translation() {
    $current_theme = wp_get_theme();
    if( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root().'/'. $current_theme->stylesheet .'/local_pms_lang' ) )
        load_plugin_textdomain( 'pms-add-on-discount-codes', false, basename( dirname( __FILE__ ) ).'/../../themes/'.$current_theme->stylesheet.'/local_pb_lang' );
    else
        load_plugin_textdomain( 'pms-add-on-discount-codes', false, basename(dirname(__FILE__)) . '/translation/' );
}
add_action( 'init', 'pms_dc_init_translation', 8 );
