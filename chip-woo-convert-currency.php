<?php
/**
 * CHIP Woo Convert Currency
 *
 * @package   CHIP_Woo_Convert_Currency
 * @author    Chip In Sdn Bhd
 * @license   GPL-3.0-or-later
 * @link      https://chip-in.asia
 *
 * Plugin Name: CHIP Woo Convert Currency
 * Plugin URI: https://wordpress.org/plugins/chip-woo-convert-currency/
 * Description: Convert unsupported currency to MYR for CHIP for WooCommerce.
 * Version: 1.3.1
 * Author: Chip In Sdn Bhd
 * Author URI: https://chip-in.asia
 * Requires PHP: 7.4
 * Requires at least: 6.3
 *
 * WC requires at least: 5.1
 * WC tested up to: 10.8
 * Requires Plugins: woocommerce
 *
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'CHIP_WCC_MODULE_VERSION' ) || define( 'CHIP_WCC_MODULE_VERSION', 'v1.3.1' );
defined( 'CHIP_WCC_FILE' ) || define( 'CHIP_WCC_FILE', __FILE__ );
defined( 'CHIP_WCC_BASENAME' ) || define( 'CHIP_WCC_BASENAME', plugin_basename( CHIP_WCC_FILE ) );
defined( 'CHIP_WCC_URL' ) || define( 'CHIP_WCC_URL', plugin_dir_url( CHIP_WCC_FILE ) );

require_once plugin_dir_path( CHIP_WCC_FILE ) . 'includes/class-chipwooconvertcurrency.php';

ChipWooConvertCurrency::get_instance();
