<?php

class ChipBNMAPI
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ChipBNMAPI();
        }
 
        return self::$instance;
    }
  
    const URL = 'https://api.bnm.gov.my/public/exchange-rate';

    public function getRates($woocommerce_currency)
    {
        // BNM don't accept User-Agent. Thus, it must be set to null.
        $header = array(
            'Accept' => 'application/vnd.BNM.API.v1+json',
            'User-Agent' => null,
        );

        if (false === ($json_rates = get_transient('wc_chip_amount_amount_converter_bnm'))) {
            $base = $woocommerce_currency;
            $query = http_build_query($this->getQueryParams());
            $rates = wp_remote_retrieve_body(wp_safe_remote_get(self::URL . "?{$query}", array(
                'headers' => $header,
            )));

            $array_return = json_decode($rates, true);

            foreach ($array_return['data'] as $value) {
                if (in_array($base, $value)) {
                    $display = $value;
                    break;
                }
            }

            $check_rates = array(
                'base' => $display['currency_code'],
                'rates' => array(
                    'MYR' => $display['rate']['selling_rate'] / $display['unit'],
                ),
            );

            $json_rates = json_encode($check_rates);

            // Check for error
            if (is_wp_error($rates) || empty($rates)) {
                // Do nothing
            } else {
                $transient_timeout = apply_filters( 'wc_chip_currency_provider_refresh_minutes', 30 );
                set_transient('wc_chip_amount_amount_converter_bnm', $json_rates, MINUTE_IN_SECONDS * $transient_timeout);
            }
        }
        return $json_rates;
    }

    private function getQueryParams()
    {
        return array(
            'quote' => "RM",
        );
    }

    public function delete_transient()
    {
        delete_transient('wc_chip_amount_amount_converter_bnm');
    }
}