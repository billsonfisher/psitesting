<?php
/**
 * Plugin Name: Paid Member Subscriptions - Multiple Subscriptions per User
 * Plugin URI: http://www.cozmoslabs.com/
 * Description: Setup multiple subscription level groups and allow members to sign up for more than one subscription plan (one per group).
 * Version: 1.1.0
 * Author: Cozmoslabs, Mihai Iova
 * Author URI: http://www.cozmoslabs.com/
 * Text Domain: paid-member-subscriptions
 * License: GPL2
 *
 * == Copyright ==
 * Copyright 2015 Cozmoslabs (www.cozmoslabs.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;


Class PMS_Multiple_Subscriptions_Per_User {

    public function __construct() {

        // Define global constants
        define( 'PMS_MSU_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'PMS_MSU_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

        // Include dependencies
        $this->include_dependencies();

    }


    /*
     * Function to include the files needed
     *
     */
    public function include_dependencies() {

        /*
         * Functions file
         */
        if( file_exists( PMS_MSU_PLUGIN_DIR_PATH . 'includes/functions.php' ) )
            include_once PMS_MSU_PLUGIN_DIR_PATH . 'includes/functions.php';

        /*
         * Form shortcodes
         */
        if( file_exists( PMS_MSU_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php' ) )
            include_once PMS_MSU_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php';

        /*
         * Form handler
         */
        if( file_exists( PMS_MSU_PLUGIN_DIR_PATH . 'includes/class-form-handler.php' ) )
            include_once PMS_MSU_PLUGIN_DIR_PATH . 'includes/class-form-handler.php';

    }


}

// Let's get the party started
new PMS_Multiple_Subscriptions_Per_User;

if( class_exists( 'pms_PluginUpdateChecker' ) ) {
    $slug = 'multiple-subscriptions-per-user';
    $localSerial = get_option( $slug . '_serial_number');
    $pms_nmf_update = new pms_PluginUpdateChecker('http://updatemetadata.cozmoslabs.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLPMSMSPU', __FILE__, $slug );
}