# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that converts unsupported currencies to Malaysian Ringgit (MYR) for the CHIP for WooCommerce payment gateway. It integrates with Bank Negara Malaysia (BNM) API (default) and Open Exchange Rate (OER) to fetch live exchange rates, with an optional fixed-rate fallback.

## Architecture

### Plugin Lifecycle

The entry point is `chip-woo-convert-currency.php`, which instantiates the `ChipWooConvertCurrency` singleton. The class:

1. **Defines constants** (`CHIP_WCC_MODULE_VERSION`, `CHIP_WCC_FILE`, etc.)
2. **Includes admin settings** (`includes/admin/currency-settings.php`) only when `is_admin()`
3. **Registers a script** (`assets/js/admin/currency-settings.js`) for the settings UI
4. **Sets up exchange rate provider** based on the `chip_wcc_options` option (`bnm`, `oer`, or `fixedrate`)
5. **Attaches repetitive hooks** across six CHIP gateway instances (`wc_gateway_chip` through `wc_gateway_chip_6`)
6. **Adds a Settings link** on the plugins page via `plugin_action_links_`

### Exchange Rate Providers

The provider is selected at runtime via `get_option('chip_wcc_options')`:

- **`bnm`** (default): `ChipBNMAPI` in `includes/BankNegaraMalaysia.php`. Fetches from `https://api.bnm.gov.my/public/exchange-rate`. Requires a null `User-Agent` header and `Accept: application/vnd.BNM.API.v1+json`.
- **`oer`**: `ChipOpenExchangeRate` in `includes/OpenExchangeRate.php`. Fetches from `https://openexchangerates.org/api/latest.json`. Requires `get_option('wcc_oer_key')`.
- **`fixedrate`**: No provider instance; `get_current_conversion()` returns `get_option('wcc_fixed_rate')` directly.

All providers cache rates using WordPress transients (`wc_chip_amount_converter_bnm` / `wc_chip_amount_converter_oer`) with a default TTL of 30 minutes. The TTL is filterable via `wc_chip_currency_provider_refresh_minutes`.

### Currency Conversion Flow

Conversion happens in `purchase_parameter()`:

1. If the purchase currency is already `MYR`, return unchanged.
2. Fetch the current conversion rate via `get_current_conversion()`.
3. Multiply every product price and `total_override` by the rate, then apply:
   - Percentage charge: `charge_percent` (e.g., 1.05 for 5%)
   - Fixed charge: `charge_fixed_cent` (added in cents after conversion)
4. Round the result and set currency to `MYR`.

The plugin also declares supported currencies per provider via `apply_base_currency()` and forces the purchase currency to `MYR` via `apply_myr_currency()`.

### Admin Settings UI

`CurrencySettings` (in `includes/admin/currency-settings.php`) injects fields into **WooCommerce → Settings → General** via the `woocommerce_general_settings` filter. Settings include:

- API provider dropdown (`chip_wcc_options`)
- Open Exchange Rate API key (`wcc_oer_key`)
- Fixed exchange rate (`wcc_fixed_rate`)
- Percentage charge (`wcc_percentage_rate`)
- Fixed charge in cents (`wcc_fixed_charge`)

The JavaScript in `assets/js/admin/currency-settings.js` shows/hides the OER key and fixed-rate fields based on the selected provider.

### HPOS Compatibility

The plugin declares compatibility with WooCommerce High-Performance Order Storage (HPOS) in the `before_woocommerce_init` action.

### No Refund Support

`can_refund_order()` always returns `false` because the converted amounts make automatic refunds unsafe. Refunds must be handled manually through the CHIP merchant dashboard.

## Common Commands

### Run PHPUnit tests locally

```bash
composer install
vendor/bin/phpunit
```

Tests are in `tests/` with a bootstrap stubbing WordPress functions.

### Run PHPCS locally

```bash
composer install
vendor/bin/phpcs --standard=phpcs.xml .
```

The codebase follows WordPress Coding Standards (WPCS). There are a handful of file-naming convention warnings that are intentionally left as-is to preserve backward compatibility.

### Build a release zip locally

```bash
git archive --format=zip --output=chip-woo-convert-currency.zip HEAD
```

`.gitattributes` excludes development files (`.github/`, `README.md`, `composer.json`, `tests/`, `scripts/`, etc.) from the archive automatically.

### Test plugin activation (GitHub Actions)

CI runs on pushes and PRs to `main`:

- `.github/workflows/plugin-activation.yml` — Spins up WordPress + MariaDB, installs CHIP for WooCommerce via WP-CLI, and activates this plugin.
- `.github/workflows/build-zip.yml` — Builds a zip artifact via `git archive`.
- `.github/workflows/plugin-check.yml` — PHP 7.4–8.4 compatibility, PHPCS (WordPress Standards), PHPUnit tests, and WordPress Plugin Check.
- `.github/workflows/pr-summary.yml` — Auto-generates PR descriptions using AI.
- `.github/workflows/release-zip.yml` — Auto-creates GitHub release + uploads zip when a `v*.*.*` tag is pushed.
- `.github/workflows/prepare-release.yml` — Manual workflow dispatch to bump version, generate changelog, and open a release PR.

## Important Notes

- **Version consistency**: The plugin version appears in four places — the file header in `chip-woo-convert-currency.php`, `readme.txt` (`Stable tag:`), `changelog.txt`, and the `CHIP_WCC_MODULE_VERSION` constant. Keep them in sync when bumping versions. Use `scripts/bump-version.sh` to automate this.
- **Error handling**: If rate fetching fails, `get_current_conversion()` throws an exception. This is intentional — it prevents buyers from paying an incorrect amount.
- **Line endings**: `.gitattributes` enforces LF for `.php`, `.js`, `.css`, `.txt`, and `.md` files.
- **Coding standards**: The codebase follows WordPress Coding Standards. When editing, use tabs for indentation, place opening braces on the same line, and use Yoda conditions.
