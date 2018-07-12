<?php

/**
 * Fired during plugin activation
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Advanced_Reviews_Pro_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::restore_decimal_ratings();

	}

	/**
	 * Restores up all the decimal ratings that were saved on plugin deactivation
	 *
	 * @since      1.0.0
	 */
	private static function restore_decimal_ratings() {

		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT meta_key,meta_value,comment_id FROM $wpdb->commentmeta WHERE meta_key = 'arp_old_rating' OR meta_key = 'rating'", array() )
		);

		$structured_results = array();

		foreach ( $results as $result ) {
			$structured_results[ $result->comment_id ][ $result->meta_key ] = $result->meta_value;
		}

		foreach ( $structured_results as $comment_id => $result ) {
			if ( ! isset( $result['arp_old_rating'] ) ) {
				continue;
			}

			if ( strval( absint( round( $result['arp_old_rating'], 0, PHP_ROUND_HALF_UP ) ) ) === strval( $result['rating'] ) ) {
				update_comment_meta( $comment_id, 'rating', $result['arp_old_rating'] );
				delete_comment_meta( $comment_id, 'arp_old_rating' );
			}
		}

	}

}
