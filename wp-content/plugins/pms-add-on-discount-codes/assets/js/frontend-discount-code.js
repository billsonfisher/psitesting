/*
 * JavaScript for front-end discount code display
 *
 */
jQuery(document).ready(function($) {

    // Cache the parent form of the apply discount button
    var $pms_form;

    // Cache the value of the last checked discount code
    var last_checked_discount_code;

    /**
     * Trigger automatically "Apply" discount button when the user already entered a discount code and selects another subscription plan, or checks the "Automatically renew subscription" checkbox.
     * This will update the discount message shown below the field.
     *
     */
    $('.pms-subscription-plan input[type="radio"][name="subscription_plans"]').click(function(){

        // If subscription is not free and discount code field is not empty
        if (  ( $(this).attr("data-price") > 0) && ( $('#pms_subscription_plans_discount_code').length > 0 ) ){

            $('#pms-apply-discount').trigger('click');

        } else {

            $('#pms-subscription-plans-discount-messages-wrapper').hide();
            $('#pms-subscription-plans-discount-messages').hide();

        }

    });

    $('.pms-subscription-plan-auto-renew input[type="checkbox"][name="pms_recurring"]').click(function(){

        // If discount code field is not empty
        if ( $('#pms_subscription_plans_discount_code').length > 0 ){

            $('#pms-apply-discount').trigger('click');

        } else {

            $('#pms-subscription-plans-discount-messages-wrapper').hide();
            $('#pms-subscription-plans-discount-messages').hide();

        }

    });


    /**
     * Handles discount code validation when the user clicks the "Apply" discount button
     *
     */
    $('#pms-apply-discount').click(function(e){
        
        e.preventDefault();

        // If undefined, cache the parent form
        if( typeof $pms_form == 'undefined' )
            $pms_form = $(this).closest('form');

        if( $('#pms_subscription_plans_discount_code').val() == '' ) {
            $('#pms-subscription-plans-discount-messages-wrapper').fadeOut( 350 );
            $('#pms-subscription-plans-discount-messages').fadeOut( 350 )
            return false;
        }

        var $subscription_plan = '';

        $('.pms-subscription-plan input[type="radio"]').each(function(){
            if($(this).is(':checked')){
                $subscription_plan = $(this);
            }
        });

        if( $subscription_plan == '' ) {
            $subscription_plan = $('input[type=hidden][name=subscription_plans]');
        }

        // Cache the discount code
        last_checked_discount_code = $('#pms_subscription_plans_discount_code').val();

        var data = {
            'action'      : 'pms_discount_code',
            'code'        : $('#pms_subscription_plans_discount_code').val(),
            'subscription': $subscription_plan.val(),
            'recurring'   : $('input[name="pms_recurring"]:checked').val()
        };

        
        $('#pms-subscription-plans-discount-messages-wrapper').show();
        $('#pms-subscription-plans-discount-messages').fadeOut( 350, function() {
            $('#pms-subscription-plans-discount-messages-loading').fadeIn( 350 );    
        });
        

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post( pms_discount_object.ajax_url, data, function(response) {

            if( response.success != undefined ) {

                // Add success message
                $('#pms-subscription-plans-discount-messages').removeClass('pms-discount-error');
                $('#pms-subscription-plans-discount-messages').addClass('pms-discount-success');

                $('#pms-subscription-plans-discount-messages-loading').fadeOut( 350, function() {
                    $('#pms-subscription-plans-discount-messages').html(response.success.message).fadeIn( 350 );
                });

                // Hide payment fields
                if( response.is_full_discount )
                    hide_payment_fields( $pms_form );
                else
                    show_payment_fields( $pms_form );
                
            }

            if( response.error != undefined ) {

                // Add error message
                $('#pms-subscription-plans-discount-messages').removeClass('pms-discount-success');
                $('#pms-subscription-plans-discount-messages').addClass('pms-discount-error');

                $('#pms-subscription-plans-discount-messages-loading').fadeOut( 350, function() {
                    $('#pms-subscription-plans-discount-messages').html(response.error.message).fadeIn( 350 );
                });

                // Show payment fields
                show_payment_fields( $pms_form );

            }

        });

    });

    /**
     * If there is a discount code value already set on document ready
     * apply it
     *
     */
    if( $('input[name=discount_code]').val() != '' )
        $('#pms-apply-discount').trigger('click');

    /**
     * When losing focus of the discount code field, directly apply the discount
     *
     */
    $('input[name=discount_code]').on( 'blur', function() {

        if( last_checked_discount_code != $('input[name=discount_code]').val() )
            $('#pms-apply-discount').trigger('click');

    });

    /**
     * Clones and caches the wrappers for the payment gateways and the credit card / billing information
     * It replaces these wrappers with empy spans that represent the wrappers
     *
     */
    function hide_payment_fields( $form ) {
        
        if( typeof $form.pms_paygates_wrapper == 'undefined' )
            $form.pms_paygates_wrapper = $form.find('#pms-paygates-wrapper').clone();

        $form.find('#pms-paygates-wrapper').replaceWith('<span id="pms-paygates-wrapper">');

        if( typeof $form.pms_credit_card_information == 'undefined' )
            $form.pms_credit_card_information = $form.find('.pms-credit-card-information').clone();

        $form.find('.pms-credit-card-information').replaceWith('<span class="pms-credit-card-information">');

        if( typeof $form.pms_billing_details == 'undefined' )
            $form.pms_billing_details = $form.find('.pms-billing-details').clone();

        $form.find('.pms-billing-details').replaceWith('<span class="pms-billing-details">');

    }


    /**
     * It replaces the placeholder spans, that represent the payment gateway and the credit card
     * and billing information, with the cached wrappers that contain the actual fields
     *
     */
    function show_payment_fields( $form ) {

        if( typeof $form.pms_paygates_wrapper != 'undefined' )
            $form.find('#pms-paygates-wrapper').replaceWith( $form.pms_paygates_wrapper );

        if( typeof $form.pms_credit_card_information != 'undefined' )
            $form.find('.pms-credit-card-information').replaceWith( $form.pms_credit_card_information );

        if( typeof $form.pms_billing_details != 'undefined' )
            $form.find('.pms-billing-details').replaceWith( $form.pms_billing_details );

    }


    /*
     * Show / Hide discount code field if a free plan is selected
     *
     */
    toggle_discount_box( $('input[type=radio][name=subscription_plans]:checked') );

    $('input[type=radio][name=subscription_plans]').click( function() {
        toggle_discount_box( $(this) );
    });

    /*
     * Show / Hide discount code field if a free plan is selected
     *
     */
    function toggle_discount_box( $element ) {

        var selector = '#pms-subscription-plans-discount';

        if( $element.attr('data-price') == '0' ) {
            $(selector).hide();
        } else {
            $(selector).show();
        }

    }

});
