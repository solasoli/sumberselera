<?php
/**
 * 'wpcdt-countdown' Shortcode
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function wpcdt_countdown_timer( $atts, $content = null ) {
	
	// Shortcode Parameter
	extract(shortcode_atts(array(
		'id'				=> '',
	), $atts));

	$id 				= !empty($id) 					? $id 							: '';

	$unique= wpcdt_get_unique();
	$prefix = WPCDT_META_PREFIX;
	
	wp_enqueue_script( 'wpcdt-timecircle-js' );
	wp_enqueue_script( 'wpcdt-public-js' );

	$date 		= get_post_meta($id, $prefix.'timer_date', true);
	$status 	= get_post_status( $id );
	$width 		= get_post_meta($id, $prefix.'timer_width', true);
	ob_start();

	if ( !empty($date) && $status == 'publish'  ) { ?>

		<div class="wpcdt-countdown-wrp wpcdt-clearfix">
			
			<div id="wpcdt-datecount-<?php echo $unique; ?>" class="wpcdt-countdown-timer" data-date="<?php echo $date; ?>" style="max-width:<?php echo $width; ?>px">
				
				<?php
					$date 					= get_post_meta($id, $prefix.'timer_date', true);
					$animation				= get_post_meta($id, $prefix.'timercircle_animation', true);
					$circlewidth			= get_post_meta($id, $prefix.'timercircle_width', true);
					$backgroundwidth		= get_post_meta($id, $prefix.'timerbackground_width', true);
					$backgroundcolor		= get_post_meta($id, $prefix.'timerbackground_color', true);
					$is_days				= get_post_meta($id, $prefix.'is_timerdays', true);
					$daysbackgroundcolor	= get_post_meta($id, $prefix.'timerdaysbackground_color', true);
					$is_hours				= get_post_meta($id, $prefix.'is_timerhours', true);
					$hoursbackgroundcolor	= get_post_meta($id, $prefix.'timerhoursbackground_color', true);
					$is_minutes				= get_post_meta($id, $prefix.'is_timerminutes', true);
					$minutesbackgroundcolor	= get_post_meta($id, $prefix.'timerminutesbackground_color', true);
					$is_seconds				= get_post_meta($id, $prefix.'is_timerseconds', true);
					$secondsbackgroundcolor	= get_post_meta($id, $prefix.'timersecondsbackground_color', true);
					$days_text 				= get_post_meta($id, $prefix.'timer_day_text', true);
					$hours_text 			= get_post_meta($id, $prefix.'timer_hour_text', true);
					$minutes_text 			= get_post_meta($id, $prefix.'timer_minute_text', true);
					$seconds_text 			= get_post_meta($id, $prefix.'timer_second_text', true);

					$date_conf 				= compact('animation', 'circlewidth', 'backgroundwidth', 'backgroundcolor', 'is_days', 'daysbackgroundcolor', 'is_hours', 'hoursbackgroundcolor','is_minutes','minutesbackgroundcolor','is_seconds','secondsbackgroundcolor','days_text','hours_text','minutes_text','seconds_text');
				?>
			</div>
			
			<div class="wpcdt-date-conf" data-conf="<?php echo htmlspecialchars(json_encode($date_conf)); ?>"></div>
		</div><!-- end .wpcdt-countdown-wrp -->
	<?php $content .= ob_get_clean();
		  return $content;
	 }
}

// 'aigpl-gallery' shortcode
add_shortcode('wpcdt-countdown', 'wpcdt_countdown_timer');