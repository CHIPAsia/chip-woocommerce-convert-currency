<?php
/**
 * Main plugin class file.
 *
 * @package   CHIP_Woo_Convert_Currency
 * @author    Chip In Sdn Bhd
 * @license   GPL-3.0-or-later
 * @link      https://chip-in.asia
 * @since     1.0.0
 */

/**
 * Main plugin class.
 *
 * @package CHIP_Woo_Convert_Currency
 * @since   1.0.0
 */
class ChipWooConvertCurrency {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var   ChipWooConvertCurrency|null
	 */
	private static $instance = null;

	/**
	 * Exchange rate provider instance.
	 *
	 * @since 1.0.0
	 * @var   object|null
	 */
	private $provider;

	/**
	 * Percentage charge multiplier.
	 *
	 * @since 1.0.0
	 * @var   float
	 */
	private $charge_percent;

	/**
	 * Fixed charge in cents.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private $charge_fixed_cent;

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 * @return ChipWooConvertCurrency
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new ChipWooConvertCurrency();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->includes();
		$this->actions();

		$this->set_currency_provider();
		$this->set_charge_percent();
		$this->set_charge_fixed_cent();

		$this->add_repetitive_hooks();
		$this->add_action_links();
	}

	/**
	 * Register WordPress actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function actions() {
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'woocommerce_settings_save_general', array( $this, 'remove_transient' ) );

		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CHIP_WCC_FILE, true );
				}
			}
		);
	}

	/**
	 * Include admin files when in admin context.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		if ( is_admin() ) {
			$includes_dir = plugin_dir_path( CHIP_WCC_FILE ) . 'includes/admin/';
			include $includes_dir . 'class-currencysettings.php';
		}
	}

	/**
	 * Register admin scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_scripts() {
		wp_register_script(
			'wcc-admin-settings',
			trailingslashit( CHIP_WCC_URL ) . 'assets/js/admin/currency-settings.js',
			array( 'jquery' ),
			CHIP_WCC_MODULE_VERSION,
			false
		);
	}

	/**
	 * Add "Settings" link on the plugins page.
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public function add_action_links() {
		add_filter(
			'plugin_action_links_' . CHIP_WCC_BASENAME,
			function ( $links ) {
				$settings_link = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ),
					esc_html__( 'Settings', 'woocommerce' )
				);
				array_unshift( $links, $settings_link );
				return $links;
			}
		);
	}

	/**
	 * Attach hooks for all CHIP gateway instances.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function add_repetitive_hooks() {
		$chip_ids = array( 'wc_gateway_chip', 'wc_gateway_chip_2', 'wc_gateway_chip_3', 'wc_gateway_chip_4', 'wc_gateway_chip_5', 'wc_gateway_chip_6' );

		foreach ( $chip_ids as $chip_id ) {
			// Legacy wc_ prefixed hooks for v1.x backward compatibility.
			add_filter( "wc_{$chip_id}_purchase_params", array( $this, 'purchase_parameter' ), 10, 2 );
			add_filter( "wc_{$chip_id}_supported_currencies", array( $this, 'apply_base_currency' ) );
			add_filter( "wc_{$chip_id}_purchase_currency", array( $this, 'apply_myr_currency' ) );
			add_filter( "wc_{$chip_id}_can_refund_order", array( $this, 'can_refund_order' ), 10, 3 );

			// New chip_ prefixed hooks for v2.x compatibility.
			add_filter( "chip_{$chip_id}_purchase_params", array( $this, 'purchase_parameter' ), 10, 2 );
			add_filter( "chip_{$chip_id}_supported_currencies", array( $this, 'apply_base_currency' ) );
			add_filter( "chip_{$chip_id}_purchase_currency", array( $this, 'apply_myr_currency' ) );
			add_filter( "chip_{$chip_id}_can_refund_order", array( $this, 'can_refund_order' ), 10, 3 );
		}

		// WooCommerce Blocks: inject supported currencies so canMakePayment doesn't hide the gateway.
		add_filter( 'chip_blocks_payment_method_data', array( $this, 'blocks_payment_method_data' ), 10, 3 );
	}

	/**
	 * Disable refunds for converted orders.
	 *
	 * Refunds are disabled because the converted amounts make automatic refund
	 * processing unsafe.
	 *
	 * @since 1.0.0
	 * @param bool               $can_refund_order Whether the order can be refunded.
	 * @param WC_Order           $order            Order object.
	 * @param WC_Payment_Gateway $gateway     Payment gateway instance.
	 * @return bool
	 */
	public function can_refund_order( $can_refund_order, $order, $gateway ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return false;
	}

