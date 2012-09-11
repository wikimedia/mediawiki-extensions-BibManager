<?php

class BibManagerDelete extends UnlistedSpecialPage {

	public function __construct () {
		parent::__construct( 'BibManagerDelete' , 'bibmanagerdelete');
		wfLoadExtensionMessages( 'BibManager' );
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 * @global WebRequest $wgRequest Current MediaWiki WebRequest object
	 * @global OutputPage $wgOut Current MediaWiki OutputPage object
	 * @param mixed $par string or false, provided by Framework
	 */
	public function execute ( $par ) {
		global $wgUser, $wgOut;
		if (!$wgUser->isAllowed('bibmanagerdelete')){
			$wgOut->showErrorPage('badaccess','badaccess-group0');
			return true;
		}

		global $wgRequest;
		$this->setHeaders();
		$wgOut->setPageTitle( wfMsg( 'heading_delete' ) );
		$deleteSubmit = $wgRequest->getBool( 'bm_delete' );

		$citation = $wgRequest->getVal( 'bm_bibtexCitation', '' );
		if ( empty( $citation ) ) {
			$wgOut->addHtml( wfMsg( 'bm_error_not-found', $citation ) );
			return;
		}

		$entry = BibManagerRepository::singleton()->getBibEntryByCitation( $citation );
		if ( empty( $entry ) ) {
			$wgOut->addHtml( wfMsg( 'bm_error_not-found', $citation ) );
		}
		$entryType = $entry['bm_bibtexEntryType'];
		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$entryFields = array_merge( // TODO RBV (17.12.11 15:01): encapsulte in BibManagerFieldsList
		    $typeDefs[$entryType]['required'], $typeDefs[$entryType]['optional']
		);
		if ( !$deleteSubmit ) {
			$wgOut->addHTML( wfMsg( 'bm_delete_confirm-delete', $citation ) );
			$wgOut->addHTML( '<hr />' );

			$table = array ( );
			$table[] = '<table id="bm_delete" class="wikitable" style="width:100%">';
			foreach ( $entryFields as $fieldName ) {
				$table[] = '  <tr><th style="width:150px">' . wfMsg( 'bm_' . $fieldName ) . '</th><td>' . $entry['bm_' . $fieldName] . '</td></tr>';
			}
			$table[] = '</table>';
			$wgOut->addHTML( implode( "\n", $table ) );
		}
		$formDescriptor = array (
		    'bm_delete' => array (
			'class' => 'HTMLHiddenField',
			'default' => true,
		    ),
		    'bm_bibtexCitation' => array (
			'class' => 'HTMLHiddenField',
			'default' => $citation,
		    )
		);

		$htmlForm = new HTMLForm( $formDescriptor, 'bm_delete' );
		$htmlForm->setSubmitText( wfMsg( 'bm_delete_submit' ) );
		$htmlForm->setTitle( $this->getTitle() );
		$htmlForm->setSubmitCallback( array ( $this, 'formSubmit' ) );
		//TODO: Add cancel button that returns user to the place he came from. I.e. filtered overview

		$wgOut->addHTML( '<div id="bm_form">' );
		$htmlForm->show();
		$wgOut->addHTML( '</div>' );
	}

	/**
	 * Submit callback for delete form
	 * @global OutputPage $wgOut
	 * @param array $formData
	 * @return boolean
	 */
	public function formSubmit ( $formData ) {
		global $wgOut;
		if ( empty( $formData['bm_delete'] ) || $formData['bm_delete'] !== true )
			return false;

		$result = BibManagerRepository::singleton()->deleteBibEntry( $formData['bm_bibtexCitation'] );

		if ( $result === true ) {
			$wgOut->addHtml( wfMsg( 'bm_success_save-complete' ) );
			$wgOut->addHtml( 
				wfMsg( 
					'bm_success_link-to-list', 
					SpecialPage::getTitleFor( "BibManagerList" )->getLocalURL()
				)
			);
		}
		return $result;
	}
}