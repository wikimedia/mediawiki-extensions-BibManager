<?php

class BibManagerPagerListAuthors extends AlphabeticPager {

	/**
	 * @return array
	 */
	public function getQueryInfo(): array {
		return [
			'tables' => 'bibmanager',
			'fields' => 'bm_author, count(bm_author)',
			'options' => [ 'GROUP BY' => 'bm_author ASC' ],
		];
	}

	/**
	 * @return string
	 */
	public function getIndexField(): string {
		return 'bm_author';
	}

	/**
	 * @param mixed $row
	 *
	 * @return string
	 * @throws MWException
	 * @global User $wgUser
	 */
	public function formatRow( $row ): string {
		if ( empty( $row->bm_author ) ) {
			return false;
		}
		foreach ( $row as $key => $val ) {
			$aData[$key] = $val;
		}

		$queryParams = "bm_list_search_select=author&bm_list_search_text=" . $aData['bm_author'];
		$sLinkToList = SpecialPage::getTitleFor( 'BibManagerList' )->getLocalURL( $queryParams );

		$sOutput = "";
		$sOutput .= "<tr>";
		$sOutput .= "	<td>";
		$sOutput .= "		<a href='" . $sLinkToList . "'>" . $aData['bm_author'] . "</a>";
		$sOutput .= "	</td>";
		$sOutput .= "	<td style='text-align:center;'>";
		$sOutput .= $aData['count(bm_author)'];
		$sOutput .= "	</td>";
		$sOutput .= "</tr>";

		return $sOutput;
	}

}
