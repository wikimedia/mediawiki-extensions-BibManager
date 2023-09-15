<?php

// HINT: https://www.mediawiki.org/wiki/Manual:Writing_maintenance_scripts

require_once dirname( __DIR__, 3 ) . '/maintenance/Maintenance.php';

class BibManagerImportRepo extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addOption( 'filename', 'The name of the file', true, true );

		$this->requireExtension( 'BibManager' );
	}

	/**
	 * @throws Exception
	 */
	public function execute() {
		$sFilename = $this->getOption( 'filename', 'new_export' );

		if ( !file_exists( $sFilename ) ) {
			echo( "The file '" . $sFilename . "' doesn't exist!" );
			return;
		}
		$sContent = file_get_contents( $sFilename );

		$bibTexParser = new PARSEENTRIES();
		$bibTexParser->loadBibtexString( $sContent );
		$bibTexParser->extractEntries();
		$entries = $bibTexParser->returnArrays();

		$repo = BibManagerRepository::singleton();
		// TODO RBV (18.12.11 15:05): Optimize this
		foreach ( $entries[2] as $entry ) {
			if ( empty( $entry ) ) {
				continue;
			}

			$citation = trim( $entry['bibtexCitation'] );
			// TODO RBV (18.12.11 15:14): This is very similar to BibManagerEdit specialpage. --> encapsulate.
			$entryType = $entry['bibtexEntryType'];
			$typeDefs = BibManagerFieldsList::getTypeDefinitions();
			$entryFields = array_merge(
				$typeDefs[$entryType]['required'], $typeDefs[$entryType]['optional']
			);

			$submittedFields = [];
			foreach ( $entry as $key => $value ) {
				if ( in_array( $key, $entryFields ) ) {
					$submittedFields['bm_' . $key] = $value;
				}
			}
			$existingEntry = $repo->getBibEntryByCitation( $citation );
			if ( empty( $existingEntry ) ) {
				$aInsert[] = [ $citation, $entryType, $submittedFields ];
			} else {
				$aUpdate[] = [ $citation, $entryType, $submittedFields ];
			}
		}
		if ( isset( $aUpdate ) ) {
			foreach ( $aUpdate as $cleanedEntry ) {
				$repo->updateBibEntry( $cleanedEntry[0], $cleanedEntry[1], $cleanedEntry[2] );
			}
		}
		if ( isset( $aInsert ) ) {
			foreach ( $aInsert as $cleanedEntry ) {
				$repo->saveBibEntry( $cleanedEntry[0], $cleanedEntry[1], $cleanedEntry[2] );
			}
		}
		$iCountUpdate = isset( $aUpdate ) ? count( $aUpdate ) : "0";
		$iCountInsert = isset( $aInsert ) ? count( $aInsert ) : "0";
		echo ( "Import complete (" . $iCountUpdate . " updated, " . $iCountInsert . " added)." );
	}
}

$maintClass = BibManagerImportRepo::class;
require_once RUN_MAINTENANCE_IF_MAIN;
