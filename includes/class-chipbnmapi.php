<?php
/**
 * Bank Negara Malaysia API provider.
 *
 * @package   CHIP_Woo_Convert_Currency
 * @author    Chip In Sdn Bhd
 * @license   GPL-3.0-or-later
 * @link      https://chip-in.asia
 * @since     1.0.0
 */

/**
 * BNM exchange rate provider.
 *
 * @package CHIP_Woo_Convert_Currency
 * @since   1.0.0
 */
class ChipBNMAPI {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var   ChipBNMAPI|null
	 */
	private static $instance = null;

	/**
	 * BNM API endpoint.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	const URL = 'https://api.bnm.gov.my/public/exchange-rate';

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 * @return ChipBNMAPI
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new ChipBNMAPI();
		}

		return self::$instance;
	}

	/**
	 * Fetch exchange rates for the given currency.
	 *
	 * @since 1.0.0
	 * @param string $woocommerce_currency WooCommerce base currency.
	 * @return string|false JSON-encoded rates or false on failure.
	 */
	public function get_rates( $woocommerce_currency ) {
		// BNM doesn't accept a User-Agent. Thus, it must be set to null.
		$header = array(
			'Accept'     => 'application/vnd.BNM.API.v1+json',
			'User-Agent' => null,
		);

		$json_rates = get_transient( 'wc_chip_amount_converter_bnm' );

		if ( false === $json_rates ) {
			$base  = $woocommerce_currency;
			$query = http_build_query( $this->get_query_params() );
			$rates = wp_remote_retrieve_body(
				wp_safe_remote_get(
					self::URL . "?{$query}",
					array(
						'headers' => $header,
					)
				)
			);

			$array_return = json_decode( $rates, true );

			$display = null;
			if ( isset( $array_return['data'] ) && is_array( $array_return['data'] ) ) {
				foreach ( $array_return['data'] as $value ) {
					if ( in_array( $base, $value, true ) ) {
						$display = $value;
						break;
					}
				}
			}

			// Validate display data before using it.
			if (
				null === $display
				|| ! isset( $display['currency_code'] )
				|| ! isset( $display['rate']['selling_rate'] )
				|| ! isset( $display['unit'] )
				|| 0 === $display['unit']
			) {
				return false;
			}

			$check_rates = array(
				'base'  => $display['currency_code'],
				'rates' => array(
					'MYR' => $display['rate']['selling_rate'] / $display['unit'],
				),
			);

			$json_rates = wp_json_encode( $check_rates );

			// Cache only on success.
			if ( ! is_wp_error( $rates ) && ! empty( $rates ) ) {
				$transient_timeout = apply_filters( 'wc_chip_currency_provider_refresh_minutes', 30 );
				set_transient( 'wc_chip_amount_converter_bnm', $json_rates, MINUTE_IN_SECONDS * $transient_timeout );
			}
		}

		return $json_rates;
	}

	/**
	 * Build query parameters for the BNM API.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_query_params() {
		return array(
			'quote' => 'RM',
		);
	}

	/**
	 * Delete the cached transient.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function delete_transient() {
		delete_transient( 'wc_chip_amount_converter_bnm' );
	}
}
