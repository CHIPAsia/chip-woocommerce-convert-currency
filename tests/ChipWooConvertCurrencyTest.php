<?php
/**
 * Class ChipWooConvertCurrencyTest
 *
 * Unit tests for the CHIP Woo Convert Currency plugin.
 *
 * @package CHIP_Woo_Convert_Currency
 */

/**
 * Test case for ChipWooConvertCurrency class.
 */
class ChipWooConvertCurrencyTest extends PHPUnit\Framework\TestCase {

	/**
	 * Reset test options before each test.
	 */
	protected function setUp(): void {
		global $_test_options;
		$_test_options = array();
	}

	/**
	 * Helper to get the singleton instance.
	 *
	 * @return ChipWooConvertCurrency
	 */
	private function get_instance(): ChipWooConvertCurrency {
		return ChipWooConvertCurrency::get_instance();
	}

	/**
	 * Helper to set the provider on the singleton via reflection.
	 *
	 * @param mixed $provider Provider instance or null.
	 */
	private function set_provider( $provider ): void {
		$reflection = new ReflectionClass( 'ChipWooConvertCurrency' );
		$property   = $reflection->getProperty( 'provider' );
		$property->setAccessible( true );
		$property->setValue( $this->get_instance(), $provider );
	}

	/**
	 * Test that the main plugin constant is defined and matches the plugin header.
	 *
	 * The version must not be hard-coded here: `scripts/bump-version.sh` updates
	 * the header and the constant but knows nothing about this file, so a frozen
	 * literal turns every version bump into a red build. The assertion below is
	 * bump-proof - it checks the two places stay in sync, which is the property
	 * that actually matters (a mismatch means the plugin tells WordPress one
	 * version and its own code another).
	 */
	public function test_plugin_constant_defined() {
		$this->assertTrue( defined( 'CHIP_WCC_MODULE_VERSION' ), 'CHIP_WCC_MODULE_VERSION should be defined.' );

		$constant = constant( 'CHIP_WCC_MODULE_VERSION' );
		$this->assertMatchesRegularExpression(
			'/^v\d+\.\d+\.\d+$/',
			$constant,
			'CHIP_WCC_MODULE_VERSION should look like vX.Y.Z.'
		);

		$header = $this->read_plugin_header_version();
		$this->assertSame(
			$header,
			ltrim( $constant, 'v' ),
			'CHIP_WCC_MODULE_VERSION should match the plugin header Version (the constant carries a leading "v", the header does not).'
		);
	}

	/**
	 * Read the Version from the plugin file header.
	 *
	 * @return string Version as written in the header (e.g. "1.3.2").
	 */
	private function read_plugin_header_version(): string {
		$source = (string) file_get_contents( dirname( __DIR__ ) . '/chip-woo-convert-currency.php' );
		$this->assertSame(
			1,
			preg_match( '/^[ 	]*\*[ 	]+Version:[ 	]*([0-9]+\.[0-9]+\.[0-9]+)/m', $source, $matches ),
			'The plugin header should declare a "Version: X.Y.Z" line.'
		);

		return $matches[1];
	}

	/**
	 * Test that the main class exists.
	 */
	public function test_main_class_exists() {
		$this->assertTrue( class_exists( 'ChipWooConvertCurrency' ), 'ChipWooConvertCurrency class should be available.' );
	}

	/**
	 * Test that the BNM provider class exists.
	 */
	public function test_bnm_provider_class_exists() {
		$this->assertTrue( class_exists( 'ChipBNMAPI' ), 'ChipBNMAPI class should be available.' );
	}

	/**
	 * Test that the OER provider class exists.
	 */
	public function test_oer_provider_class_exists() {
		$this->assertTrue( class_exists( 'ChipOpenExchangeRate' ), 'ChipOpenExchangeRate class should be available.' );
	}

	/**
	 * Test that the admin settings class exists.
	 */
	public function test_admin_settings_class_exists() {
		$this->assertTrue( class_exists( 'CurrencySettings' ), 'CurrencySettings class should be available.' );
	}

