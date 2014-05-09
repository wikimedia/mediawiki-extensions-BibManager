<?php

class SpecialBibManagerCreate extends IncludableSpecialPage {

	public function __construct () {
		parent::__construct( 'BibManagerCreate' , 'bibmanagercreate');
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 * @global OutputPage $wgOut Current MediaWiki WebRequest object
	 * @global WebRequest $wgRequest Current MediaWiki OutputPage object
	 * @param mixed $par string or false, provided by Framework
	 */
	public function execute ( $par ) {
		global $wgUser, $wgOut;
		if (!$wgUser->isAllowed('bibmanagercreate')){
			$wgOut->showErrorPage('badaccess','badaccess-group0');
			return true;
		}

		global $wgRequest;
		$wgOut->setPageTitle( wfMsg( 'heading_create' ) );
		$wgOut->addWikiMsg( 'bm_create_welcome' );
		$formDescriptor = array (
		    'bm_select_type' => array (
			'class' => 'HTMLSelectField',
			'label' => wfMsg( 'bm_label_entry_type_select' ),
			'id' => 'bm_select_type',
			'options' => array (
			    '' => '-',
			    wfMsg( 'bm_entry_type_article' ) => 'article',
			    wfMsg( 'bm_entry_type_book' ) => 'book',
			    wfMsg( 'bm_entry_type_booklet' ) => 'booklet',
			    wfMsg( 'bm_entry_type_conference' ) => 'conference',
			    wfMsg( 'bm_entry_type_inbook' ) => 'inbook',
			    wfMsg( 'bm_entry_type_incollection' ) => 'incollection',
			    wfMsg( 'bm_entry_type_inproceedings' ) => 'inproceedings',
			    wfMsg( 'bm_entry_type_manual' ) => 'manual',
			    wfMsg( 'bm_entry_type_mastersthesis' ) => 'mastersthesis',
			    wfMsg( 'bm_entry_type_misc' ) => 'misc',
			    wfMsg( 'bm_entry_type_phdthesis' ) => 'phdthesis',
			    wfMsg( 'bm_entry_type_proceedings' ) => 'proceedings',
			    wfMsg( 'bm_entry_type_techreport' ) => 'techreport',
			    wfMsg( 'bm_entry_type_unpublished' ) => 'unpublished'
			)
		    )
		);

		wfRunHooks( 'BibManagerCreateBeforeTypeSelectFormCreate', array ( $this, &$formDescriptor ) );

		$entryTypeSelectionForm = new HTMLForm( $formDescriptor, $this->getContext() );
		$entryTypeSelectionForm->setSubmitText( wfMsg( 'bm_select_entry_type_submit' ) );
		$entryTypeSelectionForm->setSubmitId( 'bm_select_entry_type_submit' );
		$entryTypeSelectionForm->setSubmitCallback( array ( $this, 'onSubmit' ) );

		$citation = $wgRequest->getVal( 'bm_bibtexCitation', '' );
		$importParams = array ( );
		if ( !empty( $citation ) ) {
			$entryTypeSelectionForm->addHiddenField( 'bm_bibtexCitation', $citation );
			$importParams['bm_bibtexCitation'] = $citation;
		}

		$entryTypeSelectionForm->addPostText(
		    wfMsg(
				'bm_bibtex_string_import_link',
				SpecialPage::getTitleFor( 'BibManagerImport' )->getLocalURL( $importParams )
		    )
		);

		$wgOut->addHTML( '<div id="bm_form">' );
		$entryTypeSelectionForm->show();
		$wgOut->addHTML( '</div>' );
	}

	/**
	 * Submit callback for create form
	 * @global OutputPage $wgOut
	 * @param array $formData
	 * @return bool Always true
	 */
	public function onSubmit ( $formData ) {
		global $wgOut, $wgRequest;
		$citation = $wgRequest->getVal( 'bm_bibtexCitation' );
		if ( !isset( $formData['bm_bibtexCitation'] ) && !empty( $citation ) ) {
			//This should not be necessary, but it seems the hidden field from
			//the type selection form is not properly included in $formData
			$formData['bm_bibtexCitation'] = $citation;
		}
		$wgOut->redirect(
			SpecialPage::getTitleFor( 'BibManagerEdit' )->getFullURL( $formData )
		);
		return true;
	}
}
