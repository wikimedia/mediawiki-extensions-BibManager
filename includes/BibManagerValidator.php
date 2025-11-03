<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class BibManagerValidator {

	/**
	 * @param string $fieldName
	 * @param string $value
	 * @param array $allData
	 * @return string|true
	 * @throws Exception
	 */
	public static function validateRequired( string $fieldName, string $value, array $allData ) {
		if ( !empty( $value ) ) {
			return true;
		}

		$entryType = $allData['bm_bibtexEntryType'];
		// TODO RBV (17.12.11 13:34): Cache?
		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$currentType = $typeDefs[$entryType];
		if ( in_array( $fieldName, $currentType['required'] ) ) {
			return wfMessage( 'bm_required-field-empty' )->text();
		}

		return true;
	}

	/**
	 * checks if citation exists in database.
	 *
	 * @param string|null $value
	 *
	 * @return string|true
	 * @throws MWException
	 */
	public static function validateCitation( ?string $value ) {
		global $wgBibManagerCitationArticleNamespace;

		if ( empty( $value ) ) {
			return wfMessage( 'bm_required-field-empty' )->text();
		}

		$result = true;
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerValidateCitation', [ $value, &$result ] );

		// HINT: https://www.mediawiki.org/wiki/Help:Bad_title
		$title = Title::newFromText( $value, $wgBibManagerCitationArticleNamespace );
		if ( $title === null ) {
			return wfMessage( 'bm_error_citation_invalid' )->text();
		}

		$repo = BibManagerRepository::singleton();
		if ( $repo->getBibEntries( [ "bm_bibtexCitation" => $value ] ) !== false ) {
			// TODO RBV (18.12.11 15:47): Bad interface!
			// Better get citations by PK and if not empty create error message here!
			return $repo->getCitationsLike( $value );
		}

		return $result;
	}

	/**
	 * checks if the input-string only consists Integers
	 *
	 * @param string $value
	 * @param array $allData
	 * @return string|true true if valid, else error-message
	 */
	public static function validateInt( string $value, array $allData ) {
		if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_INT ) ) {
			return wfMessage( 'bm_wrong-character' )->text();
		}

		return true;
	}

	/**
	 * This is necessary because MW 1.16.x does not validate required fields itself.
	 *
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateAddress( string $value, array $allData ) {
		return self::validateRequired( 'address', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateAnnote( string $value, array $allData ) {
		return self::validateRequired( 'annote', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateAuthor( string $value, array $allData ) {
		$result = self::validateRequired( 'author', $value, $allData );
		// Even if author is required it is sufficient if an editor is provided.
		if ( $result !== true && !empty( $allData['bm_editor'] ) ) {
			$result = true;
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateBooktitle( string $value, array $allData ) {
		return self::validateRequired( 'booktitle', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateChapter( string $value, array $allData ) {
		$result = self::validateRequired( 'chapter', $value, $allData );
		if ( $result === true ) {
			$result = self::validateInt( $value, $allData );
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateCrossref( string $value, array $allData ) {
		// TODO RBV (17.12.11 13:28): validate, hook?
		return self::validateRequired( 'crossref', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateEdition( string $value, array $allData ) {
		return self::validateRequired( 'edition', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateEditor( string $value, array $allData ) {
		$result = self::validateRequired( 'editor', $value, $allData );
		// Even if editor is required it is sufficient if an author is provided.
		if ( $result !== true && !empty( $allData['bm_author'] ) ) {
			$result = true;
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateHowpublished( string $value, array $allData ) {
		return self::validateRequired( 'howpublished', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateInstitution( string $value, array $allData ) {
		return self::validateRequired( 'institution', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateJournal( string $value, array $allData ) {
		return self::validateRequired( 'journal', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateKey( string $value, array $allData ) {
		return self::validateRequired( 'key', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateMonth( string $value, array $allData ) {
		return self::validateRequired( 'month', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateNote( string $value, array $allData ) {
		return self::validateRequired( 'note', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateNumber( string $value, array $allData ) {
		$result = self::validateRequired( 'number', $value, $allData );
		if ( $result === true ) {
			$result = self::validateInt( $value, $allData );
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateOrganization( string $value, array $allData ) {
		return self::validateRequired( 'organization', $value, $allData );
	}

	/**
	 * checks if the input-string is a valid page-type (just alphabetical, Int, plus, : or =)
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validatePages( string $value, array $allData ) {
		$result = self::validateRequired( 'pages', $value, $allData );
		if ( $result === true && !empty( $value ) && !preg_match( "/^[0-9a-zA-Z\:\=-]*$/", $value ) ) {
			$result = wfMessage( 'bm_wrong-character' )->text();
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validatePublisher( string $value, array $allData ) {
		return self::validateRequired( 'publisher', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateSchool( string $value, array $allData ) {
		return self::validateRequired( 'school', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateSeries( string $value, array $allData ) {
		return self::validateRequired( 'series', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateTitle( string $value, array $allData ) {
		return self::validateRequired( 'title', $value, $allData );
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateType( string $value, array $allData ) {
		return self::validateRequired( 'type', $value, $allData );
	}

	/**
	 * checks if the input-string is a valid url (leading http:// required)
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateUrl( string $value, array $allData ) {
		$result = self::validateRequired( 'url', $value, $allData );
		if ( $result === true && !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$result = wfMessage( 'bm_wrong-url-format' )->text();
		}

		return $result;
	}

	/**
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateVolume( string $value, array $allData ) {
		$result = self::validateRequired( 'volume', $value, $allData );
		if ( $result === true ) {
			$result = self::validateInt( $value, $allData );
		}

		return $result;
	}

	/**
	 * checks if the input-string just consists of Integer
	 *
	 * @param string $value
	 * @param array $allData
	 * @return string|bool true if valid, else error-message
	 * @throws Exception
	 */
	public static function validateYear( string $value, array $allData ) {
		$result = self::validateRequired( 'year', $value, $allData );
		if ( !empty( $value ) && !preg_match( "/^[0-9]*$/", $value ) ) {
			$result = wfMessage( 'bm_wrong-character' )->text();
		}

		return $result;
	}
}
