<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;


if ( class_exists('PMS_Custom_Post_Type') ) {

    class PMS_Custom_Post_Type_Discount_Codes extends PMS_Custom_Post_Type
    {
        /*
         * Method to add the needed hooks
         *
         */
        public function init()
        {
            add_action( 'init', array( $this, 'process_data' ) );
            add_action( 'init', array( $this, 'register_custom_discount_code_statuses' ) );

            add_filter( 'manage_' . $this->post_type . '_posts_columns', array(__CLASS__, 'manage_posts_columns'));
            add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 10, 2 );

            add_filter('page_row_actions', array($this, 'remove_post_row_actions'), 10, 2);
            add_action('page_row_actions', array($this, 'add_post_row_actions'), 11, 2);

            // Remove "Move to Trash" bulk action
            add_filter('bulk_actions-edit-' . $this->post_type, array($this, 'remove_bulk_actions'));

            // Add a delete button where the move to trash was
            add_action('post_submitbox_start', array($this, 'submitbox_add_delete_button'));

            // Change the default "Enter title here" text
            add_filter('enter_title_here', array($this, 'change_discount_title_prompt_text'));

            // Set custom updated messages
            add_filter('post_updated_messages', array($this, 'set_custom_messages'));

            // Set custom bulk updated messages
            add_filter('bulk_post_updated_messages', array($this, 'set_bulk_custom_messages'), 10, 2);

        }

        /*
        * Method that validates data for the discount code cpt
        *
        */
        public function process_data() {

            // Verify nonce before anything
            if( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'pms_discount_code_nonce' ) )
                return;

            // Activate discount code
            if( isset( $_REQUEST['pms-action'] ) && $_REQUEST['pms-action'] == 'activate_discount_code' && isset( $_REQUEST['post_id'] ) ) {
                PMS_Discount_Code::activate( (int)esc_attr( $_REQUEST['post_id'] ) );
            }

            // Deactivate discount code
            if( isset( $_REQUEST['pms-action'] ) && $_REQUEST['pms-action'] == 'deactivate_discount_code' && isset( $_REQUEST['post_id'] ) ) {
                PMS_Discount_Code::deactivate( (int)esc_attr( $_REQUEST['post_id'] ) );
            }

            // Delete discount code
            if( isset( $_REQUEST['pms-action'] ) && $_REQUEST['pms-action'] == 'delete_discount_code' && isset( $_REQUEST['post_id'] ) ) {
                PMS_Discount_Code::remove( (int)esc_attr( $_REQUEST['post_id'] ) );
            }

        }

        /**
         * Method for registering custom discount code statuses (active, inactive)
         *
         */
        public function register_custom_discount_code_statuses() {

            // Register custom Discount Code Statuses
            register_post_status( 'active', array(
                'label'                     => _x( 'Active', 'Active status for discount code', 'pms-add-on-discount-codes' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'pms-add-on-discount-codes' )
            )  );
            register_post_status( 'inactive', array(
                'label'                     => _x( 'Inactive', 'Inactive status for discount code', 'pms-add-on-discount-codes' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'pms-add-on-discount-codes' )
            )  );
            register_post_status( 'expired', array(
                'label'                     => _x( 'Expired', 'Expired status for discount code', 'pms-add-on-discount-codes' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'pms-add-on-discount-codes' )
            )  );

        }

        /*
         * Method to add the needed columns in Discount Codes listing.
         *
         */
        public static function manage_posts_columns($columns) {

            // Add new columns for the discount codes
            $new_columns = array_merge($columns, array(
                'code'            => __('Code', 'pms-add-on-discount-codes'),
                'amount'          => __('Amount', 'pms-add-on-discount-codes'),
                'uses'            => __('Uses', 'pms-add-on-discount-codes'),
                'start-date'      => __('Start Date', 'pms-add-on-discount-codes'),
                'expiration-date' => __('Expiration Date', 'pms-add-on-discount-codes'),
                'status'          => __('Status', 'pms-add-on-discount-codes')
            ));

            unset($new_columns['date']);

            return $new_columns;
        }

        /*
         * Method for removing the unnecessary row actions (e.g Quick edit, Trash).
         *
         */
        public function remove_post_row_actions($actions, $post) {

            if ($post->post_type != $this->post_type)
                return $actions;

            if (empty($actions))
                return $actions;

            foreach ($actions as $key => $action) {
                if ($key != 'edit') {
                    unset($actions[$key]);
                }
            }

            return $actions;
        }

        /*
         * Method for adding new row actions (e.g Activate/Deactivate , Delete).
         *
         */
        public function add_post_row_actions($actions, $post)
        {

            if ($post->post_type != $this->post_type)
                return $actions;

            if (empty($actions))
                return $actions;


            /*
            * Add the option to activate and deactivate a discount code
            */
            $discount_code = new PMS_Discount_Code( $post );

            if( $discount_code->is_active() )
                $activate_deactivate = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'deactivate_discount_code', 'post_id' => $post->ID ) ), 'pms_discount_code_nonce' ) ) . '">' . __( 'Deactivate', 'pms-add-on-discount-codes' ) . '</a>';
            else
                $activate_deactivate = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'activate_discount_code', 'post_id' => $post->ID ) ), 'pms_discount_code_nonce' ) ) . '">' . __( 'Activate', 'pms-add-on-discount-codes' ) . '</a>';

            $actions['change_status'] = $activate_deactivate;

            /*
             * Add the option to delete a discount code
             */
            $delete = '<span class="trash"><a onclick="return confirm( \'' . __("Are you sure you want to delete this Discount Code?", "pms-add-on-discount-codes") . ' \' )" href="' . esc_url(wp_nonce_url(add_query_arg(array('pms-action' => 'delete_discount_code', 'post_id' => $post->ID, 'deleted' => 1)), 'pms_discount_code_nonce')) . '">' . __('Delete', 'pms-add-on-discount-codes') . '</a></span>';

            $actions['delete'] = $delete;


            // Return actions
            return $actions;

        }

        /*
         * Method to display values for each Discount Code column
         *
        */
        public static function manage_posts_custom_column( $column, $post_id ) {

            $discount_code = new PMS_Discount_Code( $post_id );

            // Information shown in discount "Code" column
            if ($column == 'code')
                echo '<input type="text" readonly class="pms-discount-code input" value="'.$discount_code->code .'">';

            // Information shown in discount "Amount" column
            if ($column == 'amount') {

                $currency_symbol = '';
                if ( get_option('pms_settings') ) {

                    $settings = get_option('pms_settings');
                    if ( ( function_exists('pms_get_currency_symbol') ) && isset( $settings['payments']['currency'] ) )
                        $currency_symbol = pms_get_currency_symbol( $settings['payments']['currency'] );
                }

                if ( $discount_code->type == 'percent' )
                    echo $discount_code->amount . '%';
                else
                    echo $currency_symbol . $discount_code->amount;
            }

            // Information shown in discount "Uses" column
            if ($column == 'uses')
                echo $discount_code->uses . '/' . ( ! empty( $discount_code->max_uses ) ? $discount_code->max_uses : '&infin;' );

            // Information shown in discount "Start date" column
            if ($column == 'start-date') {
                if ( !empty($discount_code->start_date) ) echo $discount_code->start_date;
                    else echo __( 'No start date', 'pms-add-on-discount-codes' );
            }

            // Information shown in discount "Start date" column
            if ($column == 'expiration-date') {
                if ( !empty($discount_code->expiration_date) ) echo $discount_code->expiration_date;
                else echo __( 'No expiration date', 'pms-add-on-discount-codes' );
            }

            // Information shown in the status column
            if( $column == 'status' ) {

                $discount_code_status_dot = apply_filters( 'pms-list-table-show-status-dot', '<span class="pms-status-dot ' . $discount_code->status . '"></span>' );

                if( $discount_code->is_active() )
                    echo $discount_code_status_dot . '<span>' . __( 'Active', 'pms-add-on-discount-codes' ) . '</span>';
                elseif ( $discount_code->is_expired() )
                    echo $discount_code_status_dot . '<span>' . __( 'Expired', 'pms-add-on-discount-codes' ) . '</span>';
                    else
                        echo $discount_code_status_dot . '<span>' . __( 'Inactive', 'pms-add-on-discount-codes' ) . '</span>';
            }

        }


        /*
        * Remove "Move to Trash" bulk action
        *
        */
        public function remove_bulk_actions($actions)
        {

            unset($actions['trash']);
            return $actions;

        }

        /*
        * Add a delete button where the move to trash was
        *
        */
        public function submitbox_add_delete_button()
        {
            global $post_type;
            global $post;

            if ($post_type != $this->post_type)
                return false;

            echo '<div id="pms-delete-action">';
            echo '<a class="submitdelete deletion" onclick="return confirm( \'' . __("Are you sure you want to delete this Discount Code?", "pms-add-on-discount-codes") . ' \' )" href="' . esc_url(wp_nonce_url(add_query_arg(array('pms-action' => 'delete_discount_code', 'post_id' => $post->ID, 'deleted' => 1), admin_url('edit.php?post_type=' . $this->post_type)), 'pms_discount_code_nonce')) . '">' . __('Delete Discount', 'pms-add-on-discount-codes') . '</a>';
            echo '</div>';

        }

        /*
        * Method to change the default title text "Enter title here"
        *
        */
        public function change_discount_title_prompt_text($input)
        {
            global $post_type;

            if ($post_type == $this->post_type) {
                return __('Enter Discount Code name here', 'pms-add-on-discount-codes');
            }

            return $input;
        }

        /*
        * Method that set custom updated messages
        *
        */
        function set_custom_messages($messages)
        {

            global $post;

            $messages['pms-discount-codes'] = array(
                0 => '',
                1 => __('Discount Code updated.', 'pms-add-on-discount-codes'),
                2 => __('Custom field updated.', 'pms-add-on-discount-codes'),
                3 => __('Custom field deleted.', 'pms-add-on-discount-codes'),
                4 => __('Discount Code updated.', 'pms-add-on-discount-codes'),
                5 => isset($_GET['revision']) ? sprintf(__('Discount Code' . ' restored to revision from %s', 'pms-add-on-discount-codes'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => __('Discount Code saved.', 'pms-add-on-discount-codes'),
                7 => __('Discount Code saved.', 'pms-add-on-discount-codes'),
                8 => __('Discount Code submitted.', 'pms-add-on-discount-codes'),
                9 => sprintf(__('Discount Code' . ' scheduled for: <strong>%1$s</strong>.', 'pms-add-on-discount-codes'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))),
                10 => __('Discount Code draft updated.', 'pms-add-on-discount-codes'),
            );

            // If there are validation errors do not display the above messages
            $error = get_transient('pms_dc_metabox_validation_errors');
            if  ( !empty($error) ) // no validation errors
                return array();
            else
                return $messages;

        }

        /*
        * Method that set custom bulk updated messages
        *
        */
        public function set_bulk_custom_messages($bulk_messages, $bulk_counts)
        {

            $bulk_messages['pms-discount-codes'] = array(
                'updated'   => _n('%s Discount Code updated.', '%s Discount Codes updated.', $bulk_counts['updated'], 'pms-add-on-discount-codes'),
                'locked'    => _n('%s Discount Code not updated, somebody is editing it.', '%s Discount Codes not updated, somebody is editing them.', $bulk_counts['locked'], 'pms-add-on-discount-codes'),
                'deleted'   => _n('%s Discount Code permanently deleted.', '%s Discount Codes permanently deleted.', $bulk_counts['deleted'], 'pms-add-on-discount-codes'),
                'trashed'   => _n('%s Discount Code moved to the Trash.', '%s Discount Codes moved to the Trash.', $bulk_counts['trashed'], 'pms-add-on-discount-codes'),
                'untrashed' => _n('%s Discount Code restored from the Trash.', '%s Discount Codes restored from the Trash.', $bulk_counts['untrashed'], 'pms-add-on-discount-codes'),
            );

            return $bulk_messages;

        }

    }

    /*
     * Initialize the Discount Codes custom post type
     *
     */

    $args = array(
        'show_ui'         => true,
        'show_in_menu'    => 'paid-member-subscriptions',
        'query_var'       => true,
        'capability_type' => 'post',
        'menu_position'   => null,
        'supports'        => array('title'),
        'hierarchical'    => true
    );

    $pms_cpt_discount_codes = new PMS_Custom_Post_Type_Discount_Codes('pms-discount-codes', __('Discount Code', 'pms-add-on-discount-codes'), __('Discount Codes', 'pms-add-on-discount-codes'), $args);
    $pms_cpt_discount_codes->init();
}
