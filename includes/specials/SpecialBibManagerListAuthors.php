<?php

class SpecialBibManagerListAuthors extends SpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerListAuthors' );
	}

	/**
	 * Main method of SpecialPage. Called by Framwork.
	 *
	 * @param string|false $par string or false, provided by Framework
	 */
	public function execute( $par ): void {
		$output = $this->getOutput();

		$this->setHeaders();
		$output->setPageTitle( $this->msg( 'heading_list_authors' )->escaped() );
		$pager = new BibManagerPagerListAuthors();
		$sDataBody = $pager->getBody();
		if ( !empty( $sDataBody ) ) {
			$output->addHTML( $pager->getNavigationBar() );
			$table = [];
			$table[] = '	<table class="wikitable" style="width:100%;">';
			$table[] = '		<tr>';
			$table[] = '			<th >' . $this->msg( 'bm_list_author_table_heading-author' )->escaped() . '</th>';
			// phpcs:ignore Generic.Files.LineLength.TooLong
			$table[] = '			<th style="width: 100px;">' . $this->msg( 'bm_list_author_table_heading-amount' )->escaped() . '</th>';
			$table[] = '		</tr>';
			$table[] = $sDataBody;
			$table[] = '	</table>';

			$output->addHTML( implode( "\n", $table ) );
			$output->addHTML( $pager->getNavigationBar() );
		} else {
			$output->addHtml( $this->msg( 'bm_error_no-data-found' )->escaped() );
		}
	}

	public function submitForm( array $formData ): bool {
		return false;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
