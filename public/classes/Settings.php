<?php

namespace Palasthotel\ProLitteris;

/**
 * Settings → ProLitteris. A setting defined as constant in wp-config.php is shown
 * read-only, see Config.
 */
class Settings extends _Component {

	const PAGE = "palasthotel-integration-for-prolitteris";
	const GROUP = "palasthotel_integration_for_prolitteris";
	const CAPABILITY = "manage_options";

	public function onCreate() {
		parent::onCreate();
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_init', [ $this, 'register' ] );
		add_action( 'admin_init', [ $this, 'privacyPolicy' ] );
		add_filter( 'plugin_action_links_' . $this->plugin->basename, [ $this, 'actionLinks' ] );
	}

	public function menu() {
		add_options_page(
			__( 'ProLitteris', 'palasthotel-integration-for-prolitteris' ),
			__( 'ProLitteris', 'palasthotel-integration-for-prolitteris' ),
			self::CAPABILITY,
			self::PAGE,
			[ $this, 'render' ]
		);
	}

	public function actionLinks( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'palasthotel-integration-for-prolitteris' ) . '</a>' );

		return $links;
	}

	public function register() {
		register_setting( self::GROUP, Config::OPTION_ENABLED, [
			'type'              => 'boolean',
			'sanitize_callback' => fn( $value ) => ! empty( $value ),
			'default'           => false,
		] );
		register_setting( self::GROUP, Config::OPTION_SYSTEM, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitizeSystem' ],
			'default'           => Config::DEFAULT_SYSTEM,
		] );
		register_setting( self::GROUP, Config::OPTION_MEMBER_ID, [
			'type'              => 'string',
			'sanitize_callback' => fn( $value ) => preg_replace( '/\D/', '', (string) $value ),
			'default'           => '',
		] );
		register_setting( self::GROUP, Config::OPTION_USERNAME, [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		] );
		register_setting( self::GROUP, Config::OPTION_PASSWORD, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitizePassword' ],
			'default'           => '',
		] );
		register_setting( self::GROUP, Config::OPTION_AUTO_MESSAGES, [
			'type'              => 'boolean',
			'sanitize_callback' => fn( $value ) => ! empty( $value ),
			'default'           => false,
		] );
		register_setting( self::GROUP, Plugin::OPTION_MIN_CHAR_COUNT, [
			'type'              => 'integer',
			'sanitize_callback' => fn( $value ) => max( 0, intval( $value ) ),
			'default'           => 1500,
		] );
		register_setting( self::GROUP, Plugin::OPTION_PIXEL_POOL_SIZE, [
			'type'              => 'integer',
			'sanitize_callback' => fn( $value ) => min( 100, max( 0, intval( $value ) ) ),
			'default'           => 20,
		] );
	}

	/**
	 * Only https: the requests carry the credentials.
	 */
	public function sanitizeSystem( $value ) {
		$value = esc_url_raw( trim( (string) $value ), [ 'https' ] );
		if ( '' === $value ) {
			add_settings_error( self::GROUP, 'system', __( 'The API URL has to start with https://.', 'palasthotel-integration-for-prolitteris' ) );

			return get_option( Config::OPTION_SYSTEM, Config::DEFAULT_SYSTEM );
		}

		return untrailingslashit( $value );
	}

	/**
	 * The password is never sent back to the browser: an empty field keeps the stored one.
	 */
	public function sanitizePassword( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';

		return '' === $value ? (string) get_option( Config::OPTION_PASSWORD, '' ) : $value;
	}

	public function privacyPolicy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		wp_add_privacy_policy_content(
			__( 'Palasthotel Integration for ProLitteris', 'palasthotel-integration-for-prolitteris' ),
			wp_kses_post( wpautop( __(
				"Our articles contain a counting pixel of ProLitteris, Zurich, the Swiss collective rights management organization. It counts the views of a text so that its authors and publishers are remunerated under the Swiss Copyright Act (Art. 19 para. 1 and Art. 20). The measurement is carried out by Kantar GmbH using the Scalable Central Measurement Method (SZM). To recognize a browser it uses a session cookie or a signature built from information the browser transmits automatically; IP addresses are processed in anonymized form only. No individual user is identified.\n\nTo report a text, we transmit its title, its plain text and the names and ProLitteris member numbers of its authors and image originators to ProLitteris.",
				'palasthotel-integration-for-prolitteris'
			) ) )
		);
	}

	private function lockedNote( bool $locked ) {
		if ( $locked ) {
			echo '<p class="description">' . esc_html__( 'Set in wp-config.php, the setting here has no effect.', 'palasthotel-integration-for-prolitteris' ) . '</p>';
		}
	}

	public function render() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		$hasPassword = '' !== (string) get_option( Config::OPTION_PASSWORD, '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ProLitteris', 'palasthotel-integration-for-prolitteris' ); ?></h1>
			<p>
				<?php esc_html_e( 'Connects this website with "Onlinewerke entschädigen" of ProLitteris: posts get a counting pixel, and their texts are reported to ProLitteris. You need a contract with ProLitteris and the API credentials they provide.', 'palasthotel-integration-for-prolitteris' ); ?>
			</p>
			<?php if ( Config::isEnabled() && ! Config::hasConnection() ) : ?>
				<div class="notice notice-warning inline"><p>
					<?php esc_html_e( 'The integration is enabled, but credentials are missing: no pixels are fetched and nothing is reported.', 'palasthotel-integration-for-prolitteris' ); ?>
				</p></div>
			<?php endif; ?>
			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enabled', 'palasthotel-integration-for-prolitteris' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Config::OPTION_ENABLED ); ?>" value="1"
									<?php checked( Config::isEnabled() ); disabled( Config::isEnabledByConstant() ); ?> />
								<?php esc_html_e( 'Use the ProLitteris integration', 'palasthotel-integration-for-prolitteris' ); ?>
							</label>
							<?php $this->lockedNote( Config::isEnabledByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-system"><?php esc_html_e( 'API URL', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="url" id="pl-system" class="regular-text" name="<?php echo esc_attr( Config::OPTION_SYSTEM ); ?>"
								value="<?php echo esc_attr( Config::system() ); ?>" <?php disabled( Config::isSystemByConstant() ); ?> />
							<?php $this->lockedNote( Config::isSystemByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-member"><?php esc_html_e( 'Member number', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="text" id="pl-member" class="regular-text" inputmode="numeric" autocomplete="off"
								name="<?php echo esc_attr( Config::OPTION_MEMBER_ID ); ?>"
								value="<?php echo esc_attr( (string) get_option( Config::OPTION_MEMBER_ID, '' ) ); ?>"
								<?php disabled( Config::areCredentialsByConstant() ); ?> />
							<p class="description"><?php esc_html_e( 'The ProLitteris member number of the publisher.', 'palasthotel-integration-for-prolitteris' ); ?></p>
							<?php $this->lockedNote( Config::areCredentialsByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-username"><?php esc_html_e( 'Username', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="text" id="pl-username" class="regular-text" autocomplete="off"
								name="<?php echo esc_attr( Config::OPTION_USERNAME ); ?>"
								value="<?php echo esc_attr( (string) get_option( Config::OPTION_USERNAME, '' ) ); ?>"
								<?php disabled( Config::areCredentialsByConstant() ); ?> />
							<?php $this->lockedNote( Config::areCredentialsByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-password"><?php esc_html_e( 'Password', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="password" id="pl-password" class="regular-text" autocomplete="new-password"
								name="<?php echo esc_attr( Config::OPTION_PASSWORD ); ?>" value=""
								placeholder="<?php echo $hasPassword ? esc_attr__( 'saved – leave empty to keep it', 'palasthotel-integration-for-prolitteris' ) : ''; ?>"
								<?php disabled( Config::areCredentialsByConstant() ); ?> />
							<?php $this->lockedNote( Config::areCredentialsByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Automatic reporting', 'palasthotel-integration-for-prolitteris' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Config::OPTION_AUTO_MESSAGES ); ?>" value="1"
									<?php checked( Config::isAutoMessagesEnabled() ); disabled( Config::isAutoMessagesByConstant() ); ?> />
								<?php esc_html_e( 'Report published posts hourly, as soon as they can be reported', 'palasthotel-integration-for-prolitteris' ); ?>
							</label>
							<?php $this->lockedNote( Config::isAutoMessagesByConstant() ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-min-chars"><?php esc_html_e( 'Minimum length', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="number" id="pl-min-chars" min="0" class="small-text"
								name="<?php echo esc_attr( Plugin::OPTION_MIN_CHAR_COUNT ); ?>"
								value="<?php echo esc_attr( (string) Options::getMinCharCount() ); ?>" />
							<p class="description"><?php esc_html_e( 'Characters a text needs to be reported.', 'palasthotel-integration-for-prolitteris' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pl-pool"><?php esc_html_e( 'Pixel pool size', 'palasthotel-integration-for-prolitteris' ); ?></label></th>
						<td>
							<input type="number" id="pl-pool" min="0" max="100" class="small-text"
								name="<?php echo esc_attr( Plugin::OPTION_PIXEL_POOL_SIZE ); ?>"
								value="<?php echo esc_attr( (string) Options::getPixelPoolSize() ); ?>" />
							<p class="description"><?php esc_html_e( 'Unassigned pixels kept in stock, refilled hourly.', 'palasthotel-integration-for-prolitteris' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
