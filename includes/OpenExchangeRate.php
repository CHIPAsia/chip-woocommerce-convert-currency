<?php

class ChipOpenExchangeRate
{

    private static $instance = null;

    public static function getInstance($oer_key)
    {
        if (self::$instance == null) {
            self::$instance = new ChipOpenExchangeRate($oer_key);
        }
 
        return self::$instance;
    }

    const URL = 'http://openexchangerates.org/api/latest.json';

    public function __construct($app_id)
    {
        $this->app_id = $app_id;
    }

    public function getRates($woocommerce_currency)
    {
        if (false === ($rates = get_transient('wc_chip_amount_amount_converter_oer'))) {
            $base = $woocommerce_currency;
            $query = http_build_query($this->getQueryParams($base));
            $rates = wp_remote_retrieve_body(wp_safe_remote_get(self::URL . "?{$query}"));
            $check_rates = json_decode($rates);

            // Check for error
            if (is_wp_error($rates) || !empty($check_rates->error) || empty($rates)) {
                // Do nothing
            } else {
                $transient_timeout = apply_filters( 'wc_chip_currency_provider_refresh_minutes', 30 );
                set_transient('wc_chip_amount_amount_converter_oer', $rates, MINUTE_IN_SECONDS * $transient_timeout);
            }
        }
        return $rates;
    }

    private function getQueryParams($base = "USD")
    {
        return array(
            'base' => $base,
            'symbols' => 'MYR',
            'app_id' => $this->app_id,
        );
    }

    public function delete_transient()
    {
        delete_transient('wc_chip_amount_amount_converter_oer');
    }
}