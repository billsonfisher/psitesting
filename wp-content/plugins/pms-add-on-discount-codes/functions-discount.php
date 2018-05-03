<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;


/**
 * Wrapper function that returns a discount code object
 *
 * @param mixed $id_or_post
 *
 * @return PMS_Discount_Code
 *
 */
function pms_get_discount( $id_or_post ) {

    return new PMS_Discount_Code( $id_or_post );

}


/**
 * Wrapper function that returns a discount code object by the
 * code provided, not by the id or the post
 *
 * @param string $code
 *
 * @return PMS_Discount_Code | false
 *
 */
function pms_get_discount_by_code( $code = '' ) {

    if( empty( $code ) )
        return false;

    $code = sanitize_text_field( $code );

    $discount_codes = get_posts( array(
        'post_type'   => 'pms-discount-codes',
        'post_status' => 'any',
        'meta_key'    => 'pms_discount_code',
        'meta_value'  => $code
    ));

    if ( ! empty($discount_codes) && ( $discount_codes[0]->post_status == 'active') ) { // discount code exists and is active
        return new PMS_Discount_Code( $discount_codes[0] );
    }

    return false;

}


/**
 * Calculates and returns the discounted amount for a given amount
 * and discount code
 *
 * @param int $amount
 * @param PMS_Discount_Code $discount
 *
 * @return int
 *
 */
function pms_calculate_discounted_amount( $amount, $discount ) {

    if( ! is_a( $discount, 'PMS_Discount_Code' ) )
        return $amount;

    if ( $discount->type == 'percent' )
        $amount = $amount * ( 100 - (float)$discount->amount ) / 100;

    if ( $discount->type == 'fixed' )
        $amount = $amount - (float)$discount->amount;

    //If it's a negative amount make it zero
    if( $amount < 0 )
        $amount = 0;

    $amount = round( $amount, 2 );

    return $amount;

}