<?php

use MediaWiki\MediaWikiServices;

class SpecialBibManagerList extends SpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerList' );
	}

	/**
	 * Main method of SpecialPage. Called by Framwork.
	 *
	 * @param string|false $par string or false, provided by Framework
	 *
	 * @throws MWException
	 * @global WebRequest $wgRequest Current MediaWiki WebRequest object
	 * @global OutputPage $wgOut Current MediaWiki OutputPage object
	 */
	public function execute( $par ): void {
		global $wgOut, $wgRequest;

		$this->setHeaders();
		$wgOut->setPageTitle( $this->msg( 'heading_list' ) );
		$wgOut->enableOOUI();
		$wgOut->addHTML( '<div id="bm_form">' );

		$specialPageFactory = MediaWikiServices::getInstance()->getSpecialPageFactory();
		$createPage = $specialPageFactory->getPage( 'BibManagerCreate' );
		$importPage = $specialPageFactory->getPage( 'BibManagerImport' );
		$createLink = $this->getLinkRenderer()->makeLink(
			SpecialPage::getTitleFor( 'BibManagerCreate' ),
			$createPage->getDescription()
		);
		$importLink = $this->getLinkRenderer()->makeLink(
			SpecialPage::getTitleFor( 'BibManagerImport' ),
			$importPage->getDescription()
		);

		$wgOut->addHtml( $this->msg( 'bm_list_welcome', $createLink, $importLink )->text() );
		$fieldDefs = BibManagerFieldsList::getFieldDefinitions();
		foreach ( $fieldDefs as $fieldName => $fieldDef ) {
			$selectValues[$fieldDef['label']] = $fieldName;
		}
		ksort( $selectValues );
		$formDescriptor = [
			'bm_list_search_text' => [
				'label-message' => 'bm_list_search_term',
				'section' => 'title',
				'class' => HTMLTextField::class,
				'name' => 'bm_list_search_text',
				'default' => $wgRequest->getVal( 'bm_list_search_text', '' ),
			],
			'bm_list_search_select' => [
				'label-message' => 'bm_list_search_fieldname',
				'section' => 'title',
				'class' => HTMLSelectField::class,
				'name' => 'bm_list_search_select',
				'options' => $selectValues,
				'default' => $wgRequest->getVal( 'bm_list_search_select', '' )
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'bm_list_search' );
		$htmlForm->setSubmitText( $this->msg( 'bm_list_search_submit' )->text() )->setSubmitCallback(
				[
					$this,
					'submitForm'
				]
			)->show();

		// TODO RBV (17.12.11 15:08): We will need to change this when we support other repos than local DB
		$pager = new BibManagerPagerList();
		$wgOut->addHtml( '<div id="bm_table">' );
		$sDataBody = $pager->getBody();
		$user = $this->getUser();
		if ( !empty( $sDataBody ) ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$table = [];
			$table[] = '<form method="post" action="' .
				SpecialPage::getTitleFor( 'BibManagerExport' )->getLocalURL() .
				'">';
			$table[] = '  <table class="wikitable" style="width:100%;">';
			$table[] = '    <tr>';
			$table[] = '      <th style="width: 100px;">' .
				$this->msg( 'bm_list_table_heading-name' )->escaped() .
				'</th>';
			$table[] = '      <th>' . $this->msg( 'bm_list_table_heading-description' )->escaped() . '</th>';
			if ( $user->isAllowed( 'bibmanagerdelete' ) || $user->isAllowed( 'bibmanageredit' ) ) {
				$table[] = '      <th style="width: 70px;">' .
					$this->msg( 'bm_list_table_heading-actions' )->escaped() .
					'</th>';
			}
			$table[] = '      <th style="width: 50px;" id="bm_table_export_column_heading">' .
				$this->msg( 'bm_list_table_heading-export' )->escaped() .
				new OOUI\CheckboxInputWidget( [
					'classes' => [ 'bm-list-table-export-checkbox' ]
				] ) .
				'</th>';
			$table[] = '    </tr>';
			$table[] = $sDataBody;
			$table[] = '  </table>';
			$table[] = new OOUI\ButtonInputWidget( [
				'type' => 'submit',
				'name' => 'submit-export',
				'label' => $this->msg( 'bm_list_table_submit-export' )->text()
			] );
			$table[] = '</form>';

			$wgOut->addHTML( implode( "\n", $table ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHtml( $this->msg( 'bm_error_no-data-found' )->escaped() );
		}
		$wgOut->addHtml( '</div>' );
		$wgOut->addHTML( '</div>' );
	}

	public function submitForm( array $formData ): bool {
		return false;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
