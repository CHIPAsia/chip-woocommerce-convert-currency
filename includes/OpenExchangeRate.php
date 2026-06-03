<?php
/**
 * Open Exchange Rate API provider.
 *
 * @package   CHIP_Woo_Convert_Currency
 * @author    Chip In Sdn Bhd
 * @license   GPL-3.0-or-later
 * @link      https://chip-in.asia
 * @since     1.0.0
 */

/**
 * OER exchange rate provider.
 *
 * @package CHIP_Woo_Convert_Currency
 * @since   1.0.0
 */
class ChipOpenExchangeRate {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var   ChipOpenExchangeRate|null
	 */
	private static $instance = null;

	/**
	 * Application ID (API key).
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $app_id;

	/**
	 * OER API endpoint.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	const URL = 'https://openexchangerates.org/api/latest.json';

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 * @param string $oer_key Open Exchange Rate API key.
	 * @return ChipOpenExchangeRate
	 */
	public static function get_instance( $oer_key ) {
		if ( null === self::$instance ) {
			self::$instance = new ChipOpenExchangeRate( $oer_key );
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $app_id Application ID (API key).
	 */
	public function __construct( $app_id ) {
		$this->app_id = $app_id;
	}

	/**
	 * Fetch exchange rates for the given currency.
	 *
	 * @since 1.0.0
	 * @param string $woocommerce_currency WooCommerce base currency.
	 * @return string|false JSON-encoded rates or false on failure.
	 */
	public function get_rates( $woocommerce_currency ) {
		$rates = get_transient( 'wc_chip_amount_converter_oer' );

		if ( false === $rates ) {
			$base  = $woocommerce_currency;
			$query = http_build_query( $this->get_query_params( $base ) );
			$rates = wp_remote_retrieve_body( wp_safe_remote_get( self::URL . "?{$query}" ) );
			$check_rates = json_decode( $rates );

			// Cache only on success.
			if ( ! is_wp_error( $rates ) && empty( $check_rates->error ) && ! empty( $rates ) ) {
				$transient_timeout = apply_filters( 'wc_chip_currency_provider_refresh_minutes', 30 );
				set_transient( 'wc_chip_amount_converter_oer', $rates, MINUTE_IN_SECONDS * $transient_timeout );
			}
		}

		return $rates;
	}

	/**
	 * Build query parameters for the OER API.
	 *
	 * @since 1.0.0
	 * @param string $base Base currency.
	 * @return array
	 */
	private function get_query_params( $base = 'USD' ) {
		return array(
			'base'        => $base,
			'symbols'     => 'MYR',
			'app_id'      => $this->app_id,
		);
	}

	/**
	 * Delete the cached transient.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function delete_transient() {
		delete_transient( 'wc_chip_amount_converter_oer' );
	}
}
