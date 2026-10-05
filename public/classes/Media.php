<?php


namespace Palasthotel\WordPress\ProLitteris;

use Palasthotel\WordPress\ProLitteris\Components\Attachment\SelectMetaField;
use Palasthotel\WordPress\ProLitteris\Components\Service\AuthorListProvider;

class Media extends _Component {

	/**
	 * @var SelectMetaField
	 */
	private $attachment_author_field;

	public function onCreate() {
		$this->attachment_author_field = SelectMetaField::build( Plugin::ATTACHMENT_META_AUTHOR )
		                                                ->options( new AuthorListProvider() )
		                                                ->label( "Pro-Litteris" )
		                                                ->help( "Bildautor für die Meldung an Pro-Litteris." );
	}

	public function getAuthor( $attachment_id ) {
		return $this->attachment_author_field->getValue( $attachment_id );
	}

}
