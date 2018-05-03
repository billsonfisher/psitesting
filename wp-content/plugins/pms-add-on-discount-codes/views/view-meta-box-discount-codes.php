<?php
/*
 * HTML output for the Discount Codes details meta-box
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

?>

<?php do_action( 'pms_view_meta_box_discount_codes_top', $discount->id ); ?>

<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-code" class="pms-meta-box-field-label"><?php echo __( 'Promotion Code / Voucher', 'pms-add-on-discount-codes' ); ?></label>

    <input type="text" id="pms-discount-code" name="pms_discount_code" value="<?php echo $discount->code; ?>" />

    <p class="description"><?php echo __( 'Enter the code for the discount. For example: 50percent', 'pms-add-on-discount-codes' ); ?></p>

</div>

<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-type" class="pms-meta-box-field-label"><?php echo __( 'Type', 'pms-add-on-discount-codes' ); ?></label>

    <select id="pms-discount-type" name="pms_discount_type">
        <option value="percent" <?php selected( 'percent', $discount->type, true ); ?>><?php echo __( 'Percent', 'pms-add-on-discount-codes' ); ?></option>
        <option value="fixed" <?php selected( 'fixed', $discount->type, true ); ?>><?php echo __( 'Fixed amount', 'pms-add-on-discount-codes' ); ?></option>
    </select>
    <p class="description"><?php echo __( 'The type of discount to apply for the purchase.', 'pms-add-on-discount-codes' ); ?></p>

</div>


<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-amount" class="pms-meta-box-field-label"><?php echo __( 'Amount', 'pms-add-on-discount-codes' ); ?></label>

    <input type="text" id="pms-discount-amount" name="pms_discount_amount" class="small" value="<?php echo $discount->amount; ?>" /> <span class="pms-discount-currency"> <?php echo (!empty( $settings['payments']['currency'] )) ? $settings['payments']['currency'] : '' ?></span>

    <p class="description"><?php echo __( 'Enter the discount amount.', 'pms-add-on-discount-codes' ); ?></p>

</div>

<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-subscriptions" class="pms-meta-box-field-label"><?php echo __( 'Subscription(s)', 'pms-add-on-discount-codes' ); ?></label>

    <?php
    // Check if there are any subscription plans
    if ( function_exists('pms_get_subscription_plans') ){

        $subscription_plans = pms_get_subscription_plans();

        if( !empty( $subscription_plans ) ) {
            // Display active subscriptions
            foreach ( pms_get_subscription_plans() as $subscription_plan) {
                $checked = '';
                if ( in_array( $subscription_plan->id, explode(',', $discount->subscriptions) ) ) $checked = "checked";

                echo ' <label class="pms-meta-box-checkbox-label"> <input type="checkbox" name="pms_discount_subscriptions[]" ' . $checked . ' value="' . $subscription_plan->id . '" /> ' . $subscription_plan->name.' </label>';
            }

            echo '<p class="description">' . __( 'Select the subscription(s) to which the discount should be applied.', 'pms-add-on-discount-codes' ) . '</p>';

        } else {

            echo '<p class="description">' . sprintf( __( 'You do not have any active Subscription Plans yet. Please create them <a href="%s">here</a>.', 'pms-add-on-discount-codes' ), admin_url( 'edit.php?post_type=pms-subscription' ) ) . '</p>';

        }
    }
    ?>


</div>

<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-max-uses" class="pms-meta-box-field-label"><?php echo __( 'Maximum Uses', 'pms-add-on-discount-codes' ); ?></label>

    <input type="text" id="pms-discount-max-uses" name="pms_discount_max_uses" class="small" value="<?php echo $discount->max_uses; ?>" />

    <p class="description"><?php echo __( 'Maximum number of times this discount can be used. Enter 0 for unlimited.', 'pms-add-on-discount-codes' ); ?></p>

</div>


<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-start-date" class="pms-meta-box-field-label"><?php echo __( 'Start Date','pms-add-on-discount-codes' ); ?></label>

    <input type="text" id="pms-discount-start-date" name="pms_discount_start_date" class="pms_datepicker" value="<?php echo $discount->start_date; ?>">

    <p class="description"><?php echo __( 'Select the start date for the discount (yyyy-mm-dd). Leave blank for no start date.', 'pms-add-on-discount-codes' ); ?></p>

</div>


<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-expiration-date" class="pms-meta-box-field-label"><?php echo __( 'Expiration Date','pms-add-on-discount-codes' ); ?></label>

    <input type="text" id="pms-discount-expiration-date" name="pms_discount_expiration_date" class="pms_datepicker" value="<?php echo $discount->expiration_date; ?>">

    <p class="description"><?php echo __( 'Select the expiration date for the discount (yyyy-mm-dd). Leave blank for no expiration.', 'pms-add-on-discount-codes' ); ?></p>

</div>


<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-status" class="pms-meta-box-field-label"><?php echo __( 'Status', 'pms-add-on-discount-codes' ); ?></label>

    <select id="pms-discount-status" name="pms_discount_status">
        <option value="active" <?php selected( 'active', $discount->status, true  ); ?>><?php echo __( 'Active', 'pms-add-on-discount-codes' ); ?></option>
        <option value="inactive" <?php selected( 'inactive', $discount->status, true  ); ?>><?php echo __( 'Inactive', 'pms-add-on-discount-codes' ); ?></option>
    </select>
    <p class="description"><?php echo __( 'Select discount code status.', 'pms-add-on-discount-codes' ); ?></p>

</div>

<?php
    // Check if we have recurring payments enabled
    if ( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'recurring_payments' ) ) {
?>
<div class="pms-meta-box-field-wrapper">

    <label for="pms-discount-recurring-payments" class="pms-meta-box-field-label"><?php echo __( 'Recurring Payments','pms-add-on-discount-codes' ); ?></label>

    <input type="checkbox" id="pms-discount-recurring-payments" name="pms_discount_recurring_payments" <?php echo $discount->recurring_payments; ?>  value="<?php echo $discount->recurring_payments; ?>">

    <span class="description"><?php echo __( 'Apply discount to all future recurring payments (not just the first one).', 'pms-add-on-discount-codes' ); ?></span>

</div>
<?php } ?>

<?php do_action( 'pms_view_meta_box_discount_codes_bottom', $discount->id ); ?>