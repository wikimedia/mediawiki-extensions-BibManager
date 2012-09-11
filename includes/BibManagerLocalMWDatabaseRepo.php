<?php

class BibManagerLocalMWDatabaseRepo extends BibManagerRepository {

	public function getCitationsLike ( $sCitation ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
		    'bibmanager', 'bm_bibtexCitation', array ( 'bm_bibtexCitation ' . $dbr->buildLike( $sCitation, $dbr->anyString() ) )
		);

		if ( $dbr->numRows( $res ) > 0 ) {
			$aExistingCitations = array ( );
			foreach ( $res as $row ) {
				$aExistingCitations[] = $row->bm_bibtexCitation;
			}
			return wfMsg( 'bm_error_citation_exists', implode( ',', $aExistingCitations ), $sCitation . 'X' );
		}
		return true;
	}

	/**
	 *
	 * @param mixed $mOptions 
	 * @return mixed
	 */
	public function getBibEntries ( $mOptions ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
		    'bibmanager', '*', $mOptions
		);
		$aReturn = array ( );
		while ( $row = $dbr->fetchRow( $res ) ) {
			$aReturn [] = $row;
		}
		if ( !empty( $aReturn ) )
			return $aReturn;
		else
			return false;
	}

	public function getBibEntryByCitation ( $sCitation ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
		    'bibmanager', '*', array ( 'bm_bibtexCitation' => $sCitation ) //Do we need 'mysql_real_escape_string'?
		);
		if ( $res === false )
			return array ( );
		return (array) $res;
	}

	public function saveBibEntry ( $sCitation, $sEntryType, $aFields ) {
		$dbw = wfGetDB( DB_MASTER );
		return $dbw->insert(
			'bibmanager', $aFields + array ( 'bm_bibtexEntryType' => $sEntryType, 'bm_bibtexCitation' => $sCitation ), __METHOD__, array ( )
		);
	}

	public function updateBibEntry ( $sCitation, $sEntryType, $aFields ) {
		$dbw = wfGetDB( DB_MASTER );
		
		return $dbw->update(
			'bibmanager', $aFields + array ( 'bm_bibtexEntryType' => $sEntryType), array('bm_bibtexCitation' => $sCitation), array()
		);
	}

	public function deleteBibEntry ( $sCitation ) {
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete(
		    'bibmanager', array ( 'bm_bibtexCitation' => $sCitation )
		);

		return $res;
	}

}