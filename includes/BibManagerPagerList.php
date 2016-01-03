<?php

class BibManagerPagerList extends AlphabeticPager {
	private $searchType = '';
	private $searchTerm = '';

	function getQueryInfo () {
		global $wgRequest;
		$this->searchType = $wgRequest->getVal( 'wpbm_list_search_select', '' );
		$this->searchTerm = $wgRequest->getVal( 'wpbm_list_search_text', '' );
		$conds= array();
		if ( !empty( $this->searchType ) && !empty( $this->searchTerm ) ) {
			$conds[] = "bm_" . $this->searchType . " LIKE '%" . $this->searchTerm . "%'";
		}

		Hooks::run( 'BibManagerPagerBeforeSearch', array ( $this->searchType, $this->searchTerm, &$conds ) );
		return array (
			'tables'  => 'bibmanager',
			'fields'  => '*',
			'conds'   => $conds,
			'options' => array ( 'ORDER BY' => 'bm_bibtexCitation ASC' ),
		);
	}

	function getIndexField () {
		return 'bm_bibtexCitation';
	}

	/**
	 * Override from base class to add query string parameters
	 * @return array
	 */
	function getPagingQueries() {
		$queries = parent::getPagingQueries();
		if( !empty($this->searchType ) ) {
			foreach( $queries as $type => $query ) {
				$queries[$type]['wpbm_list_search_select'] = $this->searchType;
			}
		}
		if( !empty($this->searchTerm ) ) {
			foreach( $queries as $type => $query ) {
				$queries[$type]['wpbm_list_search_text'] = $this->searchTerm;
			}
		}
		return $queries;
	}

	/**
	 * Override from base class to add query string parameters
	 * @global Language $wgLang
	 * @return array
	 */
	function getLimitLinks() {
		global $wgLang;
		$links = array();
		if ( $this->mIsBackwards ) {
			$offset = $this->mPastTheEndIndex;
		} else {
			$offset = $this->mOffset;
		}
		$query = array( 'offset' => $offset );
		if( !empty($this->searchType ) ) {
			$query['wpbm_list_search_select'] = $this->searchType;
		}
		if( !empty($this->searchTerm ) ){
			$query['wpbm_list_search_text'] = $this->searchTerm;
		}

		foreach ( $this->mLimitsShown as $limit ) {
			$links[] = $this->makeLink(
				$wgLang->formatNum( $limit ),
				$query + array( 'limit' => $limit ),
				'num'
			);
		}
		return $links;
	}

	/**
	 *
	 * @global User $wgUser
	 * @param mixed $row
	 * @return string
	 */
	function formatRow ( $row ) {
		global $wgUser;
		global $wgBibManagerCitationArticleNamespace;

		$citationTitle = Title::newFromText( $row->bm_bibtexCitation, $wgBibManagerCitationArticleNamespace );

		$citationLink = Linker::link( $citationTitle, $row->bm_bibtexCitation );
		$editLink	 = '';
		$deleteLink   = '';
		$exportLink   = Html::input(
			'cit[]',
			str_replace( '.', '__dot__', $row->bm_bibtexCitation ),
			'checkbox'
		);

		$specialPageQuery = array ( 'bm_bibtexCitation' => $row->bm_bibtexCitation );

		if ($wgUser->isAllowed('bibmanageredit')){
			$editLink = Linker::link(
				SpecialPage::getTitleFor( 'BibManagerEdit' ),
				$this->msg( 'bm_list_table_edit' )->escaped(),
				array (
					'class' => 'icon edit',
					'title' => $this->msg( 'bm_list_table_edit' )->escaped()
				),
				$specialPageQuery
			);
		}

		if ($wgUser->isAllowed('bibmanagerdelete')){
			$deleteLink = Linker::link(
				SpecialPage::getTitleFor( 'BibManagerDelete' ),
				$this->msg( 'bm_list_table_delete' )->escaped(),
				array (
					'class' => 'icon delete',
					'title' => $this->msg( "bm_list_table_delete" )->escaped()
				),
				$specialPageQuery
			);
		}

		$format = BibManagerHooks::formatEntry((Array)$row);

		$tablerow = array ( );
		$tablerow[] = '<tr>';
		$tablerow[] = '  <td style="vertical-align:top;">' . $citationLink . '</td>';
		$tablerow[] = '  <td>' . $format . '</td>';
		if ($wgUser->isAllowed('bibmanageredit') || $wgUser->isAllowed('bibmanagerdelete')) {
			$tablerow[] = '  <td style="text-align:center;">' . $editLink . $deleteLink . '</td>';
		}
		$tablerow[] = '  <td style="text-align:center;">' . $exportLink . '</td>';
		$tablerow[] = '<tr>';

		return implode( "\n", $tablerow );
	}
}
