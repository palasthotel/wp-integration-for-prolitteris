<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;

/**
 * @property Plugin plugin
 */
class TrackingPixel extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action( 'wp_head', [$this, 'head'] );
		add_filter( 'wp_footer', array( $this, 'footer' ) );
	}

	/**
	 * list of post types that are using pro litteris pixel
	 * @return array
	 */
	public function enabledPostTypes(){
		return apply_filters(Plugin::FILTER_POST_TYPES, array("post"));
	}

	/**
	 * check if a post type is activated for pixel
	 * @param string $postType
	 *
	 * @return bool
	 */
	public function isEnabled($postType){
		return in_array($postType, $this->enabledPostTypes());
	}

	/**
	 * The pixel of the post this page shows, if it gets one: only with the integration
	 * enabled, on the single view of an enabled post type, and if the
	 * pro_litteris_render_pixel filter (e.g. a consent manager) allows it. Never on
	 * archives, where it would count a view for whatever post the loop ended with.
	 */
	private function currentPixel(): ?Pixel {
		if ( ! Config::isEnabled() || ! is_singular() ) {
			return null;
		}
		$postId = get_queried_object_id();
		if ( ! $postId || ! $this->isEnabled( get_post_type( $postId ) ) ) {
			return null;
		}
		if ( ! apply_filters( Plugin::FILTER_RENDER_PIXEL, true ) ) {
			return null;
		}
		$pixel = $this->plugin->repository->getPostPixel( $postId );

		return $pixel instanceof Pixel ? $pixel : null;
	}

	/**
	 * ProLitteris needs the full referrer to attribute the view to the page.
	 */
	public function head(){
		if ( null === $this->currentPixel() ) {
			return;
		}
		echo '<meta name="referrer" content="no-referrer-when-downgrade">' . "\n";
	}

	public function footer() {
		$pixel = $this->currentPixel();
		if ( null === $pixel ) {
			return;
		}
		echo '<img src="' . esc_url( $pixel->toUrl() ) . '" height="1" width="1" border="0" class="pro-litteris-pixel" alt="" />';
	}


}
