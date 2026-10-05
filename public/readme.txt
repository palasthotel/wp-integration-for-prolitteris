=== Integration for ProLitteris ===
Contributors: palasthotel, edwardbock, janaeggebrecht
Donate link: https://palasthotel.de/
Tags: prolitteris, copyright, remuneration, switzerland, counting pixel
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.6.4
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Counting pixels and automated text reports for "Onlinewerke entschädigen" of ProLitteris, the Swiss collective rights management organization.

== Description ==

ProLitteris remunerates authors and publishers in Switzerland and Liechtenstein for texts published online ("Onlinewerke entschädigen", also called "Verteilung Online"). Views are counted with a counting pixel on each text, and the publisher reports the texts to ProLitteris.

This plugin does both from within WordPress:

* fetches counting pixels from ProLitteris and keeps a pool of unassigned ones
* assigns a pixel to each post and renders it on the page, in the paywall variant if you tell it a post is behind a paywall
* builds the report from the post: title, plain text, authors and image originators with their ProLitteris member numbers
* reports posts from the block editor sidebar, or automatically every hour
* shows in the posts list which posts are reported, ready to report or too short
* adds the ProLitteris member number and name to user profiles, and an image originator to media files

You need a contract with ProLitteris for "Onlinewerke entschädigen" and the API credentials of your ProLitteris account. This plugin is not affiliated with or endorsed by ProLitteris.

= Developers =

Filters and actions, for example to enable more post types, add co-authors or prevent pixels on certain posts, are documented in the [repository on GitHub](https://github.com/palasthotel/wp-integration-for-pro-litteris).

== External services ==

This plugin connects to the web service of ProLitteris ("OWEN", `https://owen.prolitteris.ch`), which is needed to take part in "Onlinewerke entschädigen". Nothing is sent before the integration is enabled and the credentials are entered.

* **Fetching counting pixels:** every hour, and when an editor refills the pool from the dashboard, the plugin requests as many new counting pixels as are missing from the pool. The request carries your ProLitteris credentials.
* **Reporting a text:** when a post is reported, from the block editor or automatically, the plugin sends its title, its plain text, its counting pixel and, for every author and image originator, the ProLitteris member number, first name, last name and WordPress user id.
* **Counting views:** every post with a counting pixel embeds an image loaded from a ProLitteris server (`*.owen.prolitteris.ch`). The visitor's browser loads it, transmitting the visitor's IP address, the page address and browser information. ProLitteris has the views counted by Kantar GmbH using the Scalable Central Measurement Method (SZM).

The service is provided by ProLitteris, Swiss Copyright Society for Literature and Visual Art, Zurich: [terms of use](https://oms.prolitteris.ch/XX0/TermsOfUse), [privacy policy](https://prolitteris.ch/datenschutzerklaerung/). The plugin suggests a paragraph for your own privacy policy under Settings → Privacy.

== Installation ==

1. Install the plugin through the "Plugins" screen in WordPress, or upload the `integration-for-pro-litteris` folder to `/wp-content/plugins/`.
1. Activate it.
1. Go to Settings → ProLitteris, enable the integration and enter your member number, username and password.
1. Enter the ProLitteris member number and name of each author on their user profile.

The connection can also be configured in `wp-config.php`, which takes precedence over the settings page:

`define( 'PH_PRO_LITTERIS', true );`
`define( 'PH_PRO_LITTERIS_SYSTEM', 'https://owen.prolitteris.ch' );`
`define( 'PH_PRO_LITTERIS_CREDENTIALS', 'member number:username:password' );`
`define( 'PRO_LITTERIS_AUTO_MESSAGES', true );`

== Frequently Asked Questions ==

= Which posts get a pixel? =

Posts of the post type `post`. More post types can be added with the `pro_litteris_post_types` filter.

= Why can a post not be reported? =

ProLitteris only remunerates texts with at least 1500 characters, and every author needs a ProLitteris member number and a first and last name on their profile. Hover the icon in the ProLitteris column of the posts list to see the reason.

= What happened to the plugin "ProLitteris" (pro-litteris)? =

It is this plugin. Up to version 1.6.4 it was only available on GitHub, installed in the folder `pro-litteris`. Version 2.0.0 is the first release on wordpress.org, with a new folder and text domain: deactivate the old plugin before activating this one. Pixels, reports and settings are kept, they are stored in the database.

== Changelog ==

= 1.6.4 =
* Fix: Remove dynamic property declaration

= 1.6.2 =
* Feature: Add support for Palasthotel central autoloader

= 1.6.1 =
* Update: Rework file structure

= 1.6.0 =
* Feature: filter for paywall pixel variant

= 1.5.0 =
* Feature: Post reporting updatable

= 1.4.0 =
* Feature: Always add pixels, but limit reporting if conditions are not met

= 1.3.2 =
* Bugfix: Ignore invalid user id as participants
* Update: packages

= 1.3.1 =
* Images from gallery blocks with innerBlock images are detected
* Dependency updates

= 1.3.0 =
* Update: Default min chars count for pixels is now 1500 instead of 2000
* Feature: Two new action before and after the message text body is generated

= 1.2.6 =
* Bugfix: User meta won't be saved
* Performance issue fix

= 1.2.5 =
* Optimization: Push message error logging

= 1.2.4 =
* Optimization: place referrer meta tag in header
* Bugfix: database year -1 breaks query

= 1.2.3 =
* Feature: Add image authorship to ProLitteris reporting.

= 1.2.2 =
* Bugfix: Gutenberg script was enqueued even if no rest endpoint for post type existed

= 1.2.1 =
* Feature: Migrate support

= 1.2.0 =
* Gutenberg: Moved meta box into Gutenberg sidebar
* Performance: Moved data to custom tables

= 1.1.1 =
* First release

== Upgrade Notice ==

= 2.0.0 =
New folder and text domain: deactivate the old plugin "ProLitteris" (folder pro-litteris) before activating this one. Fixes several security issues, see the changelog.
