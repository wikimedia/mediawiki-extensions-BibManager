<?php

class SpecialBibManagerImport extends SpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerImport', 'bibmanageredit' );
	}

	/** @inheritDoc */
	public function doesWrites(): bool {
		return true;
	}

	/**
	 * Main method of SpecialPage. Called by Framework.
	 *
	 * @global WebRequest $wgRequest Current MediaWiki WebRequest object
	 * @global OutputPage $wgOut Current MediaWiki OutputPage object
	 * @param string|false $par string or false, provided by Framework
	 */
	public function execute( $par ): void {
		global $wgOut;

		if ( !$this->getUser()->isAllowed( 'bibmanageredit' ) ) {
			$wgOut->showErrorPage( 'badaccess', 'badaccess-group0' );

			return;
		}

		global $wgRequest;
		$this->setHeaders();
		$wgOut->setPageTitle( $this->msg( 'heading_import' ) );

		if ( $wgRequest->getVal( 'bm_bibtex', '' ) == '' ) {
			$wgOut->addHtml( $this->msg( 'bm_import_welcome' )->escaped() );
		}

		$formDescriptor['bm_bibtex'] = [
			'class' => 'HTMLTextAreaField',
			'rows' => 25,
			'name' => 'bm_bibtex'
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'bm_edit' );
		$htmlForm
			->setSubmitTextMsg( 'bm_edit_submit' )
			->setSubmitCallback( [ $this, 'submitForm' ] );

		$wgOut->addHTML( '<div id="bm_form">' );
		$htmlForm->show();
		$wgOut->addHTML( '</div>' );
	}

	/**
	 * Submit callback for import form
	 * @param array $formData
	 * @return mixed true on success, array of error messages on failure
	 * @throws MWException
	 * @throws Structures_BibTex_Exception
	 * @global OutputPage $wgOut
	 */
	public function submitForm( array $formData ) {
		global $wgOut;

		$bibtex = new Structures_BibTex();
		$bibtex->setOption( "extractAuthors", false );
		$bibtex->content = $formData['bm_bibtex'];
		$bibtex->parse();

		$errors = [];
		$repo = BibManagerRepository::singleton();
		$cleanedEntries = [];
		// TODO RBV (18.12.11 15:05): Optimize this
		foreach ( $bibtex->data as $entry ) {
			if ( empty( $entry ) ) {
				continue;
			}

			$citation = trim( $entry['cite'] );
			// TODO RBV (18.12.11 15:14): This is very similar to BibManagerEdit specialpage. --> encapsulate.
			$entryType = $entry['entryType'];
			$typeDefs = BibManagerFieldsList::getTypeDefinitions();
			$entryFields = array_merge(
				$typeDefs[$entryType]['required'], $typeDefs[$entryType]['optional']
			);

			$submittedFields = [];

			foreach ( $entry as $key => $value ) {
				if ( in_array( $key, $entryFields ) ) {
					$submittedFields['bm_' . $key] = $value;
				}
			}

			$result = BibManagerValidator::validateCitation( $citation, $submittedFields );

			if ( $result !== true ) {
				$errors[] = $result;
				// $errors[] = array( 'bm_error_citation_exists', $citation, $citation.'X' );
			} else {
				// TODO RBV (18.12.11 16:02): field validation!!!
				$cleanedEntries[] = [ $citation, $entryType, $submittedFields ];
			}
		}
		if ( !empty( $errors ) ) {
			return '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>';
		}

		foreach ( $cleanedEntries as $cleanedEntry ) {
			$repo->saveBibEntry( $cleanedEntry[0], $cleanedEntry[1], $cleanedEntry[2] );
		}

		$wgOut->addHtml( sprintf(
			'<div class="successbox"><strong>%s</strong></div><div class="visualClear" id="mw-pref-clear"></div>',
			$this->msg( 'bm_success_save-complete' )->escaped() )
		);

		$wgOut->addHtml( sprintf(
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
