<?php

// HINT: https://www.mediawiki.org/wiki/Manual:Writing_maintenance_scripts
use MediaWiki\MediaWikiServices;

require_once dirname( __DIR__, 3 ) . '/maintenance/Maintenance.php';

class BibManagerExportRepo extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addOption( 'filename', 'The name of the file', true, true );
		$this->requireExtension( 'BibManager' );
	}

	/**
	 * @throws Exception
	 */
	public function execute() {
		$sFilename     = $this->getOption( 'filename', 'new_export' );
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$res = $dbr->select(
			'bibmanager', 'bm_bibtexCitation'
		);
		$aReturn = [];
		foreach ( $res as $row ) {
			$aReturn[] = (array)$row;
		}

		$sOutput = "";
		foreach ( $aReturn as $sCitation ) {
			$entry = BibManagerRepository::singleton()->getBibEntryByCitation( $sCitation );
			if ( empty( $entry ) ) {
				continue;
			}
			$entryType = $entry['bm_bibtexEntryType'];
			$typeDefs = BibManagerFieldsList::getTypeDefinitions();
			// TODO RBV (17.12.11 15:01): encapsulte in BibManagerFieldsList
			$entryFields = array_merge(
				$typeDefs[$entryType]['required'], $typeDefs[$entryType]['optional']
			);
			$lines = [];
			$lines[] = "\t" . $entry['bm_bibtexCitation'];
			foreach ( $entryFields as $fieldName ) {
				$value = $entry['bm_' . $fieldName];
				if ( empty( $value ) ) {
					continue;
				}
				$lines[] = "\t" . $fieldName . ' = {' . $value . '}';
			}
			$sOutput .= '@' . $entryType . "{\n" . implode( ",\n", $lines ) . "\n}\n\n";
		}

		file_put_contents( $sFilename, $sOutput );
		if ( file_exists( $sFilename ) ) {
			echo ( "Export successful!" );
		} else {
			echo ( "Failed exporting." );
		}
	}
}

$maintClass = BibManagerExportRepo::class;
require_once RUN_MAINTENANCE_IF_MAIN;
