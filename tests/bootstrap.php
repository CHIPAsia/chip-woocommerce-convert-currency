<?php
/**
 * PHPUnit bootstrap file for CHIP Woo Convert Currency tests.
 *
 * @package CHIP_Woo_Convert_Currency
 */

// Define WordPress constants if not already defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

// In-memory option store for get_option/set_option stubs.
$_test_options = array();

// Stub WordPress functions used by the plugin at load time.
if ( ! function_exists( 'plugin_basename' ) ) {
	/**
	 * Stub for plugin_basename().
	 *
	 * @param string $file Plugin file path.
	 * @return string
	 */
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	/**
	 * Stub for plugin_dir_url().
	 *
	 * @param string $file Plugin file path.
	 * @return string
	 */
	function plugin_dir_url( $file ) {
		return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	/**
	 * Stub for plugin_dir_path().
	 *
	 * @param string $file Plugin file path.
	 * @return string
	 */
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Stub for add_action().
	 *
	 * @param string   $tag      Action hook name.
	 * @param callable $callback Callback function.
	 * @param int      $priority Priority of the action.
	 * @param int      $accepted_args Number of accepted arguments.
	 * @return true
	 */
	function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Stub for is_admin().
	 *
	 * @return bool
	 */
	function is_admin() {
		return false;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Stub for add_filter().
	 *
	 * @param string   $tag      Filter hook name.
	 * @param callable $callback Callback function.
	 * @param int      $priority Priority of the filter.
	 * @param int      $accepted_args Number of accepted arguments.
	 * @return true
	 */
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	/**
	 * Stub for trailingslashit().
	 *
	 * @param string $string Input string.
	 * @return string
	 */
	function trailingslashit( $string ) {
		return rtrim( $string, "/\'" ) . '/';
	}
}

if ( ! function_exists( 'wp_register_script' ) ) {
	/**
	 * Stub for wp_register_script().
	 *
	 * @param string $handle    Script handle.
	 * @param string $src         Script URL.
	 * @param array  $deps        Dependencies.
	 * @param string $version     Version string.
	 * @param bool   $in_footer   Whether to load in footer.
	 * @return true
	 */
	function wp_register_script( $handle, $src, $deps = array(), $version = false, $in_footer = false ) {
		return true;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Stub for get_option().
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function get_option( $option, $default = false ) {
		global $_test_options;
		return isset( $_test_options[ $option ] ) ? $_test_options[ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Stub for update_option().
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return true
	 */
	function update_option( $option, $value ) {
		global $_test_options;
		$_test_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Stub for delete_option().
	 *
	 * @param string $option Option name.
	 * @return true
	 */
	function delete_option( $option ) {
		global $_test_options;
		unset( $_test_options[ $option ] );
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Stub for get_transient().
	 *
	 * @param string $transient Transient name.
	 * @return mixed
	 */
	function get_transient( $transient ) {
		global $_test_options;
		return isset( $_test_options[ '_transient_' . $transient ] ) ? $_test_options[ '_transient_' . $transient ] : false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Stub for set_transient().
	 *
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Expiration in seconds.
	 * @return true
	 */
	function set_transient( $transient, $value, $expiration = 0 ) {
		global $_test_options;
		$_test_options[ '_transient_' . $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Stub for delete_transient().
	 *
	 * @param string $transient Transient name.
	 * @return true
	 */
	function delete_transient( $transient ) {
		global $_test_options;
		unset( $_test_options[ '_transient_' . $transient ] );
		return true;
	}
}

if ( ! function_exists( 'get_woocommerce_currency' ) ) {
	/**
	 * Stub for get_woocommerce_currency().
	 *
	 * @return string
	 */
	function get_woocommerce_currency() {
		global $_test_options;
		return isset( $_test_options['__woocommerce_currency__'] ) ? $_test_options['__woocommerce_currency__'] : 'USD';
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Stub for is_wp_error().
	 *
	 * @param mixed $thing Object to check.
	 * @return bool
	 */
	function is_wp_error( $thing ) {
		return false;
	}
}

if ( ! function_exists( 'wp_safe_remote_get' ) ) {
	/**
	 * Stub for wp_safe_remote_get().
	 *
	 * @param string $url  URL to fetch.
	 * @param array  $args Request arguments.
	 * @return array
	 */
	function wp_safe_remote_get( $url, $args = array() ) {
		return array();
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	/**
	 * Stub for wp_remote_retrieve_body().
	 *
	 * @param array $response Response array.
	 * @return string
	 */
	function wp_remote_retrieve_body( $response ) {
		return '';
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Stub for apply_filters().
	 *
	 * @param string $tag    Filter name.
	 * @param mixed  $value  Value to filter.
	 * @return mixed
	 */
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * Stub for esc_html__().
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	/**
	 * Stub for esc_url().
	 *
	 * @param string $url URL to escape.
	 * @return string
	 */
	function esc_url( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	/**
	 * Stub for admin_url().
	 *
	 * @param string $path Path relative to admin.
	 * @return string
	 */
	function admin_url( $path = '' ) {
		return 'http://example.com/wp-admin/' . $path;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Stub for wp_json_encode().
	 *
	 * @param mixed $data Data to encode.
	 * @return string
	 */
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

// Load Composer autoloader if available.
$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

/**
 * Load the main plugin file so classes are available for testing.
 */
require_once dirname( __DIR__ ) . '/chip-woo-convert-currency.php';

// Explicitly load provider classes (normally loaded conditionally).
require_once dirname( __DIR__ ) . '/includes/BankNegaraMalaysia.php';
require_once dirname( __DIR__ ) . '/includes/OpenExchangeRate.php';

// Explicitly load admin settings (normally loaded only when is_admin()).
require_once dirname( __DIR__ ) . '/includes/admin/currency-settings.php';

// Trigger includes so all plugin classes are loaded.
ChipWooConvertCurrency::get_instance();
