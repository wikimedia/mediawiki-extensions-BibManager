<?php

class SpecialBibManagerList extends SpecialPage {

	function __construct () {
		parent::__construct( 'BibManagerList' );
	}

	/**
	 * Main method of SpecialPage. Called by Framwork.
	 * @global WebRequest $wgRequest Current MediaWiki WebRequest object
	 * @global OutputPage $wgOut Current MediaWiki OutputPage object
	 * @global User $wgUser Current MediaWiki User object
	 * @param mixed $par string or false, provided by Framework
	 */
	function execute ( $par ) {
		global $wgOut, $wgRequest, $wgUser;
		$this->setHeaders();
		$wgOut->setPageTitle( $this->msg( 'heading_list' ) );
		$wgOut->addHTML( '<div id="bm_form">' );

		$createLink = Linker::link(
			SpecialPage::getTitleFor( 'BibManagerCreate' ),
			SpecialPageFactory::getPage('BibManagerCreate')->getDescription()
		);
		$importLink = Linker::link(
			SpecialPage::getTitleFor( 'BibManagerImport' ),
			SpecialPageFactory::getPage('BibManagerImport')->getDescription()
		);
		$wgOut->addHtml( $this->msg( 'bm_list_welcome', $createLink, $importLink )->escaped() );
		$fieldDefs = BibManagerFieldsList::getFieldDefinitions();
		foreach ( $fieldDefs as $fieldName => $fieldDef ) {
			$selectValues [$fieldDef['label']] = $fieldName;
		}
		ksort( $selectValues );
		$formDescriptor = array (
			'bm_list_search_text' => array (
				'label-message' => 'bm_list_search_term',
				'section' => 'title',
				'class' => 'HTMLTextField',
				'default' => $wgRequest->getVal( 'wpbm_list_search_text', '' ),
			),
			'bm_list_search_select' => array (
				'label-message' => 'bm_list_search_fieldname',
				'section' => 'title',
				'class' => 'HTMLSelectField',
				'options' => $selectValues,
				'default' => $wgRequest->getVal( 'wpbm_list_search_select', '' )
			)
		);

		$htmlForm = new HTMLForm( $formDescriptor, $this->getContext(), 'bm_list_search' );
		$htmlForm->setSubmitText( $this->msg( 'bm_list_search_submit' )->text() );
		$htmlForm->setSubmitCallback( array ( $this, 'submitForm' ) );
		$htmlForm->show();

		$pager = new BibManagerPagerList(); // TODO RBV (17.12.11 15:08): We will need to change this when we support other repos than local DB
		$wgOut->addHtml( '<div id="bm_table">' );
		$sDataBody = $pager->getBody();
		if ( !empty( $sDataBody ) ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$table = array ( );
			$table[] = '<form method="post" action="' . SpecialPage::getTitleFor( 'BibManagerExport' )->getLocalURL() . '">';
			$table[] = '  <table class="wikitable" style="width:100%;">';
			$table[] = '    <tr>';
			$table[] = '      <th style="width: 100px;">' . $this->msg( 'bm_list_table_heading-name' )->escaped() . '</th>';
			$table[] = '      <th>' . $this->msg( 'bm_list_table_heading-description' )->escaped() . '</th>';
			if ($wgUser->isAllowed('bibmanagerdelete') || $wgUser->isAllowed('bibmanageredit')) {
				$table[] = '      <th style="width: 70px;">' . $this->msg( 'bm_list_table_heading-actions' )->escaped() . '</th>';
			}
			$table[] = '      <th style="width: 50px;" id="bm_table_export_column_heading">' . $this->msg( 'bm_list_table_heading-export' )->escaped() . '</th>';
			$table[] = '    </tr>';
			$table[] = $sDataBody;
			$table[] = '  </table>';
			$table[] = Html::input(
				'submit-export',
				$this->msg( "bm_list_table_submit-export" )->text(),
				'submit',
				array ( 'style' => 'float:right;' )
			);
			$table[] = '</form>';

			$wgOut->addHTML( implode( "\n", $table ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHtml( $this->msg( 'bm_error_no-data-found' )->escaped() );
		}
		$wgOut->addHtml( '</div>' );
		$wgOut->addHTML( '</div>' );
	}

	public function submitForm ( $formData ) {
		return false;
	}

	protected function getGroupName() {
		return 'bibmanager';
	}
}
