<?php
/**
 * Plugin generic functions file
 *
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Function to unique number value
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_get_unique() {
	static $unique = 0;
	$unique++;

	return $unique;
}

/**
 * Escape Tags & Slashes. Handles escapping the slashes and tags
 *
 * @package  Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_escape_attr($data){
	return esc_attr(stripslashes($data));
}


/**
 * Strip Slashes From Array
 *
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_slashes_deep($data = array(), $flag = false) {
  
    if($flag != true) {
        $data = wpcdt_nohtml_kses($data);
    }
    $data = stripslashes_deep($data);
    return $data;
}

/**
 * Strip Html Tags 
 * 
 * It will sanitize text input (strip html tags, and escape characters)
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */

function wpcdt_nohtml_kses($data = array()) {
  
  if ( is_array($data) ) {
    
    $data = array_map('wpcdt_nohtml_kses', $data);
    
  } elseif ( is_string( $data ) ) {
    $data = trim( $data );
    $data = wp_filter_nohtml_kses($data);
  }
  
  return $data;
}

/**
 * Function to add array after specific key
 * 
 * @package Countdown Timer Ultimate
 * @since 1.0.0
 */
function wpcdt_add_array(&$array, $value, $index, $from_last = false) {
    
    if( is_array($array) && is_array($value) ) {

        if( $from_last ) {
            $total_count    = count($array);
            $index          = (!empty($total_count) && ($total_count > $index)) ? ($total_count-$index): $index;
        }
        
        $split_arr  = array_splice($array, max(0, $index));
        $array      = array_merge( $array, $value, $split_arr);
    }
    
    return $array;
}