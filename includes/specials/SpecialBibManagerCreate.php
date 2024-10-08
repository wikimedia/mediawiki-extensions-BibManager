<?php

class SpecialBibManagerCreate extends IncludableSpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerCreate', 'bibmanagercreate' );
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 * @param string|false $par string or false, provided by Framework
	 * @throws MWException
	 * @global OutputPage $wgOut Current MediaWiki WebRequest object
	 * @global WebRequest $wgRequest Current MediaWiki OutputPage object
	 */
	public function execute( $par ) {
		global $wgOut;
		if ( !$this->getUser()->isAllowed( 'bibmanagercreate' ) ) {
			$wgOut->showErrorPage( 'badaccess', 'badaccess-group0' );

			return;
		}

		global $wgRequest;
		$wgOut->setPageTitle( wfMessage( 'heading_create' )->plain() );
		$wgOut->addWikiMsg( 'bm_create_welcome' );
		$wgOut->addModuleStyles( 'ext.bibManager.styles' );
		$formDescriptor = [
			'bm_select_type' => [
				'class' => HTMLSelectField::class,
				'label' => wfMessage( 'bm_label_entry_type_select' )->plain(),
				'id' => 'bm_select_type',
				'name' => 'bm_select_type',
				'options' => [
					wfMessage( 'bm_entry_type_article' )->plain() => 'article',
					wfMessage( 'bm_entry_type_book' )->plain() => 'book',
					wfMessage( 'bm_entry_type_booklet' )->plain() => 'booklet',
					wfMessage( 'bm_entry_type_conference' )->plain() => 'conference',
					wfMessage( 'bm_entry_type_inbook' )->plain() => 'inbook',
					wfMessage( 'bm_entry_type_incollection' )->plain() => 'incollection',
					wfMessage( 'bm_entry_type_inproceedings' )->plain() => 'inproceedings',
					wfMessage( 'bm_entry_type_manual' )->plain() => 'manual',
					wfMessage( 'bm_entry_type_mastersthesis' )->plain() => 'mastersthesis',
					wfMessage( 'bm_entry_type_misc' )->plain() => 'misc',
					wfMessage( 'bm_entry_type_phdthesis' )->plain() => 'phdthesis',
					wfMessage( 'bm_entry_type_proceedings' )->plain() => 'proceedings',
					wfMessage( 'bm_entry_type_techreport' )->plain() => 'techreport',
					wfMessage( 'bm_entry_type_unpublished' )->plain() => 'unpublished'
				]
			]
		];

		$this->getHookContainer()->run( 'BibManagerCreateBeforeTypeSelectFormCreate', [ $this, &$formDescriptor ] );

		$entryTypeSelectionForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$entryTypeSelectionForm
			->setSubmitText( wfMessage( 'bm_select_entry_type_submit' )->plain() )
			->setSubmitId( 'bm_select_entry_type_submit' )
			->setSubmitCallback( [ $this, 'onSubmit' ] );

		$citation = $wgRequest->getVal( 'bm_bibtexCitation', '' );
		$importParams = [];
		if ( !empty( $citation ) ) {
			$entryTypeSelectionForm->addHiddenField( 'bm_bibtexCitation', $citation );
			$importParams['bm_bibtexCitation'] = $citation;
		}

		$entryTypeSelectionForm->addPostHtml(
			'<div class="bm-create-posttext">' . wfMessage(
				'bm_bibtex_string_import_link',
				SpecialPage::getTitleFor( 'BibManagerImport' )->getLocalURL( $importParams )
			)->plain() . "</div>\n"
		);

		$wgOut->addHTML( '<div id="bm_form">' );
		$entryTypeSelectionForm->show();
		$wgOut->addHTML( '</div>' );
	}

	/**
	 * Submit callback for create form
	 * @param array $formData
	 * @return bool Always true
	 * @throws MWException
	 * @global OutputPage $wgOut
	 */
	public function onSubmit( array $formData ): bool {
		global $wgOut, $wgRequest;

		$citation = $wgRequest->getVal( 'bm_bibtexCitation' );
		if ( !isset( $formData['bm_bibtexCitation'] ) && !empty( $citation ) ) {
			// This should not be necessary, but it seems the hidden field from
			//the type selection form is not properly included in $formData
			$formData['bm_bibtexCitation'] = $citation;
		}

		$wgOut->redirect(
			SpecialPage::getTitleFor( 'BibManagerEdit' )->getFullURL( $formData )
		);

		return true;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
