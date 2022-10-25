<?php

/**
 * Plugin Name: CHIP Woo Convert Currency
 * Description: Convert unsupported currency to MYR for CHIP for WooCommerce
 * Version: 1.0.0
 * Author: Chip In Sdn Bhd
 * Author URI: https://www.chip-in.asia

 * WC requires at least: 3.3.4
 * WC tested up to: 7.0.0
 *
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// define('CHIP_WOO_CC_PROVIDER', 'bnm');
// define('CHIP_WOO_CC_OER_KEY', '<key-here>');
// define('CHIP_WOO_CC_CHARGE_PERCENT', 0);
// define('CHIP_WOO_CC_CHARGE_FIXED_CENT', 0);
// define('CHIP_WOO_CC_DEFINE_YOUR_OWN', 1);

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
        $this->set_currency_provider();
        $this->set_charge_percent();
        $this->set_charge_fixed_cent();
        
        add_filter('wc_chip_purchase_products_price', array($this, 'convert_total_value'));
        add_filter('wc_chip_supported_currencies', array($this, 'apply_base_currency'));
        add_filter('wc_chip_purchase_currency', array($this, 'apply_myr_currency'));
        add_action('woocommerce_settings_save_general', array($this, 'remove_transient'));
    }

    public function set_currency_provider()
    {
        if (defined('CHIP_WOO_CC_DEFINE_YOUR_OWN')){
          $this->provider = null;
        } else if (defined('CHIP_WOO_CC_PROVIDER') AND CHIP_WOO_CC_PROVIDER == 'oer' AND defined('CHIP_WOO_CC_OER_KEY')){
            require_once 'includes/OpenExchangeRate.php';
            $this->provider = ChipOpenExchangeRate::getInstance(CHIP_WOO_CC_OER_KEY);
        } else {
            require_once 'includes/BankNegaraMalaysia.php';
            $this->provider = ChipBNMAPI::getInstance();
        }
    }

    public function set_charge_percent()
    {
        if (defined('CHIP_WOO_CC_CHARGE_PERCENT')){
          $this->charge_percent = CHIP_WOO_CC_CHARGE_PERCENT / 100.0 + 1.0;
        } else {
          $this->charge_percent = 1;
        }
    }

    public function set_charge_fixed_cent()
    {
        if (defined('CHIP_WOO_CC_CHARGE_FIXED_CENT')){
          $this->charge_fixed_cent = CHIP_WOO_CC_CHARGE_FIXED_CENT;
        } else {
          $this->charge_fixed_cent = 0;
        }
    }

    public function convert_total_value($sub_total)
    {
        if (get_woocommerce_currency() == 'MYR'){
          return $sub_total;
        }
        $conversion_rate = $this->get_current_conversion();
        $total = $sub_total * $conversion_rate * $this->charge_percent + $this->charge_fixed_cent;
        return round($total);
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

    public function apply_myr_currency($currency)
    {
        return 'MYR';
    }

    public function get_current_conversion()
    {
        if (is_null($this->provider)){
            return CHIP_WOO_CC_DEFINE_YOUR_OWN;
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
      $this->provider->delete_transient();
    }

}

ChipWooConvertCurrency::getInstance();