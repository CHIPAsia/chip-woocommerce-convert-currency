# CHIP WooCommerce Convert Currency

This plugin adds capability for CHIP for WooCommerce to convert total amount made in any currency to Malaysian Ringgit (MYR).

## Integrated Providers

Currently, the plugin has been integrated with Bank Negara Malaysia (BNM) and Open Exchange Rate (OER) to automate the currency conversion process. The default provider is BNM API. Please do write to us if you need to integrate with other providers.

- [Bank Negara Malaysia API](https://apikijangportal.bnm.gov.my/openapi)
- [Open Exchange Rate](https://openexchangerates.org)

However, you may opt to define your own conversion rate instead of using the automated currency exchange rate.

## Configuration

By default, the plugin will work as-is upon activation. However, you may tweak the configuration to fit your business needs. The configuration is available in **WooCommerce → Settings → General → CHIP Convert Currency API Options**.

All configuration is managed through the WooCommerce settings page.

### Configure your preferred provider

By default, the plugin is set to fetch the information from Bank Negara Malaysia API. However, you may change to your preferred providers if any.

| Setting | Description |
|---------|-------------|
| **API Options** | Choose between BNM (default), Open Exchange Rate API, or Fixed Rate. |
| **Open Exchange Rate API Key** | Required only if you select OER as the provider. |
| **Fixed Exchange Rate** | Enter your own rate. Used only when "Fixed Rate" is selected. |

### Configure additional charge for currency conversion

Since the conversion of currency will require conversion back to the merchant's home currency, you may specify an additional charge for the currency conversion. This is an important configuration since merchants who report in USD but receive in MYR will need to convert it back to USD.

**The charge calculations are added after conversion is done**.

| Setting | Description |
|---------|-------------|
| **Percentage Charge** | Percentage added after conversion (e.g., `5` = 5%). |
| **Fixed Charge (cent in MYR)** | Fixed amount in cents added after conversion. |

## Supported currencies

Kindly note that different providers do support different currencies. You need to check if the currencies that you are using are supported by the providers you choose. You are safe to ignore the list if you choose to define your own conversion rate.

### BNM Supported Currencies

```
JPY, AED, AUD, BND, CAD, CHF, CNY, EGP, EUR, GBP, HKD, IDR, INR, KHR, KRW, MMK, NPR, NZD, PHP, PKR, SAR, SGD, THB, TWD, USD, VND, SDR
```

### Open Exchange Rate Supported Currencies

```
AED, AFN, ALL, AMD, ANG, AOA, ARS, AUD, AWG, AZN, BAM, BBD, BDT, BGN, BHD, BIF, BMD, BND, BOB, BRL, BSD, BTC, BTN, BWP, BYN, BZD, CAD, CDF, CHF, CLF, CLP, CNH, CNY, COP, CRC, CUC, CUP, CVE, CZK, DJF, DKK, DOP, DZD, EGP, ERN, ETB, EUR, FJD, FKP, GBP, GEL, GGP, GHS, GIP, GMD, GNF, GTQ, GYD, HKD, HNL, HRK, HTG, HUF, IDR, ILS, IMP, INR, IQD, IRR, ISK, JEP, JMD, JOD, JPY, KES, KGS, KHR, KMF, KPW, KRW, KWD, KYD, KZT, LAK, LBP, LKR, LRD, LSL, LYD, MAD, MDL, MGA, MKD, MMK, MNT, MOP, MRU, MUR, MVR, MWK, MXN, MYR, MZN, NAD, NGN, NIO, NOK, NPR, NZD, OMR, PAB, PEN, PGK, PHP, PKR, PLN, PYG, QAR, RON, RSD, RUB, RWF, SAR, SBD, SCR, SDG, SEK, SGD, SHP, SLL, SOS, SRD, SSP, STD, STN, SVC, SYP, SZL, THB, TJS, TMT, TND, TOP, TRY, TTD, TWD, TZS, UAH, UGX, USD, UYU, UZS, VES, VND, VUV, WST, XAF, XAG, XAU, XCD, XDR, XOF, XPD, XPF, XPT, YER, ZAR, ZMW, ZWL
```

## Advanced Configuration

### Exchange rate cache

By default, the currency conversion rate is cached and will only be retrieved every 30 minutes. You may override this behavior by adding the filter **wc_chip_currency_provider_refresh_minutes**. Example code will change the interval to every hour.

```php
add_filter('wc_chip_currency_provider_refresh_minutes', function($minutes){ return 60 });
```

## Error Handling

If the plugin fails to retrieve the conversion rate from the API provider, the Checkout is expected to error, thus preventing the buyer from making payment. This is by design to ensure the order amount is not miscalculated and the buyer does not pay the wrong amount for an order.

## No Refund Support

**Important:** This plugin disables automatic refunds for all orders processed through CHIP gateways. Because the order total is converted from the original currency to MYR, refunding the original amount would result in an incorrect refund value. Refunds must be handled manually through your CHIP merchant dashboard.

## Full Disclaimer

This plugin is provided absolutely for free, without any warranty. You are expected to do your own due-diligence and use on production at your own risk. Refer to [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
