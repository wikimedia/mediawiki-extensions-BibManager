<?php

abstract class BibManagerRepository {

	/** @var null */
	public static $instance = null;

	/**
	 * Singleton factory method.
	 *
	 * @return BibManagerRepository
	 */
	public static function singleton(): BibManagerRepository {
		if ( self::$instance instanceof BibManagerRepository ) {
			return self::$instance;
		}

		global $wgBibManagerRepoClass;
		self::$instance = new $wgBibManagerRepoClass();

		return self::$instance;
	}

	/**
	 * @param mixed $sCitation
	 *
	 * @return array
	 */
	abstract public function getBibEntryByCitation( $sCitation ): array;

	abstract public function getBibEntries( $aOptions );

	abstract public function saveBibEntry( $sCitation, $sEntryType, $aFields );

	abstract public function updateBibEntry( $sCitation, $sEntryType, $aFields );

	/**
	 * @param mixed $sCtiation
	 *
	 * @return string Empty string if okay, otherwise a suggestion (alpha-incremented)
	 */
	abstract public function getCitationsLike( $sCtiation ): string;

	/**
	 * @param mixed $sCitation
	 *
	 * @return bool
	 */
	abstract public function deleteBibEntry( $sCitation ): bool;
}
