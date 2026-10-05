<?php


namespace Palasthotel\WordPress\ProLitteris;


use Palasthotel\WordPress\ProLitteris\Model\Pixel;

/**
 * @property Plugin plugin
 */
class PostsTable extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_filter( 'manage_posts_columns' , array($this, 'add_column') );
		add_action( 'manage_posts_custom_column' , array($this,'custom_columns'), 10, 2 );
	}

	public function add_column($columns){

		if(!$this->plugin->pixel->isEnabled(get_post_type())) return $columns;

		$newCols = array();
		$added = false;
		foreach ($columns as $key => $label){
			if( !$added && ($key == "comments" || $key == "date") ){
				$added = true;
				$newCols['pro-litteris'] = "ProLitteris";
			}
			$newCols[$key] = $label;
		}

		// if to any reason there is no comments or date column add it to the last position
		if($added == false){
			$newCols['pro-litteris'] = "ProLitteris";
		}

		return $newCols;
	}

	public function custom_columns($column, $post_id){
		if($column == 'pro-litteris'){

			$pixel = $this->plugin->repository->getPostPixel($post_id, true);

			if( $pixel instanceof \WP_Error ){
				$this->status( '🔴', $pixel->get_error_message() );
				return;
			} else if($pixel instanceof Pixel){

				if(!$this->plugin->post->canBeReported($post_id)){
					$this->status( '⚪️', sprintf(
						/* translators: %d: minimum number of characters */
						__( 'Cannot be reported, the text has less than %d characters.', 'integration-for-prolitteris' ),
						Options::getMinCharCount()
					) );
					return;
				} else if($this->plugin->database->isMessageReported($pixel->uid)){
					$this->status( '✅', __( 'Reported to ProLitteris', 'integration-for-prolitteris' ) );
					return;
				} else {
					$this->status( '🔶', __( 'Ready to be reported to ProLitteris', 'integration-for-prolitteris' ) );
					return;
				}
			}

			$this->status( '🔵', __( 'No counting pixel fetched from ProLitteris yet', 'integration-for-prolitteris' ) );
		}
	}

	private function status( string $icon, string $title ) {
		printf( '<span title="%1$s" style="cursor: help;">%2$s</span>', esc_attr( $title ), esc_html( $icon ) );
	}
}
