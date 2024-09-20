<?php

class SpecialBibManagerEdit extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerEdit', 'bibmanageredit' );
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 * @param string|false $par string or false, provided by Framework
	 * @throws Exception
	 * @global WebRequest $wgRequest Current MediaWiki WebRequest object
	 * @global OutputPage $wgOut Current MediaWiki OutputPage object
	 */
	public function execute( $par ) {
		global $wgOut;

		if ( !$this->getUser()->isAllowed( 'bibmanageredit' ) ) {
			$wgOut->showErrorPage( 'badaccess', 'badaccess-group0' );

			return;
		}

		global $wgRequest;
		$this->setHeaders();

		$citation = !empty( $par ) ? $par : $wgRequest->getVal( 'bm_bibtexCitation', '' );

		$entry = [];
		$entry['bm_bibtexCitation'] = $citation;
		if ( !empty( $citation ) ) {
			$e = BibManagerRepository::singleton()
					->getBibEntryByCitation( $citation );
			if ( !empty( $e ) ) {
				$entry = $e;
			}
		}

		$entryType = $wgRequest->getVal( 'bm_select_type', '' );
		if ( empty( $entryType ) ) {
			$entryType = $wgRequest->getVal( 'bm_bibtexEntryType', '' );
		}
		if ( isset( $entry['bm_bibtexEntryType'] ) ) {
			$entryType = $entry['bm_bibtexEntryType'];
		}

		if ( empty( $entryType ) ) {
			// TODO RBV (17.12.11 16:53): I18N
			$wgOut->addHTML( 'No Citation or EntryType provided.' );

			return;
		}

		// Give grep a chance to find the usages: bm_entry_type_article, bm_entry_type_book,
		// bm_entry_type_booklet, bm_entry_type_conference, bm_entry_type_inbook,
		// bm_entry_type_incollection, bm_entry_type_inproceedings, bm_entry_type_manual,
		// bm_entry_type_mastersthesis, bm_entry_type_misc, bm_entry_type_phdthesis,
		// bm_entry_type_proceedings, bm_entry_type_techreport, bm_entry_type_unpublished
		$wgOut->setPageTitle( $this->msg( 'heading_edit', $this->msg( 'bm_entry_type_' . $entryType )->text() ) );

		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$bibTeXFields = BibManagerFieldsList::getFieldDefinitions();

		$formDescriptor = [];
		$formDescriptor['bm_bibtexCitation'] = [
			'class' => HTMLTextField::class,
			'label-message' => 'bm_citation',
			'section' => 'citation',
			'required' => true,
			'name' => 'bm_bibtexCitation',
			'validation-callback' => 'BibManagerValidator::validateCitation'
		];

		$editMode = $wgRequest->getBool( 'bm_edit_mode' );

		if ( $editMode ) {
			// If it is a edit we dont need to revalidate
			unset( $formDescriptor['bm_bibtexCitation']['validation-callback'] );
			$formDescriptor['bm_bibtexCitation']['readonly'] = true;
			$formDescriptor['bm_bibtexCitation']['default'] = $entry['bm_bibtexCitation'];
			$formDescriptor['bm_bibtexCitation']['help-message'] = 'bm_readonly';
		}

		$formDescriptor['bm_edit_mode'] = [
			'class' => HTMLHiddenField::class,
			'default' => $editMode ? 1 : 0,
			'name' => 'bm_edit_mode',
		];

		foreach ( $typeDefs[$entryType]['required'] as $fieldName ) {
			$fieldDef = $bibTeXFields[$fieldName];
			$fieldDef['required'] = true;
			$fieldDef['section'] = 'required';
			$fieldDef['name'] = 'bm_' . $fieldName;
			$fieldDef['default'] = isset( $entry['bm_' . $fieldName] ) ? $entry['bm_' . $fieldName] : '';
			$formDescriptor['bm_' . $fieldName] = $fieldDef;
		}

		foreach ( $typeDefs[$entryType]['optional'] as $fieldName ) {
			$fieldDef = $bibTeXFields[$fieldName];
			$fieldDef['section'] = 'optional';
			$fieldDef['name'] = 'bm_' . $fieldName;
			$fieldDef['default'] = isset( $entry['bm_' . $fieldName] ) ? $entry['bm_' . $fieldName] : '';
			$formDescriptor['bm_' . $fieldName] = $fieldDef;
		}

		$formDescriptor['bm_bibtexEntryType'] = [
			'class' => HTMLHiddenField::class,
			'default' => $entryType,
			'name' => 'bm_bibtexEntryType',
		];
		$formDescriptor['bm_select_type'] = $formDescriptor['bm_bibtexEntryType'];

		$this->getHookContainer()->run( 'BibManagerEditBeforeFormCreate', [ $this, &$formDescriptor ] );

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'bm_edit' );
		$htmlForm
			->setSubmitText( $this->msg( 'bm_edit_submit' )->text() )
			->setSubmitCallback( [ $this, 'submitForm' ] );
		$wgOut->addHTML( '<div id="bm_form">' );
		$htmlForm->show();
		$wgOut->addHTML( '</div>' );
	}

	/**
	 * Submit callback for edit form
	 *
	 * @param array $formData
	 * @return bool
	 * @throws MWException
	 */
	public function submitForm( array $formData ): bool {
		$repo = BibManagerRepository::singleton();
		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$entryType = $formData['bm_bibtexEntryType'];
		$entryFields = array_merge(
			$typeDefs[$entryType]['required'], $typeDefs[$entryType]['optional']
		);

		$submittedFields = [];
		foreach ( $formData as $key => $value ) {
			$unprefixedKey = substr( $key, 3 );
			if ( in_array( $unprefixedKey, $entryFields ) ) {
				$submittedFields[$key] = $value;
			}
		}

		// No update? No problem...
		$repo->deleteBibEntry( $formData['bm_bibtexCitation'] );
		$repo->saveBibEntry( $formData['bm_bibtexCitation'], $entryType, $submittedFields );

		$this->getOutput()->addHtml( sprintf(
			'<div class="successbox"><strong>%s</strong></div><div class="visualClear" id="mw-pref-clear"></div>',
			$this->msg( 'bm_success_save-complete' )->escaped() )
		);
		$this->getOutput()->addHtml( sprintf(
			'<a href="%s">%s</a>',
			SpecialPage::getTitleFor( "BibManagerList" )->getLocalURL(),
			$this->msg( 'bm_success_link-to-list' )->escaped() )
		);

		return true;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
