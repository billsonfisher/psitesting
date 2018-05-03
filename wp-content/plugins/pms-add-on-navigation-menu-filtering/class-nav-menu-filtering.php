<?php
if( !function_exists( 'pms_get_member' ) )
    return;

class PMS_Nav_Menu_Filtering{

    function __construct(){
        // switch the admin walker
        add_filter('wp_edit_nav_menu_walker', array( $this, 'change_nav_menu_walker' ) );
        // add extra fields in menu items on the hook we define in the walker class
        add_action('wp_nav_menu_item_custom_fields', array( $this, 'extra_fields' ), 10, 4);
        // save the extra fields
        add_action('wp_update_nav_menu_item', array( $this, 'update_menu' ), 10, 2);
        // exclude items from frontend
        if ( ! is_admin() ){
            add_filter( 'wp_get_nav_menu_items', array( $this, 'hide_menu_elements' ) );
        }
        // enqueue needed resources
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /*
     * Change the default walker class for the menu
     *
     * @param $walker the filtered walker class
     * @return string new walker
     *
     */
    function change_nav_menu_walker( $walker ){
        $walker = 'PMS_Walker_Nav_Menu';
        return $walker;
    }

    /*
     * @param $hook the current admin page
     *
     */
    function enqueue_scripts( $hook ){

        if ( 'nav-menus.php' != $hook ) {
            return;
        }

        wp_enqueue_script( 'pms_nav_menu_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin/pms-nav-menu-filtering.js' );
        wp_enqueue_style( 'pms_nav_menu_script', plugin_dir_url( __FILE__ ) . 'assets/css/pms-nav-menu-filtering.css' );
    }

    /*
     * Function that ads extra fields on the hook we added in the walker class
     *
     */
    function extra_fields( $item_id, $item, $depth, $args ) {
        $lilo = get_post_meta( $item->ID, '_pms_menu_lilo', true );
        $saved_subscription_plans = explode( ',', get_post_meta( $item->ID, '_pms_content_restrict_subscription_plan', true ) );
        ?>

        <input type="hidden" name="pms-menu-filtering" value="<?php echo wp_create_nonce('pms-menu-filtering'); ?>"/>

        <div class="pms-options">
            <p class="description"><?php _e("Display To", 'pms-nav-menu-filtering-add-on'); ?></p>

            <label class="pms-menu-item-radio-label" for="pms-menu-li-<?php echo esc_attr( $item->ID ); ?>">
                <input type="radio" name="pms-menu-lilo-<?php echo esc_attr( $item->ID ); ?>" class="pms-lilo" id="pms-menu-li-<?php echo esc_attr( $item->ID ); ?>" <?php checked( 'loggedin', $lilo ); ?> value="loggedin"/>
                <?php _e('Logged In Users', 'pms-nav-menu-filtering-add-on'); ?>
            </label>

            <label class="pms-menu-item-radio-label" for="pms-menu-lo-<?php echo esc_attr( $item->ID ); ?>">
                <input type="radio" name="pms-menu-lilo-<?php echo esc_attr( $item->ID ); ?>" class="pms-lilo" id="pms-menu-lo-<?php echo esc_attr( $item->ID ); ?>" <?php checked( 'loggedout', $lilo ); ?> value="loggedout"/>
                <?php _e('Logged Out Users', 'pms-nav-menu-filtering-add-on'); ?>
            </label>


            <label class="pms-menu-item-radio-label" for="pms-menu-lilo-<?php echo esc_attr( $item->ID ); ?>">
                <input type="radio" name="pms-menu-lilo-<?php echo esc_attr( $item->ID ); ?>" class="pms-lilo" id="pms-menu-lilo-<?php echo esc_attr( $item->ID ); ?>" <?php checked( '', $lilo ); ?> value=""/>
                <?php _e('Everyone', 'pms-nav-menu-filtering-add-on'); ?>
            </label>

        </div>

        <?php
        $subscription_plans = pms_get_subscription_plans();
        if( !empty( $subscription_plans ) ){
        ?>
        <div class="pms-subscription-plans">
            <p class="description"><?php _e("Limit logged in users to Subscriptions", 'pms-nav-menu-filtering-add-on'); ?></p>

            <?php foreach( $subscription_plans as $subscription_plan ){?>
                <label for="pms-content-restrict-subscription-plan-<?php echo esc_attr( $subscription_plan->id ); ?>-<?php echo esc_attr( $item->ID ); ?>" class="pms-menu-item-checkbox-label">
                    <input type="checkbox" <?php if( $lilo != 'loggedin' ) echo 'disabled'; ?> value="<?php echo esc_attr( $subscription_plan->id ); ?>" <?php if( in_array( $subscription_plan->id, $saved_subscription_plans ) ) echo 'checked="checked"'; ?> name="pms-content-restrict-subscription-plan_<?php echo esc_attr( $item->ID ); ?>[]" id="pms-content-restrict-subscription-plan-<?php echo esc_attr( $subscription_plan->id ); ?>-<?php echo esc_attr( $item->ID ); ?>">
                    <?php echo $subscription_plan->name; ?>
                </label>
            <?php } ?>

        </div>
        <?php } ?>
    <?php
    }

    /**
     * Save the values on the menu
     */
    function update_menu($menu_id, $menu_item_db_id){

        // verify this came from our screen and with proper authorization.
        if (!isset($_POST['pms-menu-filtering']) || !wp_verify_nonce($_POST['pms-menu-filtering'], 'pms-menu-filtering'))
            return;

        if( !empty( $_POST['pms-menu-lilo-'.$menu_item_db_id] ) )
            update_post_meta( $menu_item_db_id, '_pms_menu_lilo', sanitize_text_field( $_POST['pms-menu-lilo-'.$menu_item_db_id] ) );
        else
            delete_post_meta( $menu_item_db_id, '_pms_menu_lilo' );
        

        if( !empty( $_REQUEST['pms-content-restrict-subscription-plan_'.$menu_item_db_id] ) && is_array( $_REQUEST['pms-content-restrict-subscription-plan_'.$menu_item_db_id] ) ) {

            $subscription_plan_ids = array_map( 'absint', $_REQUEST['pms-content-restrict-subscription-plan_'.$menu_item_db_id] );

            update_post_meta( $menu_item_db_id, '_pms_content_restrict_subscription_plan', implode( ',', $subscription_plan_ids ) );

        } else
            delete_post_meta( $menu_item_db_id, '_pms_content_restrict_subscription_plan' );
    }

    /**
     * Function that hides the elements on the frontend
     * @param $items the filtered item objects in the menu
     */
    function hide_menu_elements( $items ){
        $hide_children_of = array();

        // Iterate over the items to search and destroy
        foreach ( $items as $key => $item ) {

            $visible = true;

            // hide any item that is the child of a hidden item
            if( in_array( $item->menu_item_parent, $hide_children_of ) ){
                $visible = false;
                $hide_children_of[] = $item->ID; // for nested menus
            }

            // check any item that has NMR roles set
            if( $visible ){

                $lilo_option        = get_post_meta( $item->ID, '_pms_menu_lilo', true );
                $subscription_plans = get_post_meta( $item->ID, '_pms_content_restrict_subscription_plan', true );

                // check all logged in, all logged out, or role
                if( empty( $lilo_option ) || $lilo_option == '' ){
                    $visible = true;
                }
                elseif( $lilo_option == 'loggedout' ){
                    if( is_user_logged_in() )
                        $visible = false;
                }
                elseif( $lilo_option == 'loggedin' ) {
                    if( !is_user_logged_in() ){
                        $visible = false;
                    }
                    else{
                        if( !empty( $subscription_plans ) ){
                            $subscription_plans = explode( ',', $subscription_plans );
                            $member = pms_get_member( get_current_user_id() );
                            $member_subscriptions = array();
                            if( !empty( $member->subscriptions ) ){
                                foreach( $member->subscriptions as $subscription ){
                                    if( !empty( $subscription['status'] ) && $subscription['status'] == 'active' ) {
                                        $member_subscriptions[] = $subscription['subscription_plan_id'];
                                    }
                                }

                                $common_subscriptions = array_intersect( $subscription_plans, $member_subscriptions );
                                if( empty( $common_subscriptions ) ){
                                    $visible = false;
                                }
                            }
                            else{
                                $visible = false;
                            }
                        }

                    }
                }

            }

            // add filter to work with plugins that don't use traditional roles
            $visible = apply_filters( 'nav_menu_roles_item_visibility', $visible, $item );

            // unset non-visible item
            if ( ! $visible ) {
                $hide_children_of[] = $item->ID; // store ID of item
                unset( $items[$key] ) ;
            }

        }

        return $items;
    }

}

new PMS_Nav_Menu_Filtering();