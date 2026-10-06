<?php

/**
 * Plugin Name: Integration for ProLitteris
 * Plugin URI: https://github.com/palasthotel/wp-integration-for-prolitteris
 * Description: Adds the ProLitteris tracking pixel to posts and reports texts to ProLitteris ("Onlinewerke entschädigen").
 * Version: 2.0.1
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Author: Palasthotel <webmaster@palasthotel.de>
 * Author URI: https://palasthotel.de
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: integration-for-prolitteris
 */

namespace Palasthotel\ProLitteris;

// If this file is called directly, abort.

if (! defined('ABSPATH')) {
    die;
}

if (legacy_plugin_is_active() || ! load_dependencies()) {
    return;
}

/**
 * The plugin was called "ProLitteris" (pro-litteris/Plugin.php) up to 1.6.4 and declares
 * the same classes. With both active, this one stays out of the way: it loads first
 * (alphabetically), so checking for the class alone would let the old one crash.
 */
function legacy_plugin_is_active(): bool {
    $legacy = 'pro-litteris/Plugin.php';
    if (
        ! class_exists(__NAMESPACE__ . '\\Plugin', false)
        && ! in_array($legacy, (array) get_option('active_plugins', []), true)
        && ! (is_multisite() && isset(get_site_option('active_sitewide_plugins', [])[$legacy]))
    ) {
        return false;
    }
    admin_notice(fn() => __('Integration for ProLitteris is not loaded because the old plugin "ProLitteris" is still active. Deactivate "ProLitteris" - pixels, reports and settings are kept.', 'integration-for-prolitteris'));

    return true;
}

/**
 * An error notice for administrators, on the plugins screen and the dashboard only.
 * $message returns the text, so it is translated when the notice is shown - this
 * file runs before init, too early for translations.
 */
function admin_notice(callable $message): void {
    add_action('admin_notices', function () use ($message) {
        $screen = get_current_screen();
        if (! current_user_can('activate_plugins') || ! $screen || ! in_array($screen->id, ['plugins', 'plugins-network', 'dashboard', 'dashboard-network'], true)) {
            return;
        }
        echo '<div class="notice notice-error"><p>' . esc_html($message()) . '</p></div>';
    });
}

/**
 * Loads the bundled composer dependencies (html2text).
 */
function load_dependencies(): bool {
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (is_readable($autoload)) {
        require_once $autoload;
        return true;
    }

    admin_notice(fn() => __('Integration for ProLitteris: dependencies are missing, run "composer install" in the plugin folder.', 'integration-for-prolitteris'));

    return false;
}

class Plugin extends Components\Plugin {

    public PostsTable $postList;
    public Post $post;
    public User $user;
    public TrackingPixel $pixel;
    public Database $database;
    public Repository $repository;
    public API $api;
    public Schedule $schedule;
    public DashboardWidget $dashboardWidget;
    public Settings $settings;
    public WP_REST $rest;
    public Assets $assets;
    public Gutenberg $gutenberg;
    public Migrate $migrate;
    public Media $media;

	/**
	 * Domain for translation
	 */
	const DOMAIN = "integration-for-prolitteris";

	/**
	 * ids
	 */
	const DASHBOARD_WIDGET_ID = "pro_litteris_dashboard";

	/**
	 * handles
	 */
	const HANDLE_GUTENBERG_JS = "pro_litteris_gutenberg_script";
	const HANDLE_GUTENBERG_CSS = "pro_litteris_gutenberg_style";

	/**
	 * Schedules
	 */
	const SCHEDULE_REFILL_PIXEL_POOL = "pro_litteris_schedule_refill_pixel_pool";

	/**
	 * actions
	 */
	const ACTION_BEFORE_MESSAGE_CONTENT = "pro_litteris_before_message_content";
	const ACTION_AFTER_MESSAGE_CONTENT = "pro_litteris_after_message_content";

	/**
	 * filters
	 */
	const FILTER_PREVENT_PIXEL_ASSIGN = "pro_litteris_prevent_pixel_assign";
	const FILTER_POST_MESSAGE_CONTENT = "pro_litteris_post_message_content";
	const FILTER_POST_AUTHORS = "pro_litteris_post_authors";
	const FILTER_POST_TYPES = "pro_litteris_post_types";
	const FILTER_RENDER_PIXEL = "pro_litteris_render_pixel";
	const FILTER_POST_HAS_PAYWALL = "pro_litteris_post_has_paywall";

	/**
	 * Options
	 */
	const OPTION_PIXEL_POOL_SIZE = "_pro_litteris_pixel_pool_size";
	const OPTION_MIN_CHAR_COUNT = "_pro_litteris_min_char_count";

	/**
	 * User meta fields
	 */
	const USER_META_PRO_LITTERIS_ID = "_pro_litteris_id";
	const USER_META_PRO_LITTERIS_NAME = "_pro_litteris_name";
	const USER_META_PRO_LITTERIS_SURNAME = "_pro_litteris_surname";

	/**
	 * error codes
	 */
	const ERROR_CODE_CONFIG = 'pro-litteris-config-error';
	const ERROR_CODE_REQUEST = 'pro-litteris-request-error';
	const ERROR_CODE_RESPONSE = 'pro-litteris-response-error';
	const ERROR_CODE_ASSIGN_PIXEL = 'pro-litteris-assigned-pixel';
	const ERROR_CODE_PUSH_MESSAGE = 'pro-litteris-push-message';

	const POST_META_PUSH_MESSAGE_ERROR = "_pro-litteris-push-message-error";
	const POST_META_PUSH_MESSAGE_ERROR_DATA = "_pro-litteris-push-message-error-data";
	const ATTACHMENT_META_AUTHOR = "pro_litteris_attachment_author";

	/**
	 * rest fields
	 */
	const REST_FIELD = "pro_litteris";
	const REST_FIELD_ATTACHMENT_AUTHOR = "pro_litteris_author";

	/**
	 * Plugin constructor
	 */
	public function onCreate() {

		/**
		 * load translations
		 */

		// ----------------------------------------
		// all about data
		// ----------------------------------------
		$this->database   = new Database();
		$this->api        = new API();
		$this->repository = new Repository( $this );
		$this->rest       = new WP_REST( $this );
		$this->assets     = new Assets( $this );
		$this->migrate    = new Migrate( $this );

		// ----------------------------------------
		// tasks
		// ----------------------------------------
		$this->schedule = new Schedule( $this );

		// ----------------------------------------
		// user interaction
		// ----------------------------------------
		$this->dashboardWidget = new DashboardWidget( $this );
		$this->settings        = new Settings( $this );
		$this->gutenberg       = new Gutenberg( $this );
		$this->post            = new Post( $this );
		$this->postList        = new PostsTable( $this );
		$this->user            = new User( $this );
		$this->media           = new Media( $this );
		$this->pixel           = new TrackingPixel( $this );

		if ( WP_DEBUG ) {
			$this->database->createTables();
		}
	}

	public function isEnabled() {
		return Config::isEnabled();
	}

	public function hasConfig() {
		return Config::hasConnection();
	}

	/**
	 * on plugin activation
	 */
	function onSiteActivation() {
		$this->database->createTables();
	}

}

Plugin::instance();

require_once dirname( __FILE__ ) . "/cli/wp-cli.php";