	/**
	 * Test that can_refund_order returns false for non-MYR (converted) orders.
	 */
	public function test_can_refund_order_returns_false_for_converted_order() {
		$instance = $this->get_instance();
		$order    = $this->mock_order( 'USD' );
		$result   = $instance->can_refund_order( true, $order, null );
		$this->assertFalse( $result, 'can_refund_order should return false for converted (non-MYR) orders.' );
	}

	/**
	 * Test that can_refund_order preserves the incoming value for MYR orders.
	 */
	public function test_can_refund_order_preserves_value_for_myr_order() {
		$instance = $this->get_instance();
		$order    = $this->mock_order( 'MYR' );
		$result   = $instance->can_refund_order( true, $order, null );
		$this->assertTrue( $result, 'can_refund_order should preserve true for MYR orders.' );
	}

	/**
	 * Build a minimal order stub exposing get_currency().
	 *
	 * @param string $currency Order currency.
	 * @return object
	 */
	private function mock_order( $currency ) {
		return new class( $currency ) {
			/** @var string */
			private $currency;

			/**
			 * Constructor.
			 *
			 * @param string $currency Order currency.
			 */
			public function __construct( $currency ) {
				$this->currency = $currency;
			}

			/**
			 * Return the order currency.
			 *
			 * @return string
			 */
			public function get_currency() {
				return $this->currency;
			}
		};
	}

	/**
	 * Test that apply_myr_currency returns MYR.
	 */
	public function test_apply_myr_currency_returns_myr() {
		$instance = $this->get_instance();
		$result   = $instance->apply_myr_currency( 'USD' );
		$this->assertEquals( 'MYR', $result, 'apply_myr_currency should always return MYR.' );
	}

	/**
	 * Test that purchase_parameter skips conversion when currency is already MYR.
	 */
	public function test_purchase_parameter_skips_myr() {
		$instance = $this->get_instance();
		$params   = array(
			'purchase' => array(
				'currency' => 'MYR',
				'products' => array(
					array( 'price' => 1000 ),
				),
				'total_override' => 1000,
			),
		);

		$result = $instance->purchase_parameter( $params, null );
		$this->assertEquals( $params, $result, 'purchase_parameter should return unchanged when currency is MYR.' );
	}

	/**
	 * Test that apply_base_currency adds MYR when provider is null (fixed rate).
	 */
	public function test_apply_base_currency_with_fixed_rate() {
		$this->set_provider( null );
		$instance = $this->get_instance();
		$result   = $instance->apply_base_currency( array( 'MYR' ) );

		$this->assertContains( 'MYR', $result, 'Fixed rate provider should support MYR.' );
	}

	/**
	 * Test that blocks_payment_method_data adds supported currencies for CHIP gateways.
	 */
	public function test_blocks_payment_method_data_adds_currencies() {
		$this->set_provider( null );
		$instance = $this->get_instance();
		$data     = array( 'supported_currencies' => array() );
		$result   = $instance->blocks_payment_method_data( $data, 'wc_gateway_chip', null );

		$this->assertArrayHasKey( 'supported_currencies', $result );
		$this->assertContains( 'MYR', $result['supported_currencies'] );
	}

	/**
	 * Test that blocks_payment_method_data ignores non-CHIP gateways.
	 */
	public function test_blocks_payment_method_data_ignores_other_gateways() {
		$instance = $this->get_instance();
		$data     = array( 'supported_currencies' => array() );
		$result   = $instance->blocks_payment_method_data( $data, 'other_gateway', null );

		$this->assertEmpty( $result['supported_currencies'] );
	}

	/**
	 * Test BNM provider can be instantiated.
	 */
	public function test_bnm_provider_instantiation() {
		$provider = ChipBNMAPI::get_instance();
		$this->assertInstanceOf( 'ChipBNMAPI', $provider );
	}

	/**
	 * Test OER provider can be instantiated.
	 */
	public function test_oer_provider_instantiation() {
		$provider = ChipOpenExchangeRate::get_instance( 'test-key' );
		$this->assertInstanceOf( 'ChipOpenExchangeRate', $provider );
	}
}
