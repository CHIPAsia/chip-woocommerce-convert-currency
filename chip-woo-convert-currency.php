<?php

/**
 * Plugin Name: CHIP Woo Convert Currency
 * Description: Convert unsupported currency to MYR for CHIP for WooCommerce
 * Version: 1.1.0
 * Author: Chip In Sdn Bhd
 * Author URI: https://www.chip-in.asia

 * WC requires at least: 3.3.4
 * WC tested up to: 7.0.0
 *
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

class ChipWooConvertCurrency
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ChipWooConvertCurrency();
        }
 
        return self::$instance;
    }

    private $provider;
    private $charge_percent;
    private $charge_fixed_cent;

    public function __construct()
    {
        $this->define();
        $this->includes();
        $this->actions();

        $this->set_currency_provider();
        $this->set_charge_percent();
        $this->set_charge_fixed_cent();

        $this->add_repeative_hooks();
        add_action('woocommerce_settings_save_general', array($this, 'remove_transient'));
    }

    public function actions() {
        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action('woocommerce_settings_save_general', array($this, 'remove_transient'));
    }

    public function define() {
      define( 'CHIP_WCC_MODULE_VERSION', 'v1.1.0' );
      define( 'CHIP_WCC_FILE', __FILE__ );
      define( 'CHIP_WCC_BASENAME', plugin_basename( CHIP_WCC_FILE ));
      define( 'CHIP_WCC_URL', plugin_dir_url( CHIP_WCC_FILE ));
    }

    public function includes() {
      if ( is_admin() ) {
        $includes_dir = plugin_dir_path( CHIP_WCC_FILE ) . 'includes/admin/';
        include $includes_dir . 'currency-settings.php';
      }
    }

    public function register_scripts() {
      wp_register_script(
        "wcc-admin-settings",
        trailingslashit( CHIP_WCC_URL ) . 'assets/js/admin/currency-settings.js',
        array( 'jquery' ),
        CHIP_WCC_MODULE_VERSION,
      );
    }

    private function add_repeative_hooks() {
      $chip_ids = ['wc_gateway_chip', 'wc_gateway_chip_2', 'wc_gateway_chip_3', 'wc_gateway_chip_4', 'wc_gateway_chip_5', 'wc_gateway_chip_6'];

      foreach ( $chip_ids as $chip_id ) {
        add_filter( "wc_{$chip_id}_purchase_params", array($this, 'purchase_parameter'), 10, 2);
        add_filter( "wc_{$chip_id}_supported_currencies", array($this, 'apply_base_currency'));
        add_filter( "wc_{$chip_id}_purchase_currency", array($this, 'apply_myr_currency'));
        add_filter( "wc_{$chip_id}_can_refund_order", array($this, 'can_refund_order'), 10, 3);
      }

    
    }

    public function can_refund_order( $can_refund_order, $order, $gateway )
    {
        return false;
    }

    public function set_currency_provider()
    {
        if (get_option('chip_wcc_options') == 'fixedrate'){
          $this->provider = null;
        } else if (get_option('chip_wcc_options') AND get_option('chip_wcc_options') == 'oer' AND get_option('wcc_oer_key')){
            require_once 'includes/OpenExchangeRate.php';
            $this->provider = ChipOpenExchangeRate::getInstance(get_option('wcc_oer_key'));
        } else {
            require_once 'includes/BankNegaraMalaysia.php';
            $this->provider = ChipBNMAPI::getInstance();
        }
    }

    public function set_charge_percent()
    {
        if (get_option('wcc_percentage_rate')){
          $this->charge_percent = get_option('wcc_percentage_rate') / 100.0 + 1.0;
        } else {
          $this->charge_percent = 1;
        }
    }

    public function set_charge_fixed_cent()
    {
        if (get_option('wcc_fixed_charge')) {
          $this->charge_fixed_cent = get_option('wcc_fixed_charge');
        } else {
          $this->charge_fixed_cent = 0;
        }
    }

    public function purchase_parameter($params, $gateway)
    {
        if ( $params['purchase']['currency'] == 'MYR' ) {
          return $params;
        }

        $conversion_rate = $this->get_current_conversion();

        for( $i = 0; $i < sizeof( $params['purchase']['products'] ); $i++ ) {
          $params['purchase']['products'][$i]['price'] = round( $params['purchase']['products'][$i]['price'] * $conversion_rate * $this->charge_percent + $this->charge_fixed_cent );
        }

        $params['purchase']['total_override'] = round( $params['purchase']['total_override'] * $conversion_rate * $this->charge_percent + $this->charge_fixed_cent );

        $params['purchase']['currency'] = 'MYR';

        return $params;
    }

    public function apply_myr_currency($currency)
    {
        return 'MYR';
    }

    public function apply_base_currency($currency)
    {
        if ($this->provider instanceof ChipOpenExchangeRate){
          array_push($currency,"AED","AFN","ALL","AMD","ANG","AOA","ARS","AUD","AWG","AZN","BAM","BBD","BDT","BGN","BHD","BIF","BMD","BND","BOB","BRL","BSD","BTC","BTN","BWP","BYN","BZD","CAD","CDF","CHF","CLF","CLP","CNH","CNY","COP","CRC","CUC","CUP","CVE","CZK","DJF","DKK","DOP","DZD","EGP","ERN","ETB","EUR","FJD","FKP","GBP","GEL","GGP","GHS","GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG","HUF","IDR","ILS","IMP","INR","IQD","IRR","ISK","JEP","JMD","JOD","JPY","KES","KGS","KHR","KMF","KPW","KRW","KWD","KYD","KZT","LAK","LBP","LKR","LRD","LSL","LYD","MAD","MDL","MGA","MKD","MMK","MNT","MOP","MRU","MUR","MVR","MWK","MXN","MYR","MZN","NAD","NGN","NIO","NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR","PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD","SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP","STD","STN","SVC","SYP","SZL","THB","TJS","TMT","TND","TOP","TRY","TTD","TWD","TZS","UAH","UGX","USD","UYU","UZS","VES","VND","VUV","WST","XAF","XAG","XAU","XCD","XDR","XOF","XPD","XPF","XPT","YER","ZAR","ZMW","ZWL");
        } elseif ($this->provider instanceof ChipBNMAPI) {
          array_push($currency,"JPY","AED","AUD","BND","CAD","CHF","CNY","EGP","EUR","GBP","HKD","IDR","INR","KHR","KRW","MMK","NPR","NZD","PHP","PKR","SAR","SGD","THB","TWD","USD","VND","SDR");          
        } elseif (is_null($this->provider)){
          array_push($currency, 'MYR');
        }

        return $currency;
    }

    public function get_current_conversion()
    {
        if (is_null($this->provider)){
            return get_option('wcc_fixed_rate');
        }

        $rates = $this->provider->getRates(get_woocommerce_currency());

        $rates = json_decode($rates);

        if ($rates && !empty($rates->base) && !empty($rates->rates)) {
            return $rates->rates->MYR;
        }

        throw new Exception( 'Unable to get currency conversion rates' );
    }

    public function remove_transient()
    {
      if (is_object($this->provider) AND method_exists($this->provider, 'delete_transient')){
        $this->provider->delete_transient();
      }
    }

}

ChipWooConvertCurrency::getInstance();