<?php

use MediaWiki\MediaWikiServices;

class BibManagerLocalMWDatabaseRepo extends BibManagerRepository {

	/**
	 * @param string $sCitation
	 *
	 * @return string
	 */
	public function getCitationsLike( $sCitation ): string {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$res = $dbr->select(
			'bibmanager',
			'bm_bibtexCitation',
			[
				'bm_bibtexCitation '
				. $dbr->buildLike( $sCitation, $dbr->anyString() )
			]
		);

		if ( $res->numRows() > 0 ) {
			$aExistingCitations = [];
			foreach ( $res as $row ) {
				$aExistingCitations[] = $row->bm_bibtexCitation;
			}
			return wfMessage(
				'bm_error_citation_exists',
				implode( ',', $aExistingCitations ),
				$sCitation . 'X'
			)->escaped();
		}

		return true;
	}

	/**
	 * @param mixed $mOptions
	 *
	 * @return mixed
	 */
	public function getBibEntries( $mOptions ) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$res = $dbr->select(
			'bibmanager', '*', $mOptions
		);
		$aReturn = [];
		foreach ( $res as $row ) {
			$aReturn[] = (array)$row;
		}
		if ( !empty( $aReturn ) ) {
			return $aReturn;

		} else {
			return false;
		}
	}

	public function getBibEntryByCitation( $sCitation ): array {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$res = $dbr->selectRow(
			'bibmanager',
			'*',
			[
				'bm_bibtexCitation' => $sCitation
			]
		);
		if ( $res === false ) {
			return [];
		}

		return (array)$res;
	}

	public function saveBibEntry( $sCitation, $sEntryType, $aFields ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		return $dbw->insert(
			'bibmanager',
			$aFields + [
				'bm_bibtexEntryType' => $sEntryType,
				'bm_bibtexCitation' => $sCitation
			]
		);
	}

	public function updateBibEntry( $sCitation, $sEntryType, $aFields ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		return $dbw->update(
			'bibmanager',
			$aFields + [
				'bm_bibtexEntryType' => $sEntryType
			],
			[
				'bm_bibtexCitation' => $sCitation
			]
		);
	}

	public function deleteBibEntry( $sCitation ): bool {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		return $dbw->delete(
			'bibmanager',
			[
				'bm_bibtexCitation' => $sCitation
			]
		);
	}
}
