<?php

use MediaWiki\MediaWikiServices;

class BibManagerPagerList extends AlphabeticPager {
	/** @var string */
	private $searchType = '';

	/** @var string */
	private $searchTerm = '';

	function getQueryInfo() {
		global $wgRequest;
		$this->searchType = $wgRequest->getVal( 'wpbm_list_search_select', '' );
		$this->searchTerm = $wgRequest->getVal( 'wpbm_list_search_text', '' );
		$conds = [];
		if ( !empty( $this->searchType ) && !empty( $this->searchTerm ) ) {
			$conds[] = "bm_" . $this->searchType . " LIKE '%" . $this->searchTerm . "%'";
		}

		Hooks::run( 'BibManagerPagerBeforeSearch', [ $this->searchType, $this->searchTerm, &$conds ] );
		return [
			'tables'  => 'bibmanager',
			'fields'  => '*',
			'conds'   => $conds,
			'options' => [ 'ORDER BY' => 'bm_bibtexCitation ASC' ],
		];
	}

	function getIndexField() {
		return 'bm_bibtexCitation';
	}

	/**
	 * Override from base class to add query string parameters
	 * @return array
	 */
	function getPagingQueries() {
		$queries = parent::getPagingQueries();
		if ( !empty( $this->searchType ) ) {
			foreach ( $queries as $type => $query ) {
				$queries[$type]['wpbm_list_search_select'] = $this->searchType;
			}
		}
		if ( !empty( $this->searchTerm ) ) {
			foreach ( $queries as $type => $query ) {
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
		$links = [];
		if ( $this->mIsBackwards ) {
			$offset = $this->mPastTheEndIndex;
		} else {
			$offset = $this->mOffset;
		}
		$query = [ 'offset' => $offset ];
		if ( !empty( $this->searchType ) ) {
			$query['wpbm_list_search_select'] = $this->searchType;
		}
		if ( !empty( $this->searchTerm ) ) {
			$query['wpbm_list_search_text'] = $this->searchTerm;
		}

		foreach ( $this->mLimitsShown as $limit ) {
			$links[] = $this->makeLink(
				$wgLang->formatNum( $limit ),
				$query + [ 'limit' => $limit ],
				'num'
			);
		}
		return $links;
	}

	/**
	 * @param mixed $row
	 * @return string
	 */
	function formatRow( $row ) {
		// phpcs:ignore MediaWiki.Usage.ExtendClassUsage.FunctionConfigUsage
		global $wgBibManagerCitationArticleNamespace;

		$citationTitle = Title::newFromText( $row->bm_bibtexCitation, $wgBibManagerCitationArticleNamespace );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$citationLink = $linkRenderer->makeLink( $citationTitle, $row->bm_bibtexCitation );
		$editLink     = '';
		$deleteLink   = '';
		$exportLink   = new OOUI\CheckboxInputWidget( [
			'infusable' => true,
			'name' => 'cit[]',
			'value' => str_replace( '.', '__dot__', $row->bm_bibtexCitation ),
		] );

		$specialPageQuery = [ 'bm_bibtexCitation' => $row->bm_bibtexCitation ];

		$user = $this->getUser();
		if ( $user->isAllowed( 'bibmanageredit' ) ) {
			$editLink = $linkRenderer->makeLink(
				SpecialPage::getTitleFor( 'BibManagerEdit' ),
				$this->msg( 'bm_list_table_edit' )->text(),
				[
					'class' => 'icon edit',
					'title' => $this->msg( 'bm_list_table_edit' )->escaped()
				],
				$specialPageQuery
			);
		}

		if ( $user->isAllowed( 'bibmanagerdelete' ) ) {
			$deleteLink = $linkRenderer->makeLink(
				SpecialPage::getTitleFor( 'BibManagerDelete' ),
				$this->msg( 'bm_list_table_delete' )->text(),
				[
					'class' => 'icon delete',
					'title' => $this->msg( "bm_list_table_delete" )->escaped()
				],
				$specialPageQuery
			);
		}

		$format = BibManagerHooks::formatEntry( (array)$row );

		$tablerow = [];
		$tablerow[] = '<tr>';
		$tablerow[] = '  <td style="vertical-align:top;">' . $citationLink . '</td>';
		$tablerow[] = '  <td>' . $format . '</td>';
		if ( $user->isAllowed( 'bibmanageredit' ) || $user->isAllowed( 'bibmanagerdelete' ) ) {
			$tablerow[] = '  <td style="text-align:center;">' . $editLink . $deleteLink . '</td>';
		}
		$tablerow[] = '  <td style="text-align:center;">' . $exportLink . '</td>';
		$tablerow[] = '<tr>';

		return implode( "\n", $tablerow );
	}
}
