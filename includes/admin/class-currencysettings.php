<?php
/**
 * Admin settings for CHIP Woo Convert Currency.
 *
 * @package   CHIP_Woo_Convert_Currency
 * @author    Chip In Sdn Bhd
 * @license   GPL-3.0-or-later
 * @link      https://chip-in.asia
 * @since     1.1.0
 */

/**
 * Admin settings class.
 *
 * Injects configuration fields into WooCommerce → Settings → General.
 *
 * @package CHIP_Woo_Convert_Currency
 * @since   1.1.0
 */
class CurrencySettings {

	/**
	 * Singleton instance.
	 *
	 * @since 1.1.0
	 * @var   CurrencySettings|null
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.1.0
	 * @return CurrencySettings
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Register WordPress actions.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function add_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'run_scripts' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function run_scripts() {
		wp_enqueue_script( 'wcc-admin-settings' );
	}

	/**
	 * Register WordPress filters.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function add_filters() {
		add_filter( 'woocommerce_general_settings', array( $this, 'currency_settings_page' ) );
	}

	/**
	 * Append currency conversion settings to WooCommerce general settings.
	 *
	 * @since 1.1.0
	 * @param array|null $settings Existing settings array.
	 * @return array
	 */
	public function currency_settings_page( $settings = null ) {
		$options = array(
			'bnm'       => __( 'BNM', 'chip-woo-convert-currency' ),
			'oer'       => __( 'Open Exchange Rate API', 'chip-woo-convert-currency' ),
			'fixedrate' => __( 'Fixed Rate', 'chip-woo-convert-currency' ),
		);

		$addon_settings = array(
			array(
				'title' => __( 'CHIP Convert Currency API Options', 'chip-woo-convert-currency' ),
				'type'  => 'title',
				'desc'  => __( 'The following options convert the base currency to MYR for CHIP purposes', 'chip-woo-convert-currency' ),
				'id'    => 'wcc_api_options',
			),
			array(
				'title'    => __( 'API Options', 'chip-woo-convert-currency' ),
				'desc'     => __( 'Configure your preferred providers. Default: BNM', 'chip-woo-convert-currency' ),
				'id'       => 'chip_wcc_options',
				'default'  => 'bnm',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'desc_tip' => true,
				'options'  => $options,
			),
			array(
				'title'    => __( 'Open Exchange Rate API Key', 'chip-woo-convert-currency' ),
				'desc'     => __( 'If you are using Open Exchange Rate, you need to set the key for the exchange to work', 'chip-woo-convert-currency' ),
				'id'       => 'wcc_oer_key',
				'css'      => 'min-width: 50px;',
				'default'  => '',
				'desc_tip' => true,
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Fixed Exchange Rate', 'chip-woo-convert-currency' ),
				'desc'     => __( 'You may use your own exchange rate instead of using automated rates from API providers. By setting this option, the plugin will not fetch exchange rates from API providers.', 'chip-woo-convert-currency' ),
				'id'       => 'wcc_fixed_rate',
				'css'      => 'min-width: 50px;',
				'default'  => '',
				'desc_tip' => true,
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Percentage Charge', 'chip-woo-convert-currency' ),
				'desc'     => __( 'Add percentage charge. The charge calculations are added after conversion is done.', 'chip-woo-convert-currency' ),
				'id'       => 'wcc_percentage_rate',
				'css'      => 'min-width: 50px;',
				'default'  => '',
				'desc_tip' => true,
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Fixed Charge (cent in MYR)', 'chip-woo-convert-currency' ),
				'desc'     => __( 'Add fixed charge. The charge calculations are added after conversion is done.', 'chip-woo-convert-currency' ),
				'id'       => 'wcc_fixed_charge',
				'css'      => 'min-width: 50px;',
				'default'  => '',
				'desc_tip' => true,
				'type'     => 'text',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'api_options',
			),
		);

		if ( ! is_null( $settings ) ) {
			return array_merge( $settings, $addon_settings );
		}

		return $addon_settings;
	}
}

CurrencySettings::get_instance();
