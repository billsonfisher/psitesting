<?php
/**
 * Plugin Name: Paid Member Subscriptions - Navigation Menu Filtering
 * Plugin URI: http://www.cozmoslabs.com/
 * Description: Dynamically display menu items based on logged-in status as well as selected subscription plans.
 * Version: 1.0.4
 * Author: Cozmoslabs, Madalin Ungureanu
 * Author URI: http://www.cozmoslabs.com
 * License: GPL2
 */
/*  Copyright 2015 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    This add-on plugin is based on the "Nav Menu Roles" plugin: https://wordpress.org/plugins/nav-menu-roles/

*/
/*
* Define plugin path
*/
define( 'PMS_NMF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PMS_NMF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


if( file_exists( PMS_NMF_PLUGIN_DIR . 'class-pms_walker_nav_menu.php' ) )
    include_once PMS_NMF_PLUGIN_DIR . 'class-pms_walker_nav_menu.php';

if( file_exists( PMS_NMF_PLUGIN_DIR . 'class-nav-menu-filtering.php' ) )
    include_once PMS_NMF_PLUGIN_DIR . 'class-nav-menu-filtering.php';

function pms_nmf_init_translation()
{
    $current_theme = wp_get_theme();
    if( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root().'/'. $current_theme->stylesheet .'/local-pms-lang' ) )
        load_plugin_textdomain( 'pms-nav-menu-filtering-add-on', false, basename( dirname( __FILE__ ) ).'/../../themes/'.$current_theme->stylesheet.'/local-pms-lang' );
    else
        load_plugin_textdomain( 'pms-nav-menu-filtering-add-on', false, basename(dirname(__FILE__)) . '/translation/' );
}

add_action('init', 'pms_nmf_init_translation', 8);

if( class_exists( 'pms_PluginUpdateChecker' ) ) {
    $slug = 'navigation-menu-filtering';
    $localSerial = get_option( $slug . '_serial_number');
    $pms_nmf_update = new pms_PluginUpdateChecker('http://updatemetadata.cozmoslabs.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLPMSNMF', __FILE__, $slug );
}