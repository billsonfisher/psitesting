<?php

/* PREVENT THE USER FROM ACCESSING THE WP REGISTRATION PAGE - THIS DIRECTS THEM TO THE PROPER WEBSITE REGISTRATION PAGE */
function my_registration_page_redirect()
{
    global $pagenow;
    if ( ( strtolower($pagenow) == 'wp-login.php') && ( strtolower( $_GET['action']) == 'register' ) ) {
        wp_redirect( home_url('/purchase-your-practice-tests-here'));
    }
}
add_filter( 'init', 'my_registration_page_redirect' );

/* PREVENT THE USER FROM ACCESSING THE WP LOGIN PAGE - THIS DIRECTS THEM TO THE PROPER WEBSITE LOGIN PAGE */
function my_login_page_redirect()
{
    global $pagenow;
 if( 'wp-login.php' == $pagenow ) {
        wp_redirect( home_url('/sign-in'));
    }
}
add_filter( 'init', 'my_login_page_redirect' );

/* THIS REDIRECTS THE USER WHEN THEY HAVE SUCCESSFULLY LOGGED IN */
add_filter( 'login_redirect', 'ckc_login_redirect' );
function ckc_login_redirect() {
    // Change this to the url to Updates page.
    return home_url( '/my-test-dashboard' );
}

/* THIS CHANGES THE TEXT IN THE SUBMIT BOX FOR THE PAID MEMBER SUBSCRIPTION REGISTRATION PAGE */
add_filter('pms_register_form_submit_text', 'pmsc_change_register_submit_text');
function pmsc_change_register_submit_text() {
	return 'Continue';
}

/* THIS CHANGES THE TEXT IN THE SUBMIT BOX FOR THE PAID MEMBER SUBSCRIPTION NEW SUBSCRIPTION PAGE */
add_filter('pms_new_subscription_form_submit_text', 'pmsc_change_new_subscription_submit_text');
function pmsc_change_new_subscription_submit_text() {
	return 'Continue';
}












?>