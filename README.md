# CHIP WooCommerce Convert Currency

This plugins add capability for CHIP for WooCommerce to convert total amount made in any currency to Malaysian Ringgit (MYR).

## Integrated Providers

Currently, the plugin have been integrated with Bank Negara Malaysia (BNM) and Open Exchange Rate (OER) to automate the currency conversion process. The default provider is BNM API. Please do write to us if you need to integrate with other providers.

- [Bank Negara Malaysia API](https://apikijangportal.bnm.gov.my/openapi)
- [Open Exchange Rate](http://openexchangerates.org)

However, you may opt to define your own conversion rate instead of using the automated currency exchange rate

## Configuration

By default, the plugin will work as-is upon activation. However, you may tweak the configuration to fit your business needs. The configuration is made using PHP define where you can store the value in **wp-config.php** file. Alternatively, you may configure it on chip-woo-convert-currency.php.

### Configure your own exchange rate

You may use your own exchange rate instead of automated from API providers. By setting this option, the plugin will not fetch exchange rate from API providers.

```php
define('CHIP_WOO_CC_DEFINE_YOUR_OWN', 3.32);
```

### Configure your preferred providers

By default, the plugin is set to fetch the information from Bank Negara Malaysia API. However, you may change to your preferred providers if any.

```php
define('CHIP_WOO_CC_PROVIDER', 'bnm'); // possible values: bnm, oer
```

Note: if you are using Open Exchange Rate, you need to set the key for the exchange to work

```php
define('CHIP_WOO_CC_OER_KEY', '<open-exchange-rate-key>');
```

### Configure additional charge for currency conversion

Since the conversion of currency will require conversion back to merchant home currency, you may specifiy additional charge for the currency conversion. This is an important configuration since merchant who are reporting in USD, receiving in MYR will required to convert it back to USD.

**The charge calculation are added after conversion is done**.

#### Add percentage charge

```php
define('CHIP_WOO_CC_CHARGE_PERCENT', 0); // 0 Percent
```

#### Add fixed charge

```php
define('CHIP_WOO_CC_CHARGE_FIXED_CENT', 0); // 0 Cent
```

## Supported currencies

Kindly note that different providers do support different currencies. You need to check if the currencies that you are using is supported by the providers you choose. You are safe to ignore the list if you choose to define your own conversion rate.

### BNM Supported Currencies

```
JPY, AED, AUD, BND, CAD, CHF, CNY, EGP, EUR, GBP, HKD, IDR, INR, KHR, KRW, MMK, NPR, NZD, PHP, PKR, SAR, SGD, THB, TWD, USD, VND, SDR
```

### Open Exchange Rate Supported Currencies

```
AED, AFN, ALL, AMD, ANG, AOA, ARS, AUD, AWG, AZN, BAM, BBD, BDT, BGN, BHD, BIF, BMD, BND, BOB, BRL, BSD, BTC, BTN, BWP, BYN, BZD, CAD, CDF, CHF, CLF, CLP, CNH, CNY, COP, CRC, CUC, CUP, CVE, CZK, DJF, DKK, DOP, DZD, EGP, ERN, ETB, EUR, FJD, FKP, GBP, GEL, GGP, GHS, GIP, GMD, GNF, GTQ, GYD, HKD, HNL, HRK, HTG, HUF, IDR, ILS, IMP, INR, IQD, IRR, ISK, JEP, JMD, JOD, JPY, KES, KGS, KHR, KMF, KPW, KRW, KWD, KYD, KZT, LAK, LBP, LKR, LRD, LSL, LYD, MAD, MDL, MGA, MKD, MMK, MNT, MOP, MRU, MUR, MVR, MWK, MXN, MYR, MZN, NAD, NGN, NIO, NOK, NPR, NZD, OMR, PAB, PEN, PGK, PHP, PKR, PLN, PYG, QAR, RON, RSD, RUB, RWF, SAR, SBD, SCR, SDG, SEK, SGD, SHP, SLL, SOS, SRD, SSP, STD, STN, SVC, SYP, SZL, THB, TJS, TMT, TND, TOP, TRY, TTD, TWD, TZS, UAH, UGX, USD, UYU, UZS, VES, VND, VUV, WST, XAF, XAG, XAU, XCD, XDR, XOF, XPD, XPF, XPT, YER, ZAR, ZMW, ZWL
```

## Advance Configuration

### Exchange rate cache

By default, the currency conversion rate is cached and will be only retrieved in every 30 minutes. You may override this behavior by adding filter **wc_chip_currency_provider_refresh_minutes**. Example code will make the interval changed to every hour.

```php
add_filter('wc_chip_currency_provider_refresh_minutes', function($minutes){ return 60 });
```

## Error Handling

In the event of the plugin failed to retrieve the conversion rate from API provider, it is expected for the Checkout to error and thus preventing the buyer from making payment. This is by-design to ensure the order amount is didn't wrongly calculated and the buyer didn't pay wrong amount for an order.

## Full Disclaimer

This plugin is provided absolutely for free, without any warranty. You are expected to do your own due-diligence and use on production at your own risk. Refer to [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
