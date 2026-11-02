# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.4] - 2026-11-02

### Composer

- Changed `automattic/jetpack-autoloader` from `^3.0 || ^4.0 || ^5.0` to `^3.0 || ^4.0 || ^5.0 || ^6.0` to allow Jetpack Autoloader 6.x. Jetpack Autoloader 6.0.0 only raises its minimum PHP version to 7.4. The lock file keeps `v5.0.7`. Release notes: https://github.com/Automattic/jetpack-autoloader/blob/v6.0.0/CHANGELOG.md

Full set of changes: [`1.0.3...1.0.4`][1.0.4]

[1.0.4]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/compare/v1.0.3...v1.0.4

## [1.0.3] - 2025-06-19

### Commits

- Allow Jetpack autoloader 3, 4 and 5. ([b2c0bad](https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/commit/b2c0badc645a2c2b8bb51fdb5cc8896228a1e912))

### Composer

- Changed `automattic/jetpack-autoloader` from `v3.0.2` to `v5.0.7`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v5.0.7
- Changed `pronamic/pronamic-wp-updater` from `v1.0.1` to `v1.0.3`.
	Release notes: https://github.com/pronamic/pronamic-wp-updater/releases/tag/v1.0.3
- Changed `pronamic/wp-number` from `v1.3.0` to `v1.3.3`.
	Release notes: https://github.com/pronamic/wp-number/releases/tag/v1.3.3

Full set of changes: [`1.0.2...1.0.3`][1.0.3]

[1.0.3]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/compare/v1.0.2...v1.0.3

## [1.0.2] - 2024-03-14

### Composer

- Changed `automattic/jetpack-autoloader` from `v2.12.0` to `v3.0.2`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.0.2
- Changed `pronamic/pronamic-wp-updater` from `v1.0.0` to `v1.0.1`.
	Release notes: https://github.com/pronamic/pronamic-wp-updater/releases/tag/v1.0.1

Full set of changes: [`1.0.1...1.0.2`][1.0.2]

[1.0.2]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/compare/v1.0.1...v1.0.2

## [1.0.1] - 2023-11-06

### Changed

- WC tested up to: 8.3 ([43d9257](https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/commit/43d925792bec33f439a4ae13916261f4fa2131bd))
- HPOS compatibility. ([516b779](https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/commit/516b7796bdd1cd8a6785807a61a9a1140bcbbd0b))
- Added `if ( ! defined( 'ABSPATH' ) )` check. ([be9fda4](https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/commit/be9fda4f07cc9fdba12bc3dcf3e79ec7f6ebb2fe))

Full set of changes: [`1.0.0...1.0.1`][1.0.1]

[1.0.1]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/compare/v1.0.0...v1.0.1

## [1.0.0] - 2023-06-30

- First release.

[unreleased]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/compare/v1.0.4...HEAD
[1.0.0]: https://github.com/pronamic/pronamic-payment-gateways-fees-for-woocommerce/releases/tag/v1.0.0
