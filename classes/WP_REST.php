<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;
use WP_Error;

class WP_REST extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action('rest_api_init', [$this, 'init']);
	}

	public function init(){

		$post_types = $this->plugin->pixel->enabledPostTypes();
		if ( empty( $post_types ) ) {
			return;
		}

		register_rest_field(
			$post_types,
			Plugin::REST_FIELD,
			[
				'get_callback'        => function ( $post ) {
					$postId   = $post["id"];

					// register_rest_field() has no permission_callback: everything below is
					// visible to whoever may read the post, visitors included. They only get the
					// pixel, which is embedded in the page anyway; the message draft (full text,
					// also of password-protected posts) and the authors' ProLitteris member ids
					// are for editors only.
					if ( ! current_user_can( 'edit_post', $postId ) ) {
						$pixel = $this->plugin->repository->getPostPixel(
							$postId,
							'publish' === get_post_status( $postId )
						);
						if ( ! ( $pixel instanceof Pixel ) ) {
							return null;
						}
						$response        = new \stdClass();
						$response->pixel = [
							"uid"    => $pixel->uid,
							"domain" => $pixel->domain,
							"url"    => $pixel->toUrl(),
						];
						return $response;
					}

					$response = new \stdClass();

					$text = $this->plugin->post->getPostText($postId);

					$pixel = $this->plugin->repository->getPostPixel( $postId, true );
					if ( $pixel instanceof WP_Error ) {

						$error = $pixel->get_error_message( Plugin::ERROR_CODE_REQUEST );
						if ( empty( $error ) ) {
							$response->error = $pixel->get_error_message();
						} else {
							$response->error = $pixel->get_error_message();
						}

						return $response;
					}

					if ( ! ( $pixel instanceof Pixel ) ) {
						$response->info = "Konnte keinen Pixel für diesen Inhalt zuweisen.";
						return $response;
					}

					$response->pixel = [
						"uid" => $pixel->uid,
						"domain" => $pixel->domain,
						"url" => $pixel->toUrl(),
					];

					$response->message = $this->plugin->database->getMessage($pixel->uid);

					if(false === $response->message){
						$postMessage = $this->plugin->post->getPostMessage($postId);
						$response->messageDraft = new \stdClass();
						if( $postMessage instanceof WP_Error ){
							$response->messageDraft->error = $postMessage->get_error_message();
						} else {
							$response->messageDraft->pixelUid = $postMessage["pixelUid"];
							$response->messageDraft->title = $postMessage["title"];
							$response->messageDraft->participants = $postMessage["participants"];
							$response->messageDraft->plaintext = $text;
						}
					}

					$pushError = get_post_meta($postId, Plugin::POST_META_PUSH_MESSAGE_ERROR, true);
					if(false !== $pushError && !empty($pushError)){
						$response->pushError = $pushError;
						$response->pushErrorData = get_post_meta($postId, Plugin::POST_META_PUSH_MESSAGE_ERROR_DATA, true);
					}

					return $response;
				},
				'update_callback'     => function ( $value, $post ) {

					if ( ! is_array( $value ) ) {
						return;
					} // there is nothing to change for us

					if(isset($value["pushMessage"])){
						// only what is published may be reported, by someone who may publish it
						$postType = get_post_type_object( $post->post_type );
						if (
							! in_array( get_post_status( $post->ID ), [ 'publish', 'private' ], true )
							|| ! $postType || ! current_user_can( $postType->cap->publish_posts )
						) {
							return new WP_Error(
								'rest_forbidden',
								__( 'Sorry, you are not allowed to report this post.', Plugin::DOMAIN ),
								[ 'status' => rest_authorization_required_code() ]
							);
						}
						$this->plugin->repository->reportPost($post->ID, true);
					}
				},
			]
		);

		register_rest_field(
			"attachment",
			Plugin::REST_FIELD_ATTACHMENT_AUTHOR,
			[
				'get_callback'        => function ( $post ) {
					if ( ! current_user_can( 'edit_post', $post["id"] ) ) {
						return null;
					}
					return $this->plugin->media->getAuthor( $post["id"] );
				},
			]
		);
	}

}
