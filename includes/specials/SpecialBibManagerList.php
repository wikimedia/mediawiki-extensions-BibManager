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
		$wgOut->setPageTitle( wfMsg( 'heading_list' ) );
		$wgOut->addHTML( '<div id="bm_form">' );

		$createLink = Linker::link(
			SpecialPage::getTitleFor( 'BibManagerCreate' ),
			SpecialPageFactory::getPage('BibManagerCreate')->getDescription()
		);
		$importLink = Linker::link(
			SpecialPage::getTitleFor( 'BibManagerImport' ),
			SpecialPageFactory::getPage('BibManagerImport')->getDescription()
		);
		$wgOut->addHtml( wfMsg( 'bm_list_welcome', $createLink, $importLink ) );
		$fieldDefs = BibManagerFieldsList::getFieldDefinitions();
		foreach ( $fieldDefs as $fieldName => $fieldDef ) {
			$selectValues [$fieldDef['label']] = $fieldName;
		}
		ksort( $selectValues );
		$formDescriptor = array (
			'bm_list_search_text' => array (
				'label' => wfMsg( 'bm_list_search_term' ),
				'section' => 'title',
				'class' => 'HTMLTextField',
				'default' => $wgRequest->getVal( 'wpbm_list_search_text', '' ),
			),
			'bm_list_search_select' => array (
				'label' => wfMsg( 'bm_list_search_fieldname' ),
				'section' => 'title',
				'class' => 'HTMLSelectField',
				'options' => $selectValues,
				'default' => $wgRequest->getVal( 'wpbm_list_search_select', '' )
			)
		);

		$htmlForm = new HTMLForm( $formDescriptor, $this->getContext(), 'bm_list_search' );
		$htmlForm->setSubmitText( wfMsg( 'bm_list_search_submit' ) );
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
			$table[] = '      <th style="width: 100px;">' . wfMsg( 'bm_list_table_heading-name' ) . '</th>';
			$table[] = '      <th>' . wfMsg( 'bm_list_table_heading-description' ) . '</th>';
			if ($wgUser->isAllowed('bibmanagerdelete') || $wgUser->isAllowed('bibmanageredit')) {
				$table[] = '      <th style="width: 70px;">' . wfMsg( 'bm_list_table_heading-actions' ) . '</th>';
			}
			$table[] = '      <th style="width: 50px;" id="bm_table_export_column_heading">' . wfMsg( 'bm_list_table_heading-export' ) . '</th>';
			$table[] = '    </tr>';
			$table[] = $sDataBody;
			$table[] = '  </table>';
			$table[] = Html::input(
				'submit-export',
				wfMsg( "bm_list_table_submit-export" ),
				'submit',
				array (
					'style' => 'float:right;',
					'class' => 'mw-ui-button mw-ui-progressive'
				)
			);
			$table[] = '</form>';

			$wgOut->addHTML( implode( "\n", $table ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHtml( wfMsg( 'bm_error_no-data-found' ) );
		}
		$wgOut->addHtml( '</div>' );
		$wgOut->addHTML( '</div>' );
	}

	public function submitForm ( $formData ) {
		return false;
	}
}
