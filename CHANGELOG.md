# Changelog

## [2.1.0](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/compare/v2.0.1...v2.1.0) (2026-10-06)


### ⚠ BREAKING CHANGES

* the plugin folder, main file and text domain are now palasthotel-integration-for-prolitteris. Installs of 2.0.0 (folder integration-for-prolitteris) have to deactivate it and install this one; pixels, reports and settings are kept.

### Features

* get translations from translate.wordpress.org, translate the editor sidebar ([98495c0](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/98495c0bf23c2fc3ebbeefc2f6a211116825563a))
* rename to "Palasthotel Integration for ProLitteris" ([ab4ea39](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/ab4ea396486e665a5b82748d9984ef1cbaf5632d))


### Bug Fixes

* address the wordpress.org review (pixels only when enabled, admin notices, translations) ([c2b47c2](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/c2b47c29e5b08b5b15e818e13df889d2801f4abc))
* render and assign counting pixels only with the integration enabled ([fcdc4bd](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/fcdc4bd14ca105f3d6372b34d70b34984238c4b9))
* show the plugin's admin notices to administrators only, on the plugins screen and dashboard ([4789515](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/4789515f37152d8da5ec7c947cfba1c246999246))


### Miscellaneous

* release 2.1.0 ([e471a85](https://github.com/palasthotel/wp-palasthotel-integration-for-prolitteris/commit/e471a857334de56beceaa5f80b5bf6dee48de0c8))

## [2.0.1](https://github.com/palasthotel/wp-integration-for-prolitteris/compare/v2.0.0...v2.0.1) (2026-10-05)


### Bug Fixes

* declare Tested up to only in readme.txt ([d86c8b5](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/d86c8b51efaf15ae65441dce678af8173f37e246))
* declare Tested up to only in readme.txt ([bf68c6e](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/bf68c6e46bc1badd871975c1eb8a0e5bed060798))

## [2.0.0](https://github.com/palasthotel/wp-integration-for-prolitteris/compare/v1.6.4...v2.0.0) (2026-10-05)


### ⚠ BREAKING CHANGES

* PALASTHOTEL_COMPOSER_CENTRAL, the palasthotel/central_autoloader_loaded action and PH_CENTRAL_AUTOLOADER_DEBUG no longer have any effect. Install the plugin from wordpress.org (or wpackagist-plugin/integration-for-prolitteris) instead of from the Git repository.
* rename the constants in wp-config.php, the old names are no longer read and the integration stays off without them: PH_PRO_LITTERIS → PRO_LITTERIS_ENABLED, PH_PRO_LITTERIS_SYSTEM → PRO_LITTERIS_SYSTEM, PH_PRO_LITTERIS_CREDENTIALS → PRO_LITTERIS_CREDENTIALS. PRO_LITTERIS_AUTO_MESSAGES is unchanged.
* new plugin folder, main file and text domain. Deactivate "ProLitteris" (pro-litteris) before activating "Integration for ProLitteris". Namespace, classes, hooks, REST fields, options, meta and tables are unchanged, so data and project code keep working.

### Features

* add bootstrap to check for central autoloader ([c342d70](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/c342d7093d5ae3a578e225e221b54bd6b4c470d0))
* introduce ph-central-autoloader-debug constant ([4ae8792](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/4ae8792e17a55a8b7cfbe4034dec8a56e2ee8384))
* name all wp-config.php constants PRO_LITTERIS_* ([2d6ec71](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/2d6ec71e92b2b5ab2fe63cb645410a6678e48148))
* ship as "Integration for ProLitteris" for wordpress.org ([850270c](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/850270cdcbe80e8aaa01c55d67dcf096916b0d79))


### Bug Fixes

* bug where it couldn't find the autoloader ([e36c8da](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/e36c8daf8ddcde70acd016568a710f7054f19210))
* declare the properties of Pixel ([ec8d070](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/ec8d0707cc82cb8e2fd0598251882b77091c3a3c))
* error counting when reporting pixel ([67ddaf5](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/67ddaf575e7c7dfd9a5399295191c0a604b4df76))
* escape and translate the admin output, keep names with apostrophes ([7841d8e](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/7841d8e1d8789589dc3ef385a31981da240db512))
* escape the pixel url, error titles and attachment author options ([7f8e527](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/7f8e527be6447a36f499349a09efb30b633f3268))
* expose only the pixel of the pro_litteris REST field to visitors ([88d6ff4](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/88d6ff43a319402bb384eaad443d0b951e872b87))
* limit the ProLitteris dashboard widget to editors, add a nonce ([660b6c7](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/660b6c7764dd6a18afc870bd25fc7225d0c2aa8c))
* load the editor sidebar from @wordpress/editor, update the WordPress packages ([542ff00](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/542ff00d666aaf937012fbce0d0af7466381e00e))
* remove dynamic property declaration ([8fe06f2](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/8fe06f299e7644d1d6a5020b26e786203494ea11))
* verify the TLS certificate of the ProLitteris API ([6b6c0fb](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/6b6c0fbe0c2cb1ca00d5e2eeb536de30d635ad17))


### Code Refactoring

* drop the support for the Palasthotel central autoloader ([c86488e](https://github.com/palasthotel/wp-integration-for-prolitteris/commit/c86488ed04c0728c3e6a64b7781a05e116e7dbc6))

## Changelog
