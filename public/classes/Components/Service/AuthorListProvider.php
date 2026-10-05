<?php


namespace Palasthotel\WordPress\ProLitteris\Components\Service;


use Palasthotel\WordPress\ProLitteris\Components\Model\Option;
use Palasthotel\WordPress\ProLitteris\Plugin;
use WP_User;

class AuthorListProvider implements ProviderInterface {

	private $options;

	public function get() {

		if(null === $this->options){
			$authors = get_users( [
				"who" => "authors",
			] );

			global $wpdb;
			$keyId = Plugin::USER_META_PRO_LITTERIS_ID;
			$keySurName = Plugin::USER_META_PRO_LITTERIS_SURNAME;
			$keyName = Plugin::USER_META_PRO_LITTERIS_NAME;
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key IN ( %s, %s, %s ) AND meta_value != ''",
					$keyId,
					$keySurName,
					$keyName
				)
			);

			$options = array_map( function ( $author ) use ( $keyName, $keySurName, $keyId, $results) {
				/**
				 * @var WP_User $author
				 */
				$id = "";
				$name = "";
				$surName = "";
				foreach ($results as $result){
					if($result->meta_key === $keyId && $result->user_id === $author->ID.""){
						$id = $result->meta_value;
					}
					if($result->meta_key === $keySurName && $result->user_id === $author->ID.""){
						$surName = $result->meta_value;
					}
					if($result->meta_key === $keyName && $result->user_id === $author->ID.""){
						$name = $result->meta_value;
					}
				}

				$displayName = ( ! empty( $surName ) && ! empty( $name ) ) ? "$name $surName" : $author->display_name;

				return Option::build( $author->ID, "{$displayName} ($id)" );
			}, $authors );

			$this->options = array_merge( [ Option::build( "", "" ) ], $options );
		}

		return $this->options;
	}
}