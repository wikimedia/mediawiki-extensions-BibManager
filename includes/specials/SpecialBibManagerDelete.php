<?php

class SpecialBibManagerDelete extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerDelete', 'bibmanagerdelete' );
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 *
	 * @param string|false $par string or false, provided by Framework
	 *
	 * @throws Exception
	 */
	public function execute( $par ) {
		$output = $this->getOutput();

		if ( !$this->getUser()->isAllowed( 'bibmanagerdelete' ) ) {
			$output->showErrorPage( 'badaccess', 'badaccess-group0' );

			return;
		}

		$this->setHeaders();
		$output->setPageTitle( $this->msg( 'heading_delete' )->escaped() );
		$deleteSubmit = $this->getRequest()->getBool( 'bm_delete' );

		$citation = $this->getRequest()->getVal( 'bm_bibtexCitation', '' );
		if ( empty( $citation ) ) {
			$output->addHtml( $this->msg( 'bm_error_not-found', $citation )->escaped() );

			return;
		}

		$entry = BibManagerRepository::singleton()->getBibEntryByCitation( $citation );
		$entryType = $entry['bm_bibtexEntryType'];
		if ( empty( $entry ) || empty( $entryType ) ) {
			$output->addHtml( $this->msg( 'bm_error_not-found', $citation )->escaped() );

			return;
		}

		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		// TODO RBV (17.12.11 15:01): encapsulte in BibManagerFieldsList
		$entryFields = array_merge(
			$typeDefs[$entryType]['required'],
			$typeDefs[$entryType]['optional']
		);
		if ( !$deleteSubmit ) {
			$output->addHTML( $this->msg( 'bm_delete_confirm-delete', $citation )->escaped() );
			$output->addHTML( '<hr />' );

			$table = [];
			$table[] = '<table id="bm_delete" class="wikitable" style="width:100%">';
			// Give grep a chance to find the usages:
			// bm_address, bm_annote, bm_author, bm_booktitle, bm_chapter, bm_crossref, bm_edition,
			// bm_editor, bm_eprint, bm_howpublished, bm_institution, bm_journal, bm_key, bm_month,
			// bm_note, bm_number, bm_organization, bm_pages, bm_publisher, bm_school, bm_series,
			// bm_title, bm_type, bm_url, bm_volume, bm_year
			foreach ( $entryFields as $fieldName ) {
				$table[] = '  <tr><th style="width:150px">' .
					$this->msg( 'bm_' . $fieldName )->escaped() .
					'</th><td>' .
					$entry['bm_' . $fieldName] .
					'</td></tr>';
			}
			$table[] = '</table>';
			$output->addHTML( implode( "\n", $table ) );
		}
		$formDescriptor = [
			'bm_delete' => [
				'class' => HTMLHiddenField::class,
				'default' => true,
				'name' => 'bm_delete',
			],
			'bm_bibtexCitation' => [
				'class' => HTMLHiddenField::class,
				'default' => $citation,
				'name' => 'bm_bibtexCitation',
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'bm_delete' );
		$htmlForm->setSubmitText( $this->msg( 'bm_delete_submit' )->text() )->setSubmitCallback(
				[
					$this,
					'formSubmit'
				]
			)->setSubmitDestructive();

		// TODO: Add cancel button that returns user to the place he came from. I.e. filtered overview

		$output->addHTML( '<div id="bm_form">' );
		$htmlForm->show();
		$output->addHTML( '</div>' );
	}

	/**
	 * Submit callback for delete form
	 *
	 * @param array $formData
	 *
	 * @return bool
	 * @throws MWException
	 */
	public function formSubmit( array $formData ): bool {
		if ( empty( $formData['bm_delete'] ) || $formData['bm_delete'] !== '1' ) {
			return false;
		}

		$result = BibManagerRepository::singleton()->deleteBibEntry( $formData['bm_bibtexCitation'] );

		if ( $result === true ) {
			$this->getOutput()->addHtml(
				sprintf(
					// phpcs:ignore Generic.Files.LineLength.TooLong
					'<div class="successbox"><strong>%s</strong></div><div class="visualClear" id="mw-pref-clear"></div>',
					$this->msg( 'bm_success_save-complete' )->escaped()
				)
			);
			$this->getOutput()->addHtml(
				sprintf(
					'<a href="%s">%s</a>',
					SpecialPage::getTitleFor( "BibManagerList" )->getLocalURL(),
					$this->msg( 'bm_success_link-to-list' )->escaped()
				)
			);
		}

		return $result;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
