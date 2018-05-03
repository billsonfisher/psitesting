<?php
/*
 * Extends PMS Shortcodes class
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;


Class PMS_MSU_Shortcodes extends PMS_Shortcodes {

    /*
     * Hook methods on init
     *
     */
    public static function init() {

        remove_shortcode( 'pms-subscriptions' );
        add_shortcode( 'pms-subscriptions', __CLASS__ . '::subscriptions_form' );

    }

    /*
     * Overwrite new subscription form from parent class
     *
     */
    public static function subscriptions_form( $atts ) {

        $atts = shortcode_atts( array(
            'subscription_plans' => array(),
            'exclude'            => array(),
            'selected'           => ''
        ), $atts );


        /*
         * Sanitize attributes
         */
        if( ! empty( $atts['subscription_plans'] ) )
            $atts['subscription_plans'] = array_map( 'trim', explode(',', $atts['subscription_plans'] ) );


        // Start catching the contents of the new subscription form
        ob_start();

        if( is_user_logged_in() ) {

            $member = pms_get_member( pms_get_current_user_id() );

            // Exclude subscription
            if( $member->get_subscriptions_count() > 0 ) {
                foreach( $member->subscriptions as $member_subscription )
                    array_push( $atts['exclude'], $member_subscription['subscription_plan_id'] );
            }


            // Check to see if the member is subscribed to all subscription plans provided
            $array_dif = array_diff( $atts['subscription_plans'], $member->get_subscriptions_ids() );

            // Display the form where the user can subscribe to new plans if he/she is not subscribed to
            // every subscription plan group/tree
            if( ( $member->get_subscriptions_count() >= 0 && $member->get_subscriptions_count() < pms_get_subscription_plan_groups_count() ) && ( empty( $atts['subscription_plans'] ) || !empty( $array_dif ) ) ) {

                include_once PMS_PLUGIN_DIR_PATH . 'includes/views/shortcodes/view-shortcode-new-subscription-form.php';

            // If the user is subscribed to all possible plans display the pms-account so he/she can view hes/hers subscriptions
            } else {

                echo apply_filters( 'pms_subscriptions_form_already_a_member', do_shortcode( '[pms-account show_edit_profile="no"]' ), $atts, $member );

            }

        } else {

            echo apply_filters( 'pms_subscriptions_form_not_logged_in_message', __( 'Only registered users can see this information.', 'paid-member-subscriptions' ) );

        }

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }

}
add_action( 'init', array( 'PMS_MSU_Shortcodes', 'init' ) );