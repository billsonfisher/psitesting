<?php
/*
 * PayPal Express class
 *
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

/**
 * Legacy code for the PayPal Express payment gateway.
 * 
 * In this version the subscription would be created within PayPal and everything
 * would be handled on our side by the IPNs sent by PayPal.
 *
 * The new system that extends this code handles the subscriptions on the users website
 * and doesn't use the IPN system, just the API
 *
 */
Class PMS_Payment_Gateway_PayPal_Express_Legacy extends PMS_Payment_Gateway {

    protected $api_endpoint;

    protected $paypal_express_checkout;

    private $response_token;

    public function init() {

        // Set test mode if for some reason it is not set
        if( is_null( $this->test_mode ) )
            $this->test_mode = pms_is_payment_test_mode();


        // Set endpoint and checkout redirect
        if( $this->test_mode ) {

            $this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
            $this->paypal_express_checkout = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';

        } else {

            $this->api_endpoint = 'https://api-3t.paypal.com/nvp';
            $this->paypal_express_checkout = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';

        }


        // Get request token if any
        $this->response_token = sanitize_text_field( ( !empty( $_GET['token'] ) ? $_GET['token'] : ( !empty( $_POST['pms_token'] ) ? $_POST['pms_token'] : '' ) ) );

    }


    /*
     * Send payment information to PayPal and prepare an express checkout
     *
     */
    public function process_sign_up() {

        // Do nothing if the payment id wasn't sent
        if( $this->payment_id === false )
            return;

        // Get API credentials
        $api_credentials = pms_get_paypal_api_credentials();

        if( !$api_credentials )
            return;

        $request_fields = array(
            'METHOD'                        => 'SetExpressCheckout',
            'USER'                          => $api_credentials['username'],
            'PWD'                           => $api_credentials['password'],
            'SIGNATURE'                     => $api_credentials['signature'],
            'VERSION'                       => 68,
            'EMAIL'                         => $this->user_email,
            'PAYMENTREQUEST_0_AMT'          => $this->amount,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $this->currency,
            'PAYMENTREQUEST_0_CUSTOM'       => $this->payment_id,
            'PAYMENTREQUEST_0_NOTIFYURL'    => home_url() . '/?pay_gate_listener=epipn',
            'DESC'                          => $this->subscription_plan->name,
            'RETURNURL'                     => add_query_arg( array( 'pms-gateway' => base64_encode( 'paypal_express' ), 'pmstkn' => wp_create_nonce( 'pms_payment_process_confirmation' ) ), pms_get_current_page_url( true ) ),
            'CANCELURL'                     => pms_get_current_page_url()
        );

        // Handle recurring payments
        if( $this->recurring == 1 && $this->subscription_plan->duration != 0 ) {

            $request_fields = array_merge( $request_fields, array(
                'L_BILLINGTYPE0'                    => 'RecurringPayments',
                'L_BILLINGAGREEMENTDESCRIPTION0'    => $this->subscription_plan->name
            ));

        }


        /**
         * Because the PAYMENTREQUEST_0_CUSTOM value cannot be more than 256 characters long
         * and that some users have very long URL's we cannot pass all details needed when the users is 
         * returned from PayPal to the website for payment confirmation, that's why we save them
         * into a transient
         *
         */
        $set_express_checkout_custom = array(
            'payment_id'     => $this->payment_id,
            'sign_up_amount' => $this->sign_up_amount,
            'redirect_url'   => $this->redirect_url
        );

        set_transient( 'pms_set_express_checkout_custom_' . $this->payment_id, $set_express_checkout_custom, 2 * DAY_IN_SECONDS );


        // Post PayPal
        $request = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $request_fields ) );

        if( is_wp_error( $request ) ) {

            $error = __( 'Something went wrong when connecting to PayPal', 'paid-member-subscriptions' );

        } elseif( isset( $request['response'] ) && $request['response']['code'] == 200 ) {

            // Get the body string in an array form
            parse_str( $request['body'], $body );

            // Get error messages
            if( strtolower( $body['ACK'] ) == 'failure' ) {

                if( !current_user_can( 'manage_options' ) )
                    $error = __( 'Something went wrong when connecting to PayPal.', 'paid-member-subscriptions' );
                else
                    $error = sprintf( __( 'PayPal could not generate the needed token. %1$s %2$s', 'paid-member-subscriptions' ), ( isset( $body['L_SHORTMESSAGE0'] ) ? $body['L_SHORTMESSAGE0'] : '' ), ( isset( $body['L_LONGMESSAGE0'] ) ? $body['L_LONGMESSAGE0'] : '' ) );


            // Redirect to checkout if all is well
            } elseif( strpos( strtolower( $body['ACK'] ), 'success' ) !== false ) {

                $redirect = add_query_arg( array( 'token' => $body['TOKEN'] ), $this->paypal_express_checkout );

                do_action( 'pms_before_paypal_redirect', $redirect, $this, get_option( 'pms_settings' ) );

                if( isset( $_POST['pmstkn'] ) ) {
                    wp_redirect($redirect);
                    exit;
                }

            }

        } else {

            $error = __( 'Something went wrong when connecting to PayPal', 'paid-member-subscriptions' );

        }

        // Add errors if there are any
        if( isset($error) )
            pms_errors()->add( 'subscription_plans', $error );

    }


    /*
     * Handles the actions made by the user in order to complete the payment on the site
     *
     */
    public function process_confirmation() {


        // Display confirmation table and form
        if( empty( $_POST['pmstkn'] ) && !empty( $_GET['token'] ) && !empty( $_GET['PayerID'] ) ) {

            add_filter( 'the_content', array( $this, 'confirmation_form' ), 998 );

        // Make payment
        } elseif( !empty( $_POST['pmstkn'] ) && wp_verify_nonce( $_POST['pmstkn'], 'pms_payment_process_confirmation' ) ) {

            /*
             * Get payment data
             */
            $token = ( isset( $_POST['pms_token'] ) ? sanitize_text_field( $_POST['pms_token'] ) : '' );

            // Get API credentials
            $api_credentials      = pms_get_paypal_api_credentials();

            // Get checkout details from PayPal
            $pms_checkout_details = $this->get_checkout_details();

            // Get payment
            $payment              = pms_get_payment( $pms_checkout_details['payment_data']['payment_id'] );
            $subscription_plan    = pms_get_subscription_plan( $payment->subscription_id );


            /*
             * Make a recurring payment profile with PayPal
             */
            if( isset( $_POST['pms_is_recurring'] ) && $_POST['pms_is_recurring'] == 1 ) {

                // Update payment type
                $payment->update( array( 'type' => 'recurring_payment_profile_created' ) );

                // Prepare post fields
                $request_fields = array(
                    'METHOD'             => 'CreateRecurringPaymentsProfile',
                    'USER'               => $api_credentials['username'],
                    'PWD'                => $api_credentials['password'],
                    'SIGNATURE'          => $api_credentials['signature'],
                    'VERSION'            => 69,
                    'TOKEN'              => $token,
                    'PROFILESTARTDATE'   => date( "Y-m-d\Tg:i:s", strtotime( "+" . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit, time() ) ),
                    'BILLINGPERIOD'      => ucfirst( $subscription_plan->duration_unit ),
                    'BILLINGFREQUENCY'   => $subscription_plan->duration,
                    'AMT'                => $pms_checkout_details['AMT'],
                    'INITAMT'            => $pms_checkout_details['AMT'],
                    'CURRENCYCODE'       => $pms_checkout_details['PAYMENTREQUEST_0_CURRENCYCODE'],
                    'DESC'               => $subscription_plan->name
                );

                // If a discount has been applied to the payment
                // create a trial with the discounted price
                if( isset( $pms_checkout_details['payment_data']['sign_up_amount'] ) && !is_null( $pms_checkout_details['payment_data']['sign_up_amount'] ) ) {

                    $request_fields['INITAMT']          = $pms_checkout_details['payment_data']['sign_up_amount'];

                }


                $request = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $request_fields ) );

                if( !is_wp_error( $request ) && !empty( $request['body'] ) && !empty( $request['response']['code'] ) && $request['response']['code'] == 200 ) {

                    parse_str( $request['body'], $body );

                    if( strpos( strtolower( $body['ACK'] ), 'success' ) !== false ) {

                        $payment_profile_id = trim( $body['PROFILEID'] );
                        $payment->update( array( 'profile_id' => $payment_profile_id ) );

                        // Redirect the user to the correct page
                        if( isset( $pms_checkout_details['payment_data']['redirect_url'] ) ) {
                            wp_redirect( add_query_arg( array( 'pms_gateway_payment_id' => base64_encode($payment->id), 'pmsscscd' => base64_encode('subscription_plans') ), $pms_checkout_details['payment_data']['redirect_url'] ) );
                            exit;
                        }

                    }

                }


                // End of CreateRecurringPaymentsProfile flow
            } else {

                // Update payment type
                $payment->update( array( 'type' => 'expresscheckout' ) );

                // Prepare post fields
                $request_fields = array(
                    'METHOD'                        => 'DoExpressCheckoutPayment',
                    'USER'                          => $api_credentials['username'],
                    'PWD'                           => $api_credentials['password'],
                    'SIGNATURE'                     => $api_credentials['signature'],
                    'VERSION'                       => 69,
                    'TOKEN'                         => $token,
                    'BUTTONSOURCE'                  => 'Cozmoslabs_SP',
                    'PAYERID'                       => $pms_checkout_details['PAYERID'],
                    'PAYMENTREQUEST_0_AMT'          => $pms_checkout_details['AMT'],
                    'PAYMENTREQUEST_0_CURRENCYCODE' => $pms_checkout_details['PAYMENTREQUEST_0_CURRENCYCODE']
                );


                // Make request
                $request = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $request_fields ) );


                if( !is_wp_error( $request ) && !empty( $request['body'] ) && !empty( $request['response']['code'] ) && $request['response']['code'] == 200 ) {

                    parse_str( $request['body'], $post_data );

                    // Merge post_data on top of checkout details to
                    $post_data = array_merge( $pms_checkout_details, $post_data );

                    if( strpos( strtolower( $post_data['ACK'] ), 'success' ) !== false ) {

                        $payment_data = apply_filters( 'pms_paypal_express_ipn_payment_data', array(
                            'payment_id'     => $payment->id,
                            'user_id'        => $payment->user_id,
                            'type'           => $post_data['PAYMENTINFO_0_TRANSACTIONTYPE'],
                            'status'         => strtolower( $post_data['PAYMENTINFO_0_PAYMENTSTATUS'] ),
                            'transaction_id' => $post_data['PAYMENTINFO_0_TRANSACTIONID'],
                            'amount'         => $post_data['PAYMENTINFO_0_AMT'],
                            'date'           => $post_data['PAYMENTINFO_0_ORDERTIME'],
                            'subscription_id'=> $subscription_plan->id
                        ), $post_data );


                        // If the status is completed update the payment and also activate the member subscriptions
                        if( $payment_data['status'] == 'completed' ) {

                            // Complete payment
                            $payment->update( array( 'status' => $payment_data['status'], 'transaction_id' => $payment_data['transaction_id'] ) );

                            // Redirect upon success
                            if( $this->update_member_subscription_data( $payment_data ) ) {

                                // Redirect user to the correct location
                                if( isset( $pms_checkout_details['payment_data']['redirect_url'] ) ) {
                                    wp_redirect( add_query_arg( array( 'pms_gateway_payment_id' => base64_encode($payment->id), 'pmsscscd' => base64_encode('subscription_plans') ), $pms_checkout_details['payment_data']['redirect_url'] ) );
                                    exit;
                                }

                            }

                        // If payment status is not complete, something happened, so log it in the payment
                        } else {

                            // Add the transaction ID and log data to the log entry
                            $payment->update( array( 'transaction_id' => $payment_data['transaction_id'] ) );
                            $payment->add_log_entry( 'failure', __( 'Express Checkout payment could not be completed successfully', 'paid-member-subscription' ), $this->get_payment_log_data( $post_data ) );

                        }


                    }

                }

                // End of DoExpressCheckoutPayment flow
            }

        }

    }


    /*
     * Return the payment confirmation form
     *
     * @return string
     *
     */
    public function confirmation_form( $content ) {

        global $pms_checkout_details;

        $pms_checkout_details = $this->get_checkout_details();

        if( $pms_checkout_details ) {

            ob_start();

            include( 'views/view-paypal-express-confirmation-form.php' );

            $output = ob_get_contents();
            ob_end_clean();

            return $output;

        } else {

            return $content;

        }

    }


    /*
     * Process IPN responses
     *
     */
    public function process_webhooks() {

        if( !isset( $_GET['pay_gate_listener'] ) || $_GET['pay_gate_listener'] != 'paypal_epipn' )
            return;

        if( !isset( $_POST ) )
            return;


        // Get post data
        $post_data = $_POST;


        /*
         * Set payment data
         */
        $payment_data = array();

        // Get initial payment for the recurring_payment_id found in the IPN
        if( $post_data['txn_type'] == 'recurring_payment_profile_created' || $post_data['txn_type'] == 'recurring_payment' || $post_data['txn_type'] == 'recurring_payment_profile_cancel' ) {

            $payment_profile_id = ( isset( $post_data['recurring_payment_id'] ) ? $post_data['recurring_payment_id'] : null );

            if( is_null( $payment_profile_id ) )
                return;

            // Get initial payment for the profile id
            $payments = pms_get_payments( array( 'type' => 'recurring_payment_profile_created', 'profile_id' => $payment_profile_id ) );
            $payment  = ( !empty( $payments ) ? $payments[0] : null );

            // Exit if no initial payment is found in the db
            if( is_null( $payment ) )
                return;

        }

        // If is recurring first time payment
        if( $post_data['txn_type'] == 'recurring_payment_profile_created' ) {

            $payment_data = array(
                'payment_id'      => $payment->id,
                'user_id'         => $payment->user_id,
                'type'            => 'recurring_payment_profile_created',
                'status'          => ( !empty( $post_data['initial_payment_status'] ) ? strtolower( $post_data['initial_payment_status'] ) : 'completed' ),
                'transaction_id'  => ( !empty( $post_data['initial_payment_status'] ) && !empty( $post_data['initial_payment_txn_id'] ) ? $post_data['initial_payment_txn_id'] : '-' ),
                'profile_id'      => $post_data['recurring_payment_id'],
                'amount'          => $post_data['amount'],
                'date'            => $post_data['time_created'],
                'subscription_id' => $payment->subscription_id
            );

        // If this is a recurring payment
        } elseif( $post_data['txn_type'] == 'recurring_payment' ) {

            $payment_data = array(
                'payment_id'      => 0,
                'user_id'         => $payment->user_id,
                'type'            => 'recurring_payment',
                'status'          => strtolower( $post_data['payment_status'] ),
                'transaction_id'  => $post_data['txn_id'],
                'profile_id'      => $post_data['recurring_payment_id'],
                'amount'          => $post_data['amount'],
                'date'            => $post_data['payment_date'],
                'subscription_id' => $payment->subscription_id
            );

        } elseif( $post_data['txn_type'] == 'recurring_payment_profile_cancel' ) {

            $payment_data = array(
                'payment_id'      => 0,
                'user_id'         => $payment->user_id,
                'type'            => 'recurring_payment_profile_cancel',
                'profile_id'      => $post_data['recurring_payment_id'],
                'subscription_id' => $payment->subscription_id
            );

        } elseif ( ( $post_data['txn_type'] == 'web_accept' ) && ( !empty($post_data['custom']) ) ){
            $payment_data = array(
                'payment_id'    => $post_data['custom'],
                'type'          => 'web_accept_paypal_pro',
                'transaction_id'=> $post_data['txn_id']
            );
        }


        $payment_data = apply_filters( 'pms_paypal_express_ipn_payment_data', $payment_data, $post_data );


        // Depending on the payment data type, or better said IPN response type
        // handle payment and member data
        switch( $payment_data['type'] ) {

            case 'recurring_payment_profile_created':

                if( $payment_data['status'] == 'completed' ) {

                    // Update payment to complete
                    $payment->update( array( 'status' => $payment_data['status'], 'transaction_id' => $payment_data['transaction_id'] ) );

                    // Update member data
                    $this->update_member_subscription_data( $payment_data );

                } else {

                    $payment->update( array( 'transaction_id' => $payment_data['transaction_id'] ) );
                    $payment->add_log_entry( 'failure', __( 'Recurring initial payment could not be completed successfully', 'paid-member-subscription' ), $this->get_payment_log_data( $post_data ) );

                }
                break; // End of case 'recurring_payment_profile_created'


            case 'recurring_payment':

                if( $payment_data['status'] == 'completed' ) {

                    // Add payment and complete it
                    $payment->add($payment_data['user_id'], $payment_data['status'], date('Y-m-d H:i:s'), $payment_data['amount'], $payment_data['subscription_id']);
                    $payment->update(array('type' => $payment_data['type'], 'transaction_id' => $payment_data['transaction_id'], 'profile_id' => $payment_profile_id));

                    // Update member data
                    $this->update_member_subscription_data($payment_data);

                } else {

                    $payment->update( array( 'transaction_id' => $payment_data['transaction_id'] ) );
                    $payment->add_log_entry( 'failure', __( 'Recurring payment could not be completed successfully', 'paid-member-subscription' ), $this->get_payment_log_data( $post_data ) );

                }
                break; // End of case 'recurring_payment'

            case 'recurring_payment_profile_cancel':

                $member              = pms_get_member( $payment_data['user_id'] );
                $member_subscription = $member->get_subscription( $payment_data['subscription_id'] );

                if( !empty( $member_subscription ) )
                    $member->update_subscription( $member_subscription['subscription_plan_id'], $member_subscription['start_date'], $member_subscription['expiration_date'], 'canceled' );

                break; // End of case 'recurring_payment_profile_cancel'

            case 'web_accept_paypal_pro':

                    $payment = pms_get_payment( $payment_data['payment_id'] );

                    if( $payment->is_valid() ) {

                        $payment->update( array('transaction_id' => $payment_data['transaction_id']) );

                    }

                break; // End of case 'web_accept'

            default:
                break;

        }

    }


    /*
     * Handles all the member subscription data flow after a payment is complete
     *
     */
    public function update_member_subscription_data( $payment_data ) {

        if( empty( $payment_data ) || !is_array( $payment_data ) )
            return false;


        // Get post data
        $post_data = $_POST;

        // Update member subscriptions
        $member = pms_get_member( $payment_data['user_id'] );

        // Get all member subscriptions
        $member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => $payment_data['user_id'] ) );

        foreach( $member_subscriptions as $member_subscription ) {

            if( $member_subscription->subscription_plan_id != $payment_data['subscription_id'] )
                continue;

            $subscription_plan = pms_get_subscription_plan( $member_subscription->subscription_plan_id );

            // If subscription is pending it is a new one
            if( $member_subscription->status == 'pending' ) {
                $member_subscription_expiration_date = $subscription_plan->get_expiration_date();

            // This is an old subscription
            } else {

                if( strtotime( $member_subscription->expiration_date ) < time() || $subscription_plan->duration === 0 )
                    $member_subscription_expiration_date = $subscription_plan->get_expiration_date();
                else
                    $member_subscription_expiration_date = date( 'Y-m-d 23:59:59', strtotime( $member_subscription->expiration_date . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) );

            }

            // Update subscription
            $member_subscription->update( array( 
                'expiration_date'    => $member_subscription_expiration_date, 
                'status'             => 'active',
                'payment_profile_id' => ( ! empty( $payment_data['profile_id'] ) ? $payment_data['profile_id'] : '' )
            ));

        }


        /*
         * If the subscription plan id sent by the IPN is not found in the members subscriptions
         * then it could be an update to an existing one
         *
         * If one of the member subscriptions is in the same group as the payment subscription id,
         * the payment subscription id is an upgrade to the member subscription one
         *
         */
        $member_subscriptions_plan_ids = array();

        foreach( $member_subscriptions as $member_subscription ) {
            $member_subscriptions_plan_ids[] = $member_subscription->subscription_plan_id;
        }

        if( ! in_array( $payment_data['subscription_id'], $member_subscriptions_plan_ids ) ) {

            $group_subscription_plans = pms_get_subscription_plans_group( $payment_data['subscription_id'], false );

            if( count( $group_subscription_plans ) > 1 ) {

                // Get current member subscription that will be upgraded
                foreach( $group_subscription_plans as $subscription_plan ) {
                    if( in_array( $subscription_plan->id, $member_subscriptions_plan_ids ) ) {
                        $member_subscription_plan = $subscription_plan;
                        break;
                    }
                }

                if( isset( $member_subscription_plan ) ) {

                    do_action( 'pms_paypal_express_before_upgrade_subscription', $member_subscription_plan->id, $payment_data, $post_data );

                    /**
                     * This should be changed in the future as we will not be relying on the $member object
                     * This is left for now as it has a few hooks inside the remove_subscription method 
                     * that should be handled in the future by the PMS_Member_Subscription class
                     *
                     */
                    $member->remove_subscription( $member_subscription_plan->id );

                    $new_subscription_plan = pms_get_subscription_plan( $payment_data['subscription_id'] );

                    $subscription_data = array(
                        'user_id'              => $payment_data['user_id'],
                        'subscription_plan_id' => $new_subscription_plan->id,
                        'start_date'           => date( 'Y-m-d H:i:s' ),
                        'expiration_date'      => $new_subscription_plan->get_expiration_date(),
                        'status'               => 'active',
                        'payment_profile_id'   => ( ! empty( $payment_data['profile_id'] ) ? $payment_data['profile_id'] : '' )
                    );

                    $subscription = new PMS_Member_Subscription();
                    $subscription->insert( $subscription_data );

                    do_action( 'pms_paypal_express_after_upgrade_subscription', $member_subscription_plan->id, $payment_data, $post_data );
                }

            }

        }

        return true;

    }


    /**
     * Get the details of the checkout that was initiated through SetExpressCheckout
     *
     * @return mixed array | bool false
     *
     */
    public function get_checkout_details() {

        if( empty( $this->response_token ) )
            return false;


        // Get API credentials
        $api_credentials = pms_get_paypal_api_credentials();

        $request_fields = array(
            'METHOD'    => 'GetExpressCheckoutDetails',
            'TOKEN'     => $this->response_token,
            'USER'      => $api_credentials['username'],
            'PWD'       => $api_credentials['password'],
            'SIGNATURE' => $api_credentials['signature'],
            'VERSION'   => 68
        );


        $request = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $request_fields ) );

        if( !is_wp_error( $request ) && !empty( $request['body'] ) && !empty( $request['response']['code'] ) && $request['response']['code'] == 200 ) {

            parse_str( $request['body'], $body );

            if( strpos( strtolower( $body['ACK'] ), 'success' ) !== false ) {

                $payment_id = (int)$body['PAYMENTREQUEST_0_CUSTOM'];

                $set_express_checkout_custom = get_transient( 'pms_set_express_checkout_custom_' . $payment_id );

                if( $set_express_checkout_custom )
                    $body['payment_data'] = $set_express_checkout_custom;
                else
                    $body['payment_data']['payment_id'] = $payment_id;

                return $body;

            } else
                return false;

        } else {

            return false;

        }

    }


    /*
     * Returns the log data that should be saved in case a payment is not completed with
     * the payment gateway
     *
     * @param array $post_data
     *
     * @return array
     *
     */
    public function get_payment_log_data( $post_data ) {

        // Return if no post_data is provided
        if( empty( $post_data ) )
            return array();

        // post_data comes from an API request
        if( isset( $post_data['TOKEN'] ) ) {

            $log_data = array(
                'payment_status' => ( isset( $post_data['PAYMENTINFO_0_PAYMENTSTATUS'] ) ? $post_data['PAYMENTINFO_0_PAYMENTSTATUS'] : '' ),
                'payment_date'   => ( isset( $post_data['PAYMENTINFO_0_ORDERTIME'] ) ? $post_data['PAYMENTINFO_0_ORDERTIME'] : '' ),
                'payer_id'       => ( isset( $post_data['PAYERID'] ) ? $post_data['PAYERID'] : '' ),
                'payer_email'    => ( isset( $post_data['EMAIL'] ) ? $post_data['EMAIL'] : '' ),
                'payer_status'   => ( isset( $post_data['PAYERSTATUS'] ) ? $post_data['PAYERSTATUS'] : '' )
            );

        // post_data comes from an IPN response
        } else {

            $log_data = array(
                'payment_status' => ( isset( $post_data['initial_payment_status'] ) ? $post_data['initial_payment_status'] : ( isset( $post_data['payment_status'] ) ? $post_data['payment_status'] : '' ) ),
                'payment_date'   => ( isset( $post_data['payment_date'] ) ? $post_data['payment_date'] : ( isset( $post_data['time_created'] ) ? $post_data['time_created'] : '' ) ),
                'payer_id'       => ( isset( $post_data['payer_id'] ) ? $post_data['payer_id'] : '' ),
                'payer_email'    => ( isset( $post_data['payer_email'] ) ? $post_data['payer_email'] : '' ),
                'payer_status'   => ( isset( $post_data['payer_status'] ) ? $post_data['payer_status'] : '' )
            );

        }

        return $log_data;

    }


    /*
     * Make a call to the PayPal API to cancel a subscription
     *
     */
    public function process_cancel_subscription( $payment_profile_id = '' ) {

        // Get API credentials and check if they are complete
        $api_credentials = pms_get_paypal_api_credentials();

        if( !$api_credentials )
            return false;


        // Get payment_profile_id
        if( empty( $payment_profile_id ) )
            $payment_profile_id = pms_member_get_payment_profile_id( $this->user_id, $this->subscription_plan->id );


        //PayPal API arguments
        $request_fields = array(
            'USER'      => $api_credentials['username'],
            'PWD'       => $api_credentials['password'],
            'SIGNATURE' => $api_credentials['signature'],
            'VERSION'   => '76.0',
            'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
            'PROFILEID' => $payment_profile_id,
            'ACTION'    => 'Cancel'
        );

        $request = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $request_fields ) );

        // Cache the request
        $this->request_response_cancel_subscription = $request;

        if( !is_wp_error( $request ) && !empty( $request['body'] ) && !empty( $request['response']['code'] ) && $request['response']['code'] == 200 ) {

            parse_str( $request['body'], $body );

            if( strpos( strtolower( $body['ACK'] ), 'success' ) !== false )
                return true;
            else
                return false;

        } else {

            return false;

        }

    }


    /*
     * Return the error message for the last cancel subscription request call
     *
     * @return string
     *
     */
    public function get_cancel_subscription_error() {

        if( empty( $this->request_response_cancel_subscription ) )
            return '';

        $error = '';

        $request = $this->request_response_cancel_subscription;

        if( is_wp_error( $request ) ) {

            $error = $request->get_error_message();

        } else {

            parse_str( $request['body'], $body );

            if( !isset( $request['response'] ) || empty( $request['response'] ) )
                $error = __( 'No request response received.', 'paid-member-subscriptions' );
            else {

                if( isset( $request['response']['code'] ) && (int)$request['response']['code'] != 200 )
                    $error = $request['response']['code'] . ( isset( $request['response']['message'] ) ? ' : ' . $request['response']['message'] : '' );

            }

            if( isset( $body['L_LONGMESSAGE0'] ) )
                $error = $body['L_LONGMESSAGE0'];


        }

        return $error;

    }

}