	/**
	 * Inject supported currencies into WooCommerce Blocks payment data.
	 *
	 * @since 1.3.0
	 * @param array  $payment_method_data Payment method data.
	 * @param string $name                Gateway name.
	 * @param object $gateway             Gateway instance.
	 * @return array
	 */
	public function blocks_payment_method_data( $payment_method_data, $name, $gateway ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		$chip_ids = array( 'wc_gateway_chip', 'wc_gateway_chip_2', 'wc_gateway_chip_3', 'wc_gateway_chip_4', 'wc_gateway_chip_5', 'wc_gateway_chip_6' );

		if ( in_array( $name, $chip_ids, true ) ) {
			$payment_method_data['supported_currencies'] = $this->apply_base_currency( array( 'MYR' ) );
		}

		return $payment_method_data;
	}

	/**
	 * Set the exchange rate provider.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_currency_provider() {
		$provider_option = get_option( 'chip_wcc_options' );

		if ( 'fixedrate' === $provider_option ) {
			$this->provider = null;
		} elseif ( 'oer' === $provider_option && get_option( 'wcc_oer_key' ) ) {
			require_once plugin_dir_path( CHIP_WCC_FILE ) . 'includes/class-chipopenexchangerate.php';
			$this->provider = ChipOpenExchangeRate::get_instance( get_option( 'wcc_oer_key' ) );
		} else {
			require_once plugin_dir_path( CHIP_WCC_FILE ) . 'includes/class-chipbnmapi.php';
			$this->provider = ChipBNMAPI::get_instance();
		}
	}

	/**
	 * Set the percentage charge multiplier.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_charge_percent() {
		$percentage = get_option( 'wcc_percentage_rate' );

		if ( $percentage ) {
			$this->charge_percent = ( (float) $percentage / 100.0 ) + 1.0;
		} else {
			$this->charge_percent = 1;
		}
	}

	/**
	 * Set the fixed charge in cents.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_charge_fixed_cent() {
		$fixed = get_option( 'wcc_fixed_charge' );

		if ( $fixed ) {
			$this->charge_fixed_cent = (int) $fixed;
		} else {
			$this->charge_fixed_cent = 0;
		}
	}

	/**
	 * Convert purchase parameters to MYR.
	 *
	 * @since 1.0.0
	 * @param array              $params  Purchase parameters.
	 * @param WC_Payment_Gateway $gateway Payment gateway.
	 * @return array
	 * @throws Exception If conversion rate cannot be retrieved.
	 */
	public function purchase_parameter( $params, $gateway ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( 'MYR' === $params['purchase']['currency'] ) {
			return $params;
		}

		$conversion_rate = $this->get_current_conversion();

		$product_count = count( $params['purchase']['products'] );
		for ( $i = 0; $i < $product_count; $i++ ) {
			$params['purchase']['products'][ $i ]['price'] = round( $params['purchase']['products'][ $i ]['price'] * $conversion_rate * $this->charge_percent + $this->charge_fixed_cent );
		}

		$params['purchase']['total_override'] = round( $params['purchase']['total_override'] * $conversion_rate * $this->charge_percent + $this->charge_fixed_cent );

		$params['purchase']['currency'] = 'MYR';

		return $params;
	}

	/**
	 * Force purchase currency to MYR.
	 *
	 * @since 1.0.0
	 * @param string $currency Current currency.
	 * @return string
	 */
	public function apply_myr_currency( $currency ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return 'MYR';
	}

	/**
	 * Declare supported currencies based on the active provider.
	 *
	 * @since 1.0.0
	 * @param array $currency List of supported currencies.
	 * @return array
	 */
	public function apply_base_currency( $currency ) {
		if ( $this->provider instanceof ChipOpenExchangeRate ) {
			$currency[] = 'AED';
			$currency[] = 'AFN';
			$currency[] = 'ALL';
			$currency[] = 'AMD';
			$currency[] = 'ANG';
			$currency[] = 'AOA';
			$currency[] = 'ARS';
			$currency[] = 'AUD';
			$currency[] = 'AWG';
			$currency[] = 'AZN';
			$currency[] = 'BAM';
			$currency[] = 'BBD';
			$currency[] = 'BDT';
			$currency[] = 'BGN';
			$currency[] = 'BHD';
			$currency[] = 'BIF';
			$currency[] = 'BMD';
			$currency[] = 'BND';
			$currency[] = 'BOB';
			$currency[] = 'BRL';
			$currency[] = 'BSD';
			$currency[] = 'BTC';
			$currency[] = 'BTN';
			$currency[] = 'BWP';
			$currency[] = 'BYN';
			$currency[] = 'BZD';
			$currency[] = 'CAD';
			$currency[] = 'CDF';
			$currency[] = 'CHF';
			$currency[] = 'CLF';
			$currency[] = 'CLP';
			$currency[] = 'CNH';
			$currency[] = 'CNY';
			$currency[] = 'COP';
			$currency[] = 'CRC';
			$currency[] = 'CUC';
			$currency[] = 'CUP';
			$currency[] = 'CVE';
			$currency[] = 'CZK';
			$currency[] = 'DJF';
			$currency[] = 'DKK';
			$currency[] = 'DOP';
			$currency[] = 'DZD';
			$currency[] = 'EGP';
			$currency[] = 'ERN';
			$currency[] = 'ETB';
			$currency[] = 'EUR';
			$currency[] = 'FJD';
			$currency[] = 'FKP';
			$currency[] = 'GBP';
			$currency[] = 'GEL';
			$currency[] = 'GGP';
			$currency[] = 'GHS';
			$currency[] = 'GIP';
			$currency[] = 'GMD';
			$currency[] = 'GNF';
			$currency[] = 'GTQ';
			$currency[] = 'GYD';
			$currency[] = 'HKD';
			$currency[] = 'HNL';
			$currency[] = 'HRK';
			$currency[] = 'HTG';
			$currency[] = 'HUF';
			$currency[] = 'IDR';
			$currency[] = 'ILS';
			$currency[] = 'IMP';
			$currency[] = 'INR';
			$currency[] = 'IQD';
			$currency[] = 'IRR';
			$currency[] = 'ISK';
			$currency[] = 'JEP';
			$currency[] = 'JMD';
			$currency[] = 'JOD';
			$currency[] = 'JPY';
			$currency[] = 'KES';
			$currency[] = 'KGS';
			$currency[] = 'KHR';
			$currency[] = 'KMF';
			$currency[] = 'KPW';
			$currency[] = 'KRW';
			$currency[] = 'KWD';
			$currency[] = 'KYD';
			$currency[] = 'KZT';
			$currency[] = 'LAK';
			$currency[] = 'LBP';
			$currency[] = 'LKR';
			$currency[] = 'LRD';
			$currency[] = 'LSL';
			$currency[] = 'LYD';
			$currency[] = 'MAD';
			$currency[] = 'MDL';
			$currency[] = 'MGA';
			$currency[] = 'MKD';
			$currency[] = 'MMK';
			$currency[] = 'MNT';
			$currency[] = 'MOP';
			$currency[] = 'MRU';
			$currency[] = 'MUR';
			$currency[] = 'MVR';
			$currency[] = 'MWK';
			$currency[] = 'MXN';
			$currency[] = 'MYR';
			$currency[] = 'MZN';
			$currency[] = 'NAD';
			$currency[] = 'NGN';
			$currency[] = 'NIO';
			$currency[] = 'NOK';
			$currency[] = 'NPR';
			$currency[] = 'NZD';
			$currency[] = 'OMR';
			$currency[] = 'PAB';
			$currency[] = 'PEN';
			$currency[] = 'PGK';
			$currency[] = 'PHP';
			$currency[] = 'PKR';
			$currency[] = 'PLN';
			$currency[] = 'PYG';
			$currency[] = 'QAR';
			$currency[] = 'RON';
			$currency[] = 'RSD';
			$currency[] = 'RUB';
			$currency[] = 'RWF';
			$currency[] = 'SAR';
			$currency[] = 'SBD';
			$currency[] = 'SCR';
			$currency[] = 'SDG';
			$currency[] = 'SEK';
			$currency[] = 'SGD';
			$currency[] = 'SHP';
			$currency[] = 'SLL';
			$currency[] = 'SOS';
			$currency[] = 'SRD';
			$currency[] = 'SSP';
			$currency[] = 'STD';
			$currency[] = 'STN';
			$currency[] = 'SVC';
			$currency[] = 'SYP';
			$currency[] = 'SZL';
			$currency[] = 'THB';
			$currency[] = 'TJS';
			$currency[] = 'TMT';
			$currency[] = 'TND';
			$currency[] = 'TOP';
			$currency[] = 'TRY';
			$currency[] = 'TTD';
			$currency[] = 'TWD';
			$currency[] = 'TZS';
			$currency[] = 'UAH';
			$currency[] = 'UGX';
			$currency[] = 'USD';
			$currency[] = 'UYU';
			$currency[] = 'UZS';
			$currency[] = 'VES';
			$currency[] = 'VND';
			$currency[] = 'VUV';
			$currency[] = 'WST';
			$currency[] = 'XAF';
			$currency[] = 'XAG';
			$currency[] = 'XAU';
			$currency[] = 'XCD';
			$currency[] = 'XDR';
			$currency[] = 'XOF';
			$currency[] = 'XPD';
			$currency[] = 'XPF';
			$currency[] = 'XPT';
			$currency[] = 'YER';
			$currency[] = 'ZAR';
			$currency[] = 'ZMW';
			$currency[] = 'ZWL';
		} elseif ( $this->provider instanceof ChipBNMAPI ) {
			$currency[] = 'JPY';
			$currency[] = 'AED';
			$currency[] = 'AUD';
			$currency[] = 'BND';
			$currency[] = 'CAD';
			$currency[] = 'CHF';
			$currency[] = 'CNY';
			$currency[] = 'EGP';
			$currency[] = 'EUR';
			$currency[] = 'GBP';
			$currency[] = 'HKD';
			$currency[] = 'IDR';
			$currency[] = 'INR';
			$currency[] = 'KHR';
			$currency[] = 'KRW';
			$currency[] = 'MMK';
			$currency[] = 'NPR';
			$currency[] = 'NZD';
			$currency[] = 'PHP';
			$currency[] = 'PKR';
			$currency[] = 'SAR';
			$currency[] = 'SGD';
			$currency[] = 'THB';
			$currency[] = 'TWD';
			$currency[] = 'USD';
			$currency[] = 'VND';
			$currency[] = 'SDR';
		} elseif ( null === $this->provider ) {
			$currency[] = 'MYR';
		}

		return $currency;
	}

	/**
	 * Get the current conversion rate to MYR.
	 *
	 * @since 1.0.0
	 * @return float|int Conversion rate.
	 * @throws Exception If the rate cannot be retrieved.
	 */
	public function get_current_conversion() {
		if ( null === $this->provider ) {
			return get_option( 'wcc_fixed_rate' );
		}

		$rates = $this->provider->get_rates( get_woocommerce_currency() );
		$rates = json_decode( $rates );

		if ( $rates && ! empty( $rates->base ) && ! empty( $rates->rates ) ) {
			return $rates->rates->MYR;
		}

		throw new Exception( esc_html__( 'Unable to get currency conversion rates', 'woocommerce' ) );
	}

	/**
	 * Clear the provider transient when WooCommerce general settings are saved.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function remove_transient() {
		if ( is_object( $this->provider ) && method_exists( $this->provider, 'delete_transient' ) ) {
			$this->provider->delete_transient();
		}
	}
}
