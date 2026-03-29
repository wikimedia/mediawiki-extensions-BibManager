<?php

class SpecialBibManagerExport extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'BibManagerExport' );
	}

	/**
	 * Main method of SpecialPage. Called by framework.
	 *
	 * @param string|false $par string or false, provided by Framework
	 * @throws Exception
	 */
	public function execute( $par ): void {
		$request = $this->getRequest();
		// TODO RBV (17.12.11 16:46): This is very similar to the BibManagerEdit SpecialPage --> encapsulate logic

		$givenValues = $request->getArray( 'cit', [] );
		$out = '';
		foreach ( $givenValues as $citation ) {
			$citation = str_replace( '__dot__', '.', $citation );
			$entry = BibManagerRepository::singleton()->getBibEntryByCitation( $citation );
			if ( empty( $entry ) ) {
				continue;
			}
			$lines = [];
			$lines['entryType'] = $entry['bm_bibtexEntryType'];
			$lines['cite'] = $entry['bm_bibtexCitation'];
			$typeDefs = BibManagerFieldsList::getTypeDefinitions();
			// TODO RBV (17.12.11 15:01): encapsulte in BibManagerFieldsList
			$entryFields = array_merge(
				$typeDefs[$lines['entryType']]['required'], $typeDefs[$lines['entryType']]['optional']
			);

			foreach ( $entryFields as $fieldName ) {
				$value = $entry['bm_' . $fieldName];
				if ( empty( $value ) ) {
					continue;
				}
				$lines[$fieldName] = $value;
			}

			$bibtex = new Structures_BibTex();
			$bibtex->setOption( "extractAuthors", false );
			$bibtex->addEntry( $lines );
			$out .= $bibtex->bibTex();
		}

		$this->getOutput()->disable();
		wfResetOutputBuffers();

		$filename = 'export-' . date( 'Y-m-d_H-i-s' ) . '.bib';
		$request->response()->header( "Content-type: application/x-bibtex; charset=utf-8" );
		$request->response()->header( "Content-disposition: attachment;filename={$filename}" );

		echo $out;
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'bibmanager';
	}
}
