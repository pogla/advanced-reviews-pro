<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Advanced_Reviews_Pro_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		self::cleanup_decimal_ratings();
	}

	/**
	 * Cleans up all the decimal ratings
	 *
	 * @since      1.0.0
	 */
	private static function cleanup_decimal_ratings() {

		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT meta_value,comment_id FROM $wpdb->commentmeta WHERE meta_key = 'rating'", array() )
		);

		if ( $results ) {
			foreach ( $results as $result ) {
				// Remember rating if decimal
				if ( strval( absint( round( $result->meta_value, 0, PHP_ROUND_HALF_UP ) ) ) !== strval( $result->meta_value ) ) {
					update_comment_meta( $result->comment_id, 'arp_old_rating', $result->meta_value );
				}
				update_comment_meta( $result->comment_id, 'rating', round( $result->meta_value, 0, PHP_ROUND_HALF_UP ) );
			}
		}

	}

}
