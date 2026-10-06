<?php


namespace Palasthotel\ProLitteris;


class DashboardWidget extends _Component {

	const CAPABILITY = 'edit_others_posts';
	const NONCE_ACTION = 'pro_litteris_refill_pixel_pool';

	public function onCreate() {
		parent::onCreate();
		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
	}

	public function setup(){
		if ( ! Config::isEnabled() || ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		wp_add_dashboard_widget(
			Plugin::DASHBOARD_WIDGET_ID,
			__("Pro Litteris", 'palasthotel-integration-for-prolitteris'),
			[$this, 'widget'],
			[$this, 'config']
		);
	}

	public function widget(){
		$submit_button_name = "submit_refill_pixel_pool";
		if ( ! Config::isEnabled() || ! Config::hasConnection() ) {
			printf(
				'<p>%1$s <a href="%2$s">%3$s</a></p>',
				esc_html__( "The integration is not set up yet.", 'palasthotel-integration-for-prolitteris' ),
				esc_url( admin_url( 'options-general.php?page=' . Settings::PAGE ) ),
				esc_html__( 'Settings → ProLitteris', 'palasthotel-integration-for-prolitteris' )
			);
			return;
		}

		if(isset($_POST[$submit_button_name]) && check_admin_referer( self::NONCE_ACTION )){
			$this->plugin->repository->refillPixelPool(Options::getPixelPoolSize());
		}

		if($this->plugin->repository->isAutoMessagesEnabled()){
			$postIds = $this->plugin->repository->database->getPostIdsReadyForMessage();
			if(count($postIds) > 0 ){
				printf("<p>%s</p>", esc_html__("Posts will be reported on next cron schedule:", 'palasthotel-integration-for-prolitteris'));
				echo "<ul>";
				foreach ($postIds as $i =>  $postId){
					printf("<li><a href='%s'>%s</a></li>", esc_url( get_edit_post_link($postId) ), esc_html( get_the_title($postId) ));
					if($i > 9){
						echo "<li>…</li>";
						break;
					}
				}
				echo "</ul>";
			} else {
				printf("<p>%s</p>", esc_html__("All posts in question are reported.", 'palasthotel-integration-for-prolitteris'));
			}

			echo "<hr />";
        }


		$aspired = Options::getPixelPoolSize();
		$size = $this->plugin->database->countAvailablePixels();
		printf(
			"<p>%s</p>",
			esc_html( sprintf(
				/* translators: 1: available pixels, 2: aspired pool size */
				__( '%1$d/%2$d pixels available in pool.', 'palasthotel-integration-for-prolitteris' ),
				$size,
				$aspired
			) )
		);

		printf(
			"<p class='description'>%s</p>",
			esc_html__("The pixel pool is filled up every hour. If you need new pixels immediately, you can manually request new ones here.", 'palasthotel-integration-for-prolitteris')
		);

		$needPixels = $size < $aspired;
		$attrs = [];
		if(!$needPixels){
			$attrs["disabled"] = true;
		}
		echo "<form method='POST'>";
		wp_nonce_field( self::NONCE_ACTION );
		submit_button(
			__("Refill", 'palasthotel-integration-for-prolitteris'),
			"primary",
			$submit_button_name,
			false,
			$attrs
		);
		echo "</form>";
	}

	public function config(){
		// core checks the nonce of the widget form before calling this
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if(isset($_POST[Plugin::OPTION_PIXEL_POOL_SIZE]) && current_user_can( self::CAPABILITY )){
			Options::setPixelPoolSize(min(100, max(0, intval($_POST[Plugin::OPTION_PIXEL_POOL_SIZE]))));
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		?>
		<div style="padding-bottom: 10px;">
			<label><?php esc_html_e( 'Aspired pixel pool size:', 'palasthotel-integration-for-prolitteris' ); ?><br/>
				<input
					type="number"
					min="0"
					max="100"
					style="width: 100px;"
					name="<?php echo esc_attr( Plugin::OPTION_PIXEL_POOL_SIZE ); ?>"
					value="<?php echo esc_attr( (string) Options::getPixelPoolSize() ); ?>"
				/>
			</label>
		</div>
		<?php
	}


}
