<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wpcdt_Admin {
	
	function __construct() {
		
		// Action to add metabox
		add_action( 'add_meta_boxes', array($this, 'wpcdt_post_sett_metabox') );

		// Action to save metabox
		add_action( 'save_post', array($this, 'wpcdt_save_metabox_value') );

		// Action to add custom column to Timer listing
		add_filter( 'manage_'.WPCDT_POST_TYPE.'_posts_columns', array($this, 'wpcdt_posts_columns') );

		// Action to add custom column data to Timer listing
		add_action('manage_'.WPCDT_POST_TYPE.'_posts_custom_column', array($this, 'wpcdt_post_columns_data'), 10, 2);
	}

	/**
	 * Post Settings Metabox
	 * 
	 * @package Countdown Timer Ultimate
	 * @since 1.0.0
	 */
	function wpcdt_post_sett_metabox() {
		add_meta_box( 'wpcdt-post-sett', __( 'WP Countdown Timer Settings - Settings', 'countdown-timer-ultimate' ), array($this, 'wpcdt_post_sett_mb_content'), WPCDT_POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Post Settings Metabox HTML
	 * 
	 * @package Countdown Timer Ultimate
	 * @since 1.0.0
	 */
	function wpcdt_post_sett_mb_content() {
		include_once( WPCDT_DIR .'/includes/admin/metabox/wpcdt-sett-metabox.php');
	}

	/**
	 * Function to save metabox values
	 * 
	 * @package Countdown Timer Ultimate
	 * @since 1.0.0
	 */
	function wpcdt_save_metabox_value( $post_id ) {

		global $post_type;
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )                	// Check Autosave
		|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )  	// Check Revision
		|| ( $post_type !=  WPCDT_POST_TYPE ) )              					// Check if current post type is supported.
		{
		  return $post_id;
		}
		
		$prefix = WPCDT_META_PREFIX; // Taking metabox prefix
		
		// Taking variables
		$date 					= isset($_POST[$prefix.'timer_date']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_date']) : '';
		$animation 				= isset($_POST[$prefix.'timercircle_animation']) ? wpcdt_slashes_deep($_POST[$prefix.'timercircle_animation']) : '';
		$circlewidth			= isset($_POST[$prefix.'timercircle_width']) ? wpcdt_slashes_deep($_POST[$prefix.'timercircle_width']) : '';
		$backgroundwidth		= isset($_POST[$prefix.'timerbackground_width']) ? wpcdt_slashes_deep($_POST[$prefix.'timerbackground_width']) : '';
		$backgroundcolor		= isset($_POST[$prefix.'timerbackground_color']) ? wpcdt_slashes_deep($_POST[$prefix.'timerbackground_color']) : '';
		$timer_width 			= isset($_POST[$prefix.'timer_width']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_width']) : '';
		$is_days				= !empty($_POST[$prefix.'is_timerdays']) ? 1 : 0;
		$days_text 				= isset($_POST[$prefix.'timer_day_text']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_day_text']) : 'Days';
		$daysbackgroundcolor	= isset($_POST[$prefix.'timerdaysbackground_color']) ? wpcdt_slashes_deep($_POST[$prefix.'timerdaysbackground_color']) : '';
		$is_hours				= !empty($_POST[$prefix.'is_timerhours']) ? 1 : 0;
		$hours_text 			= isset($_POST[$prefix.'timer_hour_text']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_hour_text']) : 'Hours';
		$hoursbackgroundcolor	= isset($_POST[$prefix.'timerhoursbackground_color']) ? wpcdt_slashes_deep($_POST[$prefix.'timerhoursbackground_color']) : '';
		$is_minutes				= !empty($_POST[$prefix.'is_timerminutes']) ? 1 : 0;
		$minutes_text 			= isset($_POST[$prefix.'timer_minute_text']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_minute_text']) : 'Minutes';
		$minutesbackgroundcolor	= isset($_POST[$prefix.'timerminutesbackground_color']) ? wpcdt_slashes_deep($_POST[$prefix.'timerminutesbackground_color']) : '';
		$is_seconds				= !empty($_POST[$prefix.'is_timerseconds']) ? 1 : 0;
		$seconds_text 			= isset($_POST[$prefix.'timer_second_text']) ? wpcdt_slashes_deep($_POST[$prefix.'timer_second_text']) : 'Seconds';
		$secondsbackgroundcolor	= isset($_POST[$prefix.'timersecondsbackground_color']) ? wpcdt_slashes_deep($_POST[$prefix.'timersecondsbackground_color']) : '';
		
		update_post_meta($post_id, $prefix.'timer_date', $date);
		update_post_meta($post_id, $prefix.'timercircle_animation', $animation);
		update_post_meta($post_id, $prefix.'timercircle_width', $circlewidth);
		update_post_meta($post_id, $prefix.'timerbackground_width', $backgroundwidth);
		update_post_meta($post_id, $prefix.'timerbackground_color', $backgroundcolor);
		update_post_meta($post_id, $prefix.'is_timerhours', $is_hours);
		update_post_meta($post_id, $prefix.'timer_hour_text', $hours_text);
		update_post_meta($post_id, $prefix.'timerdaysbackground_color', $daysbackgroundcolor);
		update_post_meta($post_id, $prefix.'timer_width', $timer_width);
		update_post_meta($post_id, $prefix.'is_timerdays', $is_days);
		update_post_meta($post_id, $prefix.'timer_day_text', $days_text);
		update_post_meta($post_id, $prefix.'timerhoursbackground_color', $hoursbackgroundcolor);
		update_post_meta($post_id, $prefix.'is_timerminutes', $is_minutes);
		update_post_meta($post_id, $prefix.'timer_minute_text', $minutes_text);
		update_post_meta($post_id, $prefix.'timerminutesbackground_color', $minutesbackgroundcolor);
		update_post_meta($post_id, $prefix.'is_timerseconds', $is_seconds);
		update_post_meta($post_id, $prefix.'timer_second_text', $seconds_text);
		update_post_meta($post_id, $prefix.'timersecondsbackground_color', $secondsbackgroundcolor);
	}

	/**
	 * Add custom column to Post listing page
	 * 
	 * @package Countdown Timer Ultimate
	 * @since 1.0.0
	 */
	function wpcdt_posts_columns( $columns ) {

	    $new_columns['wpcdt_shortcode'] 	= __('Shortcode', 'countdown-timer-ultimate');
	    $columns = wpcdt_add_array( $columns, $new_columns, 1, true );

	    return $columns;
	}

	/**
	 * Add custom column data to Post listing page
	 * 
	 * @package Countdown Timer Ultimate
	 * @since 1.0.0
	 */
	function wpcdt_post_columns_data( $column, $post_id ) {

		global $post;

		// Taking some variables
		$prefix = WPCDT_META_PREFIX;

	    switch ($column) {
	    	case 'wpcdt_shortcode':
	    		
	    		echo '<div class="wpcdt-shortcode-preview">[wpcdt-countdown id="'.$post_id.'"]</div> <br/>';
	    		break;
		}
	}
}

$wpcdt_admin = new Wpcdt_Admin();