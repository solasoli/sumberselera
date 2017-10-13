<?php
/**
 * Plugin Name: Countdown Timer Ultimate
 * Plugin URI: https://www.wponlinesupport.com/
 * Description: Easy to add and display timer.
 * Author: WP Online Support
 * Text Domain: countdown-timer-ultimate
 * Domain Path: /languages/
 * Version: 1.1.3
 * Author URI: https://www.wponlinesupport.com/
 *
 * @package WordPress
 * @author WP Online Support
 */

/**
 * Basic plugin definitions
 * 
 * @package Countdown Timer Ultimate
 * @since 1.1.1
 */
if( !defined( 'WPCDT_VERSION' ) ) {
	define( 'WPCDT_VERSION', '1.1.3' ); // Version of plugin
}
if( !defined( 'WPCDT_DIR' ) ) {
    define( 'WPCDT_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WPCDT_URL' ) ) {
    define( 'WPCDT_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}
if( !defined( 'WPCDT_PLUGIN_BASENAME' ) ) {
	define( 'WPCDT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // plugin base name
}
if( !defined( 'WPCDT_POST_TYPE' ) ) {
    define( 'WPCDT_POST_TYPE', 'wpcdt_countdown' ); // Plugin post type
}
if( !defined( 'WPCDT_META_PREFIX' ) ) {
    define( 'WPCDT_META_PREFIX', '_wpcdt_' ); // Plugin metabox prefix
}

/**
 * Load Text Domain
 * This gets the plugin ready for translation
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_load_textdomain() {
	load_plugin_textdomain( 'countdown-timer-ultimate', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}
add_action('plugins_loaded', 'wpcdt_load_textdomain');

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'wpcdt_install' );


/**
 * Deactivation Hook
 * 
 * Register plugin deactivation hook.
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'wpcdt_uninstall');

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_install() {  
    
    wpcdt_register_post_type();

    // IMP need to flush rules for custom registered post type
    flush_rewrite_rules();

    if( is_plugin_active('countdown-timer-ultimate-pro/countdown-timer-ultimate-pro.php') ) {
        add_action('update_option_active_plugins', 'deactivate_countdown_pro_version');
    }
}

/**
 * Plugin Setup (On Deactivation)
 * 
 * Delete plugin options.
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_uninstall() {
    // Uninstall functionality
}

/**
 * Deactivate free plugin
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function deactivate_countdown_pro_version() {
    deactivate_plugins('countdown-timer-ultimate-pro/countdown-timer-ultimate-pro.php', true);
}

/**
 * Function to display admin notice of activated plugin.
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_countdown_admin_notice() {

    $dir = WP_PLUGIN_DIR . '/countdown-timer-ultimate-pro/countdown-timer-ultimate-pro.php';
    
    // If free plugin exist
    if( file_exists($dir) ) {
        
        global $pagenow;
        
        if ( $pagenow == 'plugins.php' && current_user_can( 'install_plugins' ) ) {
            echo '<div id="message" class="updated notice is-dismissible"><p><strong>Thank you for activating Countdown Timer Ultimate</strong>.<br /> It looks like you had Pro version <strong>(<em>Countdown Timer Ultimate Pro</em>)</strong> of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it. </p></div>';
        }
    }
}

// Action to display notice
add_action( 'admin_notices', 'wpcdt_countdown_admin_notice');

// Functions file
require_once( WPCDT_DIR . '/includes/wpcdt-functions.php' );

// Plugin Post Type File
require_once( WPCDT_DIR . '/includes/wpcdt-post-types.php' );

// Admin Class File
require_once( WPCDT_DIR . '/includes/admin/class-wpcdt-admin.php' );

// Script Class File
require_once( WPCDT_DIR . '/includes/class-wpcdt-script.php' );

// Shortcode File
require_once( WPCDT_DIR . '/includes/shortcode/wpcdt-shortcode.php' );

// How it work file, Load admin files
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( WPCDT_DIR . '/includes/admin/wpcdt-how-it-work.php' );
}