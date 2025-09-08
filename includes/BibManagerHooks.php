<?php

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class BibManagerHooks {

	/**
	 * Default MW-Importer (for MW <=1.16 and MW >= 1.17)
	 *
	 * @param DatabaseUpdater|null $updater
	 * @return bool true if alright
	 */
	public static function onLoadExtensionSchemaUpdates( ?DatabaseUpdater $updater = null ): bool {
		$updater->addExtensionUpdate( [
			'addTable',
			'bibmanager',
			dirname( __DIR__ ) . '/maintenance/bibmanager.sql',
			true
		] );

		return true;
	}

	/**
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 * @return bool Always true to keep hook running
	 *
	 * @throws MWException
	 */
	public static function onBeforePageDisplay( &$out, &$skin ): bool {
		if ( $out->getTitle()->equals( SpecialPage::getTitleFor( 'BibManagerList' ) ) ) {
			$out->addModules( 'ext.bibManager.List' );
		}

		return true;
	}

	/**
	 * Init-method for the BibManager-Hooks
	 * @param Parser &$parser
	 * @return bool Always true to keep hooks running
	 *
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( &$parser ): bool {
		$parser->setHook( 'bib', 'BibManagerHooks::onBibTag' );
		$parser->setHook( 'biblist', 'BibManagerHooks::onBiblistTag' );
		$parser->setHook( 'bibprint', 'BibManagerHooks::onBibprintTag' );

		return true;
	}

	/**
	 * Method for the BibManager-Tag <bib id='citation' />
	 *
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string the link to the Bib-Cit entered by id
	 *
	 * @throws MWException
	 */
	public static function onBibTag( ?string $input, array $args, Parser $parser, PPFrame $frame ): string {
		global $wgBibManagerCitationArticleNamespace;

		$parser->getOutput()->updateCacheExpiry( 0 );
		if ( !isset( $args['id'] ) ) {
			return '[' . wfMessage( 'bm_missing-id' )->escaped() . ']';
		}
		$parser->getOutput()->addModuleStyles( [ 'ext.bibManager.styles' ] );

		$entry = BibManagerRepository::singleton()
			->getBibEntryByCitation( $args['id'] );

		$user = MediaWikiServices::getInstance()
			->getUserFactory()->newFromUserIdentity( $parser->getUserIdentity() );
		if ( empty( $entry ) ) {
			$spTitle = SpecialPage::getTitleFor( 'BibManagerCreate' );
			$sLink = $parser->getLinkRenderer()->makeBrokenLink(
				$spTitle,
				$args['id'],
				[ 'class' => 'new' ],
				[ 'bm_bibtexCitation' => $args['id'] ]
			);
			$sTooltip = '<span>' . wfMessage( 'bm_error_not-existing' )->escaped();
			if ( $user->isAllowed( 'bibmanagercreate' ) ) {

				// DR 15.09.2023 Validate citation format
				$result = true;
				MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerValidateCitation', [
					$args['id'], &$result ]
				);

				$sTooltipText = " ";
				if ( $result !== true ) {
					$sTooltipText .= $result . " ";
				}

				$sLinkToEdit = SpecialPage::getTitleFor( 'BibManagerCreate' )
					->getLocalURL(
						[
							'bm_bibtexCitation' => $args['id']
						]
					);
				$sTooltip .= $sTooltipText . Html::element(
					"a",
					[
						"href" => $sLinkToEdit
					],
					wfMessage( 'bm_tag_click_to_create' )->text()
				);
			}
			$sTooltip .= '</span>';
		} else {
			$oCitationTitle = Title::newFromText(
				$args['id'],
				$wgBibManagerCitationArticleNamespace
			);
			$sLink = $parser->getLinkRenderer()->makeLink(
				$oCitationTitle,
				$args['id'],
				[ 'title' => '' ]
			);
			$sTooltip = self::getTooltip( $entry, $args, $user );
		}

		return '<span class="bibmanager-citation">[' . $sLink . ']' . $sTooltip . '</span>';
	}

	/**
	 * @param array $entry
	 * @param array $args
	 * @param User $user
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getTooltip( array $entry, array $args, User $user ): string {
		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$entryTypeFields = array_merge(
			$typeDefs[$entry['bm_bibtexEntryType']]['required'], $typeDefs[$entry['bm_bibtexEntryType']]['optional']
		);

		$tooltip = [];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerBibTagBeforeTooltip', [ &$entry ] );
		foreach ( $entry as $key => $value ) {
			$unprefixedKey = substr( $key, 3 );
			if ( empty( $value ) || !in_array( $unprefixedKey, $entryTypeFields ) ) {
				// Filter unnecessary fields
				continue;
			}
			if ( $unprefixedKey == 'author' ) {
				// TODO RBV (22.12.11 15:34): Duplicate code!
				$value = implode( '; ', explode( ' and ', $value ) );
			}
			// . Html::element( 'br' );
			$tooltip[] = Html::element( 'strong', [], wfMessage( $key )->text() . ': ' ) . ' '
				. Html::element( 'em', [], $value )
				. "<br/>";
		}

		$tooltip[] = self::getIcons( $entry, $user );
		$tooltipString = implode( "", $tooltip );
		$tooltipString = '<span>' . $tooltipString . '</span>';

		if ( isset( $args['mode'] ) && $args['mode'] == 'full' ) {
			$format = self::formatEntry( $entry );
			$tooltipString = ' ' . $format . ' ' . $tooltipString;
		}

		return $tooltipString;
	}

	/**
	 * @param array $entry
	 * @param User $user
	 * @return string
	 *
	 * @throws MWException
	 */
	public static function getIcons( array $entry, User $user ): string {
		global $wgScriptPath;
		global $wgBibManagerScholarLink;
		$icons = [];

		if ( !empty( $entry['bm_bibtexCitation'] ) && $user->isAllowed( 'bibmanageredit' ) ) {
			$icons['edit'] = [
				'src' => $wgScriptPath . '/extensions/BibManager/resources/images/pencil.png',
				'title' => 'bm_tooltip_edit',
				'href' => SpecialPage::getTitleFor( 'BibManagerEdit' )
				->getLocalURL( [
					'bm_bibtexCitation' => $entry['bm_bibtexCitation'],
					'bm_edit_mode' => 1,
				] )
			];
		}
		$scholarLink = str_replace( '%title%', $entry['bm_title'], $wgBibManagerScholarLink );
		$icons['scholar'] = [
			'src' => $wgScriptPath . '/extensions/BibManager/resources/images/book.png',
			'title' => 'bm_tooltip_scholar',
			'href' => $scholarLink
		];

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerGetIcons', [ $entry, &$icons ] );

		$out = [];
		foreach ( $icons as $iconDesc ) {
			$text = wfMessage( $iconDesc['title'] )->escaped();
			$iconEl = Html::element(
				'img', [
					'src' => $iconDesc['src'],
					'alt' => $text,
					'title' => $text
				]
			);
			$anchorEl = Html::rawElement(
				'a', [
					'href' => $iconDesc['href'],
					'title' => $text,
					'target' => '_blank',
				], $iconEl
			);
			$out[] = Html::rawElement( 'span', [ 'class' => 'bm_icon_link' ], $anchorEl );
		}

		return implode( '', $out );
	}

	/**
	 * Method for the BibManager-Tag <biblist />
	 *
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 *
	 * @return string List of used <bib />-tags
	 * @throws MWException
	 */
	public static function onBiblistTag( ?string $input, array $args, Parser $parser, PPFrame $frame ): string {
		$parser->getOutput()->updateCacheExpiry( 0 );

		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $parser->getTitle() );
		$pageContent = $page->getContent();
		$content = $pageContent->getText();
		$parser->getOutput()->addModuleStyles( [ 'ext.bibManager.styles' ] );

		$out = [];
		$out[] = Html::element( 'hr' );
		$out[] = wfMessage( 'bm_tag_tag-used' )->escaped();

		$bibTags = [];
		// TODO RBV (10.11.11 13:31): It might be better to have a db table for wikipage <-> citation relationship.
		// This table could be updated in bib-Tag callback.
		preg_match_all( '<bib.*?id=[\'"\ ]*(.*?)[\'"\ ].*?>', $content, $bibTags );
		if ( empty( $bibTags[0][0] ) ) {
			// No Tags found
			return wfMessage( 'bm_tag_no-tags-used' )->escaped();
		}
		$entries = [];
		$repo = BibManagerRepository::singleton();

		// TODO RBV (23.12.11 13:27): Customizable sorting?
		natsort( $bibTags[1] );

		foreach ( $bibTags[1] as $citation ) {
			// TODO RBV (10.11.11 13:14): This is not good. If a lot of citations every citation will cause db query.
			$entries[$citation] = $repo->getBibEntryByCitation( $citation );
		}

		// $out[] = Html::openElement( 'table', [ 'class' => 'bm_list_table' ] );

		// TODO RBV (23.12.11 13:28): Remove filtering
		if ( isset( $args['filter'] ) ) {
			$filterValues = explode( ',', $args['filter'] );
			foreach ( $filterValues as $val ) {
				$temp = explode( ':', trim( $val ) );
				$filter[$temp[0]] = $temp[1];
			}
		}

		$user = MediaWikiServices::getInstance()
			->getUserFactory()->newFromUserIdentity( $parser->getUserIdentity() );

		return self::getTable( $entries, $user );

		// HINT: Maybe better way to find not existing entries after a _single_ db call.
		/*
		  $aMissingCits = array_diff(self::$aBibTagUsed, $aFoundCits);
		  foreach ($aMissingCits as $sCit){
		  $aOut [$sCit] = "<li>
			<a href='".SpecialPage::getTitleFor("BibManagerCreate")->getLocalURL()."' >
				[".$sCit."]
			</a> (".wfMessage('bm_error_not-existing')->escaped().")
		</li>";
		  }
		 */
	}

	/**
	 * Method for the BibManager-Tag <bibprint />
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 *
	 * @return string
	 * @throws MWException
	 * @global object $wgScript
	 */
	public static function onBibprintTag( ?string $input, array $args, Parser $parser, PPFrame $frame ): string {
		$parser->getOutput()->updateCacheExpiry( 0 );

		if ( !isset( $args['filter'] ) && !isset( $args['citation'] ) ) {
			return '[' . wfMessage( 'bm_missing-filter' )->escaped() . ']';
		}

		$repo = BibManagerRepository::singleton();
		if ( isset( $args['citation'] ) ) {
			$res[] = $repo->getBibEntryByCitation( $args['citation'] );
		} else {
			$filters = explode( ',', trim( $args['filter'] ) );
			$fieldsDefs = BibManagerFieldsList::getFieldDefinitions();
			$validFieldNames = array_keys( $fieldsDefs );
			$conds = [];
			foreach ( $filters as $val ) {
				$keyValuePairs = explode( ':', trim( $val ), 2 );
				if ( count( $keyValuePairs ) == 1 ) {
					// No ':' included, so we skip it.
					continue;
				}

				$key = $keyValuePairs[0];
				if ( !in_array( $key, $validFieldNames ) ) {
					// No valid DB field, so skip it.
					continue;
				}

				$values = explode( '|', $keyValuePairs[1] );
				$tmpCond = [];
				foreach ( $values as $value ) {
					$tmpCondPart = 'bm_' . $key . ' ';
					// Truncating? We need a "LIKE"
					if ( strpos( $value, '%' ) !== false ) {
						$tmpCondPart .= 'LIKE "' . $value . '"';
					} else {
						$tmpCondPart .= '= "' . $value . '"';
					}
					$tmpCond[] = $tmpCondPart;
				}

				$conds[] = implode( ' OR ', $tmpCond );
			}
			if ( empty( $conds ) ) {
				return '[' . wfMessage( 'bm_invalid-filter' )->escaped() . ']';
			}

			$res = $repo->getBibEntries( $conds );
		}
		$user = MediaWikiServices::getInstance()
			->getUserFactory()->newFromUserIdentity( $parser->getUserIdentity() );

		return self::getTable( $res, $user );
	}

	/**
	 * @param string &$data
	 *
	 * @return bool
	 * @throws MWException
	 */
	public static function onSkinAfterContent( string &$data ): bool {
		$data .= self::onBiblistTag( null, null, null, null );

		return true;
	}

	/**
	 * @param array $entry
	 * @param string $formatOverride
	 * @param bool $prefixedKeys
	 *
	 * @return string
	 */
	public static function formatEntry( array $entry, string $formatOverride = '', bool $prefixedKeys = true ): string {
		global $wgBibManagerCitationFormats;

		// Use default
		$format = $wgBibManagerCitationFormats['article'];
		if ( isset( $entry['bm_bibtexEntryType'] )
			&& !empty( $wgBibManagerCitationFormats[$entry['bm_bibtexEntryType']] ) ) {
			$format = !empty( $formatOverride ) ? $formatOverride
				: $wgBibManagerCitationFormats[$entry['bm_bibtexEntryType']];
		}

		// Replace placeholders
		foreach ( $entry as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			if ( $prefixedKeys ) {
				// 'bm_title' --> 'title'
				$key = substr( $key, 3 );
			}

			if ( $key == 'author' || $key == 'editor' ) {
				$value = implode( '; ', explode( ' and ', $value ) );
			}

			if ( $key == 'editor' ) {
				$value .= wfMessage( 'bm_editor_addition' )->escaped();
			}

			if ( $key == 'url' ) {
				$urlKey = $prefixedKeys ? 'bm_url' : 'url';
				$value = ' ' . Html::element(
					'a',
					[
						'href'   => $entry[$urlKey],
						'target' => '_blank',
						'class'  => 'external',
						'rel'    => 'nofollow'
					],
					wfMessage( 'bm_url' )->text()
				);
			}

			$format = str_replace( '%' . $key . '%', $value, $format );
		}

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerFormatEntry', [
			$entry, $prefixedKeys, &$format
		] );

		return $format;
	}

	/**
	 * @param array|bool $res
	 * @param User $user
	 *
	 * @return string
	 *
	 * @throws MWException
	 */
	public static function getTable( $res, User $user ): string {
		global $wgBibManagerCitationArticleNamespace;

		$out = Html::openElement( 'table', [ 'class' => 'bm_list_table' ] );
		if ( !$res ) {
			return '[' . wfMessage( 'bm_no-data-found' )->escaped() . ']';
		}

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		foreach ( $res as $key => $val ) {
			if ( empty( $val ) ) {
				// TODO RBV (10.11.11 13:50): Dublicate code --> encapsulate
				$spTitle = SpecialPage::getTitleFor( 'BibManagerCreate' );
				$citLink = $linkRenderer->makeBrokenLink(
					$spTitle,
					$key,
					[ 'class' => 'new' ],
					[ 'bm_bibtexCitation' => $key ]
				);
				$sLinkToEdit = SpecialPage::getTitleFor( 'BibManagerCreate' )->getLocalURL( [
					'bm_bibtexCitation' => $key
				] );
				$citFormat = '<em>' . wfMessage( 'bm_error_not-existing' )->escaped();
				if ( $user->isAllowed( 'bibmanagercreate' ) ) {
					// DR 15.09.2023 Validate citation format
					$result = true;

					MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerValidateCitation', [
						$key,
						&$result
					] );

					$sFormatText = " ";
					if ( $result !== true ) {
						$sFormatText .= $result . " ";
					}

					$citFormat .= $sFormatText . Html::element(
						'a',
						[ 'href' => $sLinkToEdit ],
						wfMessage( 'bm_tag_click_to_create' )->text()
					);
				}
				$citFormat .= '</em>';
				$citIcons = '';
			} else {
				$title = Title::newFromText(
					$val['bm_bibtexCitation'],
					$wgBibManagerCitationArticleNamespace
				);
				$citLink = $linkRenderer->makeLink( $title, $val['bm_bibtexCitation'] );
				$citFormat = self::formatEntry( $val );
				$citIcons = self::getIcons( $val, $user );
			}

			$out .= Html::openElement( 'tr' );
			$out .= '<td style="width:100px; text-align: left; vertical-align: top;">[' . $citLink . ']</td>';
			$out .= '<td>' . $citFormat . '</td>';
			$out .= '<td style="width:70px">' . $citIcons . '</td>';
			$out .= Html::closeElement( 'tr' );
		}
		$out .= Html::closeElement( 'table' );

		return $out;
	}
}
