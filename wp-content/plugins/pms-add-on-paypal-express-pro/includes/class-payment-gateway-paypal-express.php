<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

Class PMS_Payment_Gateway_PayPal_Express extends PMS_Payment_Gateway_PayPal_Express_Legacy {

	/**
     * The features supported by the payment gateway
     *
     * @access public
     * @var array
     *
     */
    public $supports;

    /**
	 * PayPal's API credentials
	 *
	 * @access protected
	 * @var array
	 *
	 */
    protected $api_credentials;


    /**
     * The last request response from PayPal
     *
     * @access protected
     * @var array
     *
     */
    private $_last_request_response;


	/**
	 * Initialisation
	 *
	 */
	public function init() {

		parent::init();

		$settings = get_option( 'pms_settings', array() );

		$this->supports = array(
			'gateway_scheduled_payments',
			'recurring_payments'
		);

		// If reference transactions are enabled add support for all features
		if( ! empty( $settings['payments']['gateways']['paypal']['reference_transactions'] ) ) {

			// Remove the support for the gateway scheduled payments
			unset( $this->supports[ array_search( 'gateway_scheduled_payments', $this->supports ) ] );

			$this->supports = array_merge( $this->supports, array(
				'plugin_scheduled_payments',
				'subscription_free_trial',
				'subscription_sign_up_fee',
				'change_subscription_payment_method_admin'
			));

		}

		$this->api_credentials = pms_get_paypal_api_credentials();

		// Hook to catch information when the user returns from PayPal
		add_action( 'wp_loaded', array( $this, 'catch_express_checkout_return_billing_agreement' ) );
		add_action( 'wp_loaded', array( $this, 'catch_express_checkout_return_process_payment' ) );

	}


	/**
	 * Makes an express checkout to create a billing agreement with the user
	 *
	 * @param int $member_subscription_id
	 *
	 */
	public function register_automatic_billing_info( $member_subscription_id = 0 ) {

		if( ! empty( $this->payment_id ) )
			return;

		$this->set_express_checkout( $member_subscription_id );

	}


	/**
     * Processes a one-time payment 
     *
     * @param int $payment_id       - the payment that needs to be processed
     * @param int $subscription_id  - (optional) the subscription for which the payment is made
     *
     * @return bool
     *
     */
	public function process_payment( $payment_id = 0, $member_subscription_id = 0 ) {

		if( empty( $payment_id ) || empty( $member_subscription_id ) )
			return false;

		$payment = pms_get_payment( $payment_id );

		// Process the initial checkout payment
		if( $payment->type != 'subscription_renewal_payment' && $payment->type != 'subscription_recurring_payment' )
			return $this->process_payment_checkout_payment( $payment_id, $member_subscription_id );

		// Do the renewal reference transaction
		else
			return $this->process_payment_reference_transaction( $payment_id, $member_subscription_id );

	}


	/**
     * Initiates the express checkout payment. The actual payment is made when the user returns from
     * PayPal.
     *
     * @param int $payment_id       - the payment that needs to be processed
     * @param int $subscription_id  - (optional) the subscription for which the payment is made
     *
     * @return bool
     *
     */
	protected function process_payment_checkout_payment( $payment_id = 0, $member_subscription_id = 0 ) {

		if( empty( $payment_id ) || empty( $member_subscription_id ) )
			return false;

		$this->set_express_checkout( $member_subscription_id, $payment_id );

	}


	/**
     * Processes a one-time payment to renew the subscription
     *
     * @param int $payment_id       - the payment that needs to be processed
     * @param int $subscription_id  - (optional) the subscription for which the payment is made
     *
     * @return bool
     *
     */
	protected function process_payment_reference_transaction( $payment_id = 0, $member_subscription_id = 0 ) {

		if( empty( $payment_id ) || empty( $member_subscription_id ) )
			return false;

		if( ! function_exists( 'pms_get_member_subscription' ) )
			return false;

		// Get billing agreement id
		$billing_agreement_id = pms_get_member_subscription_meta( $member_subscription_id, '_paypal_billing_agreement_id', true );

		// Get payment
		$payment = pms_get_payment( $payment_id );

		$args = array(
			'AMT' 			=> $payment->amount,
			'ITEMAMT'		=> $payment->amount,
			'CURRENCYCODE'	=> $payment->currency,
			'CUSTOM'		=> json_encode( array( 'payment_id' => $payment_id, 'member_subscription_id' => $member_subscription_id ) )
		);

		// Process payment
		$response = $this->do_reference_transaction( $billing_agreement_id, $args );

		if( ! $response )
			return false;

		if( $response['PAYMENTSTATUS'] == 'Completed' ) {

			// Complete the payment and save the transaction id
			$payment->update( array( 'transaction_id' => $response['TRANSACTIONID'], 'status' => 'completed' ) );

			return true;

		} else {

			// Save the transaction id
			$payment->update( array( 'transaction_id' => $response['TRANSACTIONID'] ) );

			/**
			 * In case of payment failure maybe log data
			 *
			 * @param array $response
			 * @param int 	$payment_id
			 * @param int 	$member_subscription_id
			 *
			 */
			do_action( 'pms_paypal_express_process_payment_reference_transaction_failed', $response, $payment_id, $member_subscription_id );

			return false;

		}


	}


	/**
	 * Initiates an Express Checkout transaction
	 *
	 * @param int $member_subscription_id 	- (optional)
	 * @param int $payment_id 				- (optional) - if provided will prepare a payment to be made
	 *
	 */
	protected function set_express_checkout( $member_subscription_id = 0, $payment_id = 0 ) {

		// Basic args required for Express Checkout
		$request_args = array(
            'METHOD'                        => 'SetExpressCheckout',
            'USER'                          => $this->api_credentials['username'],
            'PWD'                           => $this->api_credentials['password'],
            'SIGNATURE'                     => $this->api_credentials['signature'],
            'VERSION'                       => 68,
            'EMAIL'                         => $this->user_email,
            'RETURNURL'                     => $this->redirect_url,
            'CANCELURL'                     => pms_get_current_page_url(),
            'LANDINGPAGE'					=> 'Login'
        );

		// Cache current time to send as a custom value
		// in order to retrieve the custom transient
		$time = time();

		// Payment args
        if( ! empty( $payment_id ) ) {

        	$payment = pms_get_payment( $payment_id );

        	$request_args = array_merge( $request_args, array(
        		'DESC'								=> $this->subscription_plan->name,
        		'PAYMENTREQUEST_0_AMT'           	=> $payment->amount,
	            'PAYMENTREQUEST_0_CURRENCYCODE'  	=> $this->currency,
	            'PAYMENTREQUEST_0_CUSTOM'        	=> $time,
	            'PAYMENTREQUEST_0_NOTIFYURL'     	=> home_url() . '/?pay_gate_listener=epipn',
	            'PAYMENTREQUEST_0_PAYMENTACTION' 	=> 'Sale',
	            'PAYMENTREQUEST_0_PAYMENTREQUESTID' => $this->payment_id,
	            'L_BILLINGTYPE0' 			     	=> 'MerchantInitiatedBillingSingleAgreement',
            	'L_BILLINGAGREEMENTDESCRIPTION0' 	=> $this->subscription_plan->name,
            	'L_PAYMENTREQUEST_0_AMT0'        	=> $payment->amount,
            	//'PAYMENTREQUEST_0_ITEMAMT'        	=> $payment->amount, //I think this should be here according to the documentation but for now I will leave it out
            	'L_PAYMENTREQUEST_0_NAME0'       	=> $this->subscription_plan->name,
            	'RETURNURL'                     	=> add_query_arg( array( 'pmstkn' => wp_create_nonce( 'pms_paypal_checkout_process_payment' ) ), $this->redirect_url ),
        	));

        } else {

        	$request_args = array_merge( $request_args, array(
        		'PAYMENTREQUEST_0_AMT' 				=> 0,
        		'PAYMENTREQUEST_0_CUSTOM'        	=> $time,
        		'PAYMENTREQUEST_0_NOTIFYURL'     	=> home_url() . '/?pay_gate_listener=epipn',
        		'L_BILLINGTYPE0' 			     	=> 'MerchantInitiatedBillingSingleAgreement',
            	'L_BILLINGAGREEMENTDESCRIPTION0' 	=> $this->subscription_plan->name,
            	'RETURNURL'                     	=> add_query_arg( array( 'pmstkn' => wp_create_nonce( 'pms_paypal_checkout_billing_agreement' ) ), $this->redirect_url ),
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
        	'member_subscription_id'   => $member_subscription_id,
        	'member_subscription_data' => $this->subscription_data,
            'payment_id'     		   => $payment_id,
            'form_location'			   => $this->form_location,
            'redirect_url'   		   => $this->redirect_url
        );

        set_transient( 'pms_set_express_checkout_custom_' . $time, $set_express_checkout_custom, 2 * DAY_IN_SECONDS );


        // Make the request
        $response = $this->_make_request( $request_args );
        
        // If the request response validates redirect the user to PayPal
        if( $this->_validate_request_response( $response ) ) {

        	$body = wp_remote_retrieve_body( $response );

        	parse_str( $body, $body );

        	$redirect = add_query_arg( array( 'token' => $body['TOKEN'] ), $this->paypal_express_checkout );

        	/**
        	 * Do something just before redirecting the user to PayPal to do the express checkout
        	 *
        	 */
        	do_action( 'pms_set_express_checkout_redirect', $redirect, $member_subscription_id, $payment_id );

            if( isset( $_POST['pmstkn'] ) ) {
                wp_redirect( $redirect );
                exit;
            }

        }

	}


	/**
	 * Returns an express checkout previously set, by providing the unique token
	 *
	 * @param string $token
	 *
	 * @return array
	 *
	 */
	protected function get_express_checkout( $token = '' ) {

		if( empty( $token ) )
			return array();

		$request_args = array(
			'METHOD' 	=> 'GetExpressCheckoutDetails',
			'USER'		=> $this->api_credentials['username'],
            'PWD'		=> $this->api_credentials['password'],
            'SIGNATURE'	=> $this->api_credentials['signature'],
            'VERSION'	=> 68,
			'TOKEN'	 	=> sanitize_text_field( $token )
		);

		$response = $this->_make_request( $request_args );

		if( $this->_validate_request_response( $response ) ) {

			$body = wp_remote_retrieve_body( $response );

			parse_str( $body, $body );

			return $body;

		} else
			return array();

	}


	/**
	 * Creates a billing agreement between the user and PayPal so that further payments can
	 * be made without user intervention
	 *
	 * @param string $token
	 *
	 * @return string
	 *
	 */
	protected function create_billing_agreement( $token = '' ) {

		if( empty( $token ) )
			return array();

		$request_args = array(
			'METHOD' 	=> 'CreateBillingAgreement',
			'USER'		=> $this->api_credentials['username'],
            'PWD'		=> $this->api_credentials['password'],
            'SIGNATURE'	=> $this->api_credentials['signature'],
            'VERSION'	=> 68,
			'TOKEN'	 	=> sanitize_text_field( $token )
		);

		$response = $this->_make_request( $request_args );

		if( $this->_validate_request_response( $response ) ) {

			$body = wp_remote_retrieve_body( $response );

			parse_str( $body, $body );

			return ( ! empty( $body['BILLINGAGREEMENTID'] ) ? $body['BILLINGAGREEMENTID'] : '' );

		} else
			return '';

	}


	/**
	 * Processes a one-time payment within PayPal based on a set express checkout
	 *
	 * @param string $token
	 *
	 * @return mixed - array if all good | bool false if payment failed
	 *
	 */
	protected function do_express_checkout_payment( $token = '' ) {

		if( empty( $token ) )
			return false;

		$checkout_details = $this->get_express_checkout( sanitize_text_field( $token ) );

		$request_args = array(
			'METHOD' 							=> 'DoExpressCheckoutPayment',
			'USER'								=> $this->api_credentials['username'],
            'PWD'								=> $this->api_credentials['password'],
            'SIGNATURE'							=> $this->api_credentials['signature'],
            'VERSION'							=> 68,
			'TOKEN'	 							=> sanitize_text_field( $token ),
			'PAYERID'							=> $checkout_details['PAYERID'],
			'PAYMENTREQUEST_0_AMT'           	=> $checkout_details['PAYMENTREQUEST_0_AMT'],
            'PAYMENTREQUEST_0_CURRENCYCODE'  	=> $checkout_details['PAYMENTREQUEST_0_CURRENCYCODE'],
            'PAYMENTREQUEST_0_CUSTOM'        	=> $checkout_details['PAYMENTREQUEST_0_CUSTOM'],
            'PAYMENTREQUEST_0_NOTIFYURL'     	=> $checkout_details['PAYMENTREQUEST_0_NOTIFYURL'],
            'PAYMENTREQUEST_0_PAYMENTREQUESTID' => $checkout_details['PAYMENTREQUEST_0_PAYMENTREQUESTID'],
		);

		$response = $this->_make_request( $request_args );

		if( $this->_validate_request_response( $response ) ) {

			$body = wp_remote_retrieve_body( $response );

			parse_str( $body, $body );

			return ( ! empty( $body ) ? $body : '' );

		} else
			return false;

	}


	/**
	 * Processes a one-time payment within PayPal based on a billing agreement
	 *
	 * @param string $billing_agreement_id
	 * @param array  $args
	 *
	 * @return mixed - array if all good | bool false if payment failed
	 *
	 */
	protected function do_reference_transaction( $billing_agreement_id = '', $args = array() ) {

		if( empty( $billing_agreement_id ) )
			return false;

		$request_args = array(
			'METHOD' 							=> 'DoReferenceTransaction',
			'USER'								=> $this->api_credentials['username'],
            'PWD'								=> $this->api_credentials['password'],
            'SIGNATURE'							=> $this->api_credentials['signature'],
            'VERSION'							=> 68,
			'REFERENCEID'	 					=> sanitize_text_field( $billing_agreement_id ),
			'PAYMENTACTION'						=> 'Sale'
		);

		$request_args = array_merge( $request_args, $args );

		$response = $this->_make_request( $request_args );

		if( $this->_validate_request_response( $response ) ) {

			$body = wp_remote_retrieve_body( $response );

			parse_str( $body, $body );

			return ( ! empty( $body ) ? $body : '' );

		} else
			return false;

	}


	/**
	 * Catches the return from PayPal and creates a billing agreement between the user and PayPal
	 *
	 */
	public function catch_express_checkout_return_billing_agreement() {

		if( empty( $_GET['token'] ) )
			return;

		if( empty( $_GET['pmstkn'] ) || ! wp_verify_nonce( $_GET['pmstkn'], 'pms_paypal_checkout_billing_agreement' ) )
			return;

		// Get checkout details
		$checkout_details = $this->get_express_checkout( $_GET['token'] );

		// If the user did not agree to the recurring billing within PayPal return
		if( empty( $checkout_details['BILLINGAGREEMENTACCEPTEDSTATUS'] ) )
			return;

		// Create the billing agreement
		$billing_agreement_id = $this->create_billing_agreement( $_GET['token'] );

		if( ! empty( $billing_agreement_id ) ) {

			// Get custom data saved in the transient on setting the express checkout
			$custom = $checkout_details['PAYMENTREQUEST_0_CUSTOM'];
			$custom = get_transient( 'pms_set_express_checkout_custom_' . $custom );

			$member_subscription_id   = ( ! empty( $custom['member_subscription_id'] ) ? $custom['member_subscription_id'] : 0 );
			$member_subscription_data = ( ! empty( $custom['member_subscription_data'] ) ? $custom['member_subscription_data'] : array() );
			$redirect_url 		      = ( ! empty( $custom['redirect_url'] ) ? $custom['redirect_url'] : '' );
			$form_location 		      = ( ! empty( $custom['form_location'] ) ? $custom['form_location'] : '' );

			/**
			 * Save subscription billing agreement
			 *
			 */
			if( ! empty( $member_subscription_id ) ) {

				pms_update_member_subscription_meta( $member_subscription_id, '_paypal_billing_agreement_id', $billing_agreement_id );

			}

			/**
			 * Update subscription data
			 *
			 */
			if( ! empty( $member_subscription_id ) && ! empty( $member_subscription_data ) ) {

				$this->update_member_subscription( $member_subscription_id, $member_subscription_data, $form_location );

			}

			/**
			 * Redirect when finished
			 *
			 */
			if( ! empty( $redirect_url ) ) {

				wp_redirect( $redirect_url );
				exit;

			}

		}

	}


	/**
	 * Catches the return from PayPal and processes the payment
	 *
	 * If the payment is successful within PayPal we complete the payment on our end, 
	 * activate the subscription, save the billing agreement for the subscription and 
	 * then redirect the user to the success page
	 *
	 */
	public function catch_express_checkout_return_process_payment() {

		if( empty( $_GET['token'] ) )
			return;

		if( empty( $_GET['pmstkn'] ) || ! wp_verify_nonce( $_GET['pmstkn'], 'pms_paypal_checkout_process_payment' ) )
			return;

		// Get checkout details
		$checkout_details = $this->get_express_checkout( $_GET['token'] );

		// Get custom data saved in the transient on setting the express checkout
		$custom = $checkout_details['PAYMENTREQUEST_0_CUSTOM'];
		$custom = get_transient( 'pms_set_express_checkout_custom_' . $custom );

		$payment_id 		      = ( ! empty( $custom['payment_id'] ) ? $custom['payment_id'] : 0 );
		$member_subscription_id   = ( ! empty( $custom['member_subscription_id'] ) ? $custom['member_subscription_id'] : 0 );
		$member_subscription_data = ( ! empty( $custom['member_subscription_data'] ) ? $custom['member_subscription_data'] : array() );
		$redirect_url 		      = ( ! empty( $custom['redirect_url'] ) ? $custom['redirect_url'] : '' );
		$form_location 		      = ( ! empty( $custom['form_location'] ) ? $custom['form_location'] : '' );

		// If the user did not agree to the recurring billing within PayPal return
		if( empty( $checkout_details['BILLINGAGREEMENTACCEPTEDSTATUS'] ) ) {

			$redirect_url = add_query_arg( array( 'pms_payment_error' => '1', 'pms_is_register' => ( in_array( $form_location, array( 'register', 'register_email_confirmation' ) ) ) ? '1' : '0' ), pms_get_current_page_url( true ) );
            wp_redirect( $redirect_url );
            exit;

		}
		
		// Process the payment
		$response = $this->do_express_checkout_payment( $_GET['token'] );

		// If the response isn't good return
		if( ! $response ) {

			$redirect_url = add_query_arg( array( 'pms_payment_error' => '1', 'pms_is_register' => ( in_array( $form_location, array( 'register', 'register_email_confirmation' ) ) ) ? '1' : '0' ), pms_get_current_page_url( true ) );
            wp_redirect( $redirect_url );
            exit;

		}


		// Complete the payment and activate the subscription if everything is okay
		if( $response['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed' ) {

			$billing_agreement_id     = ( ! empty( $response['BILLINGAGREEMENTID'] ) ? $response['BILLINGAGREEMENTID'] : '' );
			$transaction_id		      = ( ! empty( $response['PAYMENTINFO_0_TRANSACTIONID'] ) ? $response['PAYMENTINFO_0_TRANSACTIONID'] : '' );

			/**
			 * Update the payment
			 *
			 */
			if( ! empty( $payment_id ) ) {

				$payment = pms_get_payment( $payment_id );
				$payment->update( array( 'status' => 'completed', 'transaction_id' => $transaction_id ) );

			}
			
			/**
			 * Update subscription data
			 *
			 */
			if( ! empty( $member_subscription_id ) && ! empty( $member_subscription_data ) ) {

				$this->update_member_subscription( $member_subscription_id, $member_subscription_data, $form_location );

			}

			/**
			 * Save subscription billing agreement
			 *
			 */
			if( ! empty( $member_subscription_id ) ) {

				pms_update_member_subscription_meta( $member_subscription_id, '_paypal_billing_agreement_id', $billing_agreement_id );

			}

			/**
			 * Redirect when finished
			 *
			 */
			if( ! empty( $redirect_url ) ) {

				wp_redirect( $redirect_url );
				exit;

			}

		} else {

			/**
			 * In case of payment failure maybe log data
			 *
			 * @param array $response
			 * @param int 	$payment_id
			 * @param int 	$member_subscription_id
			 *
			 */
			do_action( 'pms_paypal_express_process_payment_express_checkout_payment_failed', $response, $payment_id, $member_subscription_id );

			// Redirect to error
			$redirect_url = add_query_arg( array( 'pms_payment_error' => '1', 'pms_is_register' => ( in_array( $form_location, array( 'register', 'register_email_confirmation' ) ) ) ? '1' : '0' ), pms_get_current_page_url( true ) );
            wp_redirect( $redirect_url );
            exit;

		}

	}


	/**
	 * Updates a subscription after all the processes have been completed
	 *
	 * @param int 	 $member_subscription_id
	 * @param array  $member_subscription_data
	 * @param string $form_location
	 *
	 */
	private function update_member_subscription( $member_subscription_id = 0, $member_subscription_data = array(), $form_location = '' ) {

		if( ! function_exists( 'pms_get_member_subscription' ) )
			return;

		if( empty( $member_subscription_id ) )
			return;

		if( empty( $member_subscription_data ) )
			return;

		if( empty( $form_location ) )
			return;

		$subscription = pms_get_member_subscription( $member_subscription_id );

		$subscription_data['status'] = 'active';

        // Handle each subscription by the form location
        switch( $form_location ) {

            case 'register':
            // new subscription
            case 'new_subscription':
            // register form E-mail Confirmation compatibility
            case 'register_email_confirmation':
            // upgrading the subscription
            case 'upgrade_subscription':

            case 'retry_payment':

                $subscription->update( $subscription_data );
                break;

            case 'renew_subscription':

                if( strtotime( $subscription->expiration_date ) < time() || $subscription_plan->duration === 0 )
                    $expiration_date = $subscription_plan->get_expiration_date();
                else {
                    $expiration_date = date( 'Y-m-d 23:59:59', strtotime( $subscription->expiration_date . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) );
                }

                if( $is_recurring ) {
                    $subscription_data['billing_next_payment'] = $expiration_date;
                    $subscription_data['expiration_date']      = '';
                } else {
                    $subscription_data['expiration_date']      = $expiration_date;
                }

                $subscription->update( $subscription_data );

                break;

            default:
                break;

        }

	}


	/**
	 * Posts to the API endpoint with the given args and returns the results
	 *
	 * @param array $args
	 *
	 * @return array
	 *
	 */
	protected function _make_request( $args = array() ) {

		// Make the call
		$response = wp_remote_post( $this->api_endpoint, array( 'timeout' => 30, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $args ) );

		// Cache the response
		$this->_last_request_response = $response;

		// If the response doesn't validate add a hook to log errors
		if( ! $this->_validate_request_response( $response ) ) {

			/**
			 * Actions to log errors if the request response is invalid
			 *
			 * @param string $error_message
			 * @param array  $response
			 *
			 */
			do_action( 'pms_paypal_express_make_request_response_invalid', $this->_get_request_error_message( $response ), $response );

		}

		return $response;

	}


	/**
	 * Validates a given request response
	 *
	 * @param mixed $response
	 *
	 * @return bool
	 *
	 */
	protected function _validate_request_response( $response = array() ) {

		if( is_wp_error( $response ) )
			return false;

		if( 200 != wp_remote_retrieve_response_code( $response ) )
			return false;

		$body = wp_remote_retrieve_body( $response );

		parse_str( $body, $body );

		if( empty( $body ) )
			return false;

		if( empty( $body['ACK'] ) )
			return false;

		if( $body['ACK'] != 'Success' && $body['ACK'] != 'SuccessWithWarning' )
			return false;

		return true;

	}


	/**
	 * Returns the error message if any for a given request response
	 *
	 * @param mixed $response
	 *
	 * @return string
	 *
	 */
	protected function _get_request_error_message( $response ) {

		$message = '';

		// If is wp_error return the error
		if( is_wp_error( $response ) ) {

			$message = $response->get_error_message();

		// If is a valid response, but PayPal ACK failed
		} else {

			$body = wp_remote_retrieve_body( $response );
		
			parse_str( $body, $body );

			if( ! empty( $body['L_ERRORCODE0'] ) )
				$message .= $body['L_ERRORCODE0'] . ': ';

			if( ! empty( $body['L_LONGMESSAGE0'] ) )
				$message .= $body['L_LONGMESSAGE0'];

		}

		return $message;

	}

}