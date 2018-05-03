<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;


/**
 * Returns the output for the add new subscription members subpage
 *
 * @param string $output
 *
 * @return string
 *
 */
function pms_msu_output_add_new_subscription_subpage( $output = '' ) {

    if( empty( $_GET['member_id'] ) )
        return $output;

    if( empty( $_GET['subpage'] ) || $_GET['subpage'] != 'add_subscription' )
        return $output;

    
    ob_start();

    if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/views/view-page-members-add-new-edit-subscription.php' ) )
        include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/views/view-page-members-add-new-edit-subscription.php';

    $output = ob_get_contents();

    ob_clean();

    return $output;

}
add_filter( 'pms_submenu_page_members_output', 'pms_msu_output_add_new_subscription_subpage' );


/*
 * Return the number of subscription plan groups
 *
 */
function pms_get_subscription_plan_groups_count() {

    $subscription_plan_posts = get_posts( array( 'post_type' => 'pms-subscription', 'numberposts' => -1, 'post_parent' => 0, 'post_status' => 'any' ) );

    return count( $subscription_plan_posts );

}


/*
 * Add new button for subscription plans allows you to add top level subscription plan
 *
 */
function pms_msu_add_subscription_plan_action( $action ) {
    return 'allow';
}
add_filter( 'pms_action_add_new_subscription_plan', 'pms_msu_add_subscription_plan_action' );


/*
 * Add the "Add New Subscription" button on members add/edit list table
 *
 */
function pms_msu_member_subscription_list_table_add_new_button( $which, $member, $existing_subscriptions ) {

    if( $which == 'bottom' ) {

        $subscription_groups_count = pms_get_subscription_plan_groups_count();

        if( ( $subscription_groups_count > 1 && count( $member->subscriptions ) < $subscription_groups_count ) ) {
            echo '<a href="' . add_query_arg( array( 'page' => 'pms-members-page', 'subpage' => 'add_subscription', 'member_id' => $member->user_id ), admin_url( 'admin.php' ) ) . '" class="button-secondary" style="display: inline-block;">' . __( 'Add New Subscription', 'paid-member-subscriptions' ) . '</a>';
        }

        echo '<input id="pms-subscription-groups-count" type="hidden" value="' . $subscription_groups_count . '" />';

    }

}
add_action( 'pms_member_subscription_list_table_extra_tablenav', 'pms_msu_member_subscription_list_table_add_new_button', 10, 3 );


/*
 * Replace the default message when a user has a subscription attached with
 * a form that allows the user to subscribe to extra plans
 *
 */
function pms_msu_replace_message_subscription_form( $message, $atts, $member ) {

    if( $member->get_subscriptions_count() < pms_get_subscription_plan_groups_count() && defined('PMS_PLUGIN_DIR_PATH') ) {

        ob_start();

        include_once PMS_PLUGIN_DIR_PATH . 'includes/views/shortcodes/view-shortcode-register-form.php';

        $message = ob_get_contents();
        ob_end_clean();

    }

    return apply_filters( 'pms_msu_new_subscription_form_already_a_member_message', $message, $atts, $member );

}
add_filter( 'pms_register_form_already_a_member_message', 'pms_msu_replace_message_subscription_form', 25, 3 );