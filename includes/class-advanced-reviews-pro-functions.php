<?php

/**
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Helper functions
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Transform minutes, hours, days to seconds
 *
 * @param $type
 *
 * @return float|int
 */
function get_seconds_from_units( $type ) {

	$units = 0;
	switch ( $type ) {
		case 'minutes':
			$units = 60;
			break;
		case 'hours':
			$units = 60 * 60;
			break;
		case 'days':
			$units = 60 * 60 * 60;
			break;
	}

	return $units;
}
