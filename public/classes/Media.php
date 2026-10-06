<?php


namespace Palasthotel\ProLitteris;

use Palasthotel\ProLitteris\Components\Attachment\SelectMetaField;
use Palasthotel\ProLitteris\Components\Service\AuthorListProvider;

class Media extends _Component {

	/**
	 * @var SelectMetaField
	 */
	private $attachment_author_field;

	public function onCreate() {
		// on init: the label is translated, and translations must not load earlier
		add_action( 'init', [ $this, 'field' ] );
	}

	public function field(): SelectMetaField {
		if ( null === $this->attachment_author_field ) {
			$this->attachment_author_field = SelectMetaField::build( Plugin::ATTACHMENT_META_AUTHOR )
			                                                ->options( new AuthorListProvider() )
			                                                ->label( __( 'ProLitteris', 'palasthotel-integration-for-prolitteris' ) )
			                                                ->help( __( 'Image originator reported to ProLitteris.', 'palasthotel-integration-for-prolitteris' ) );
		}

		return $this->attachment_author_field;
	}

	public function getAuthor( $attachment_id ) {
		return $this->field()->getValue( $attachment_id );
	}

}
