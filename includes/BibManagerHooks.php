<?php

class BibManagerHooks {

	/**
	 * Default MW-Importer (for MW <=1.16 and MW >= 1.17)
	 * @param Updater $updater
	 * @return boolean true if alright
	 */
	public static function onLoadExtensionSchemaUpdates ( $updater = null ) {
		if ( $updater === null ) {
			// <= 1.16 support
			global $wgExtNewTables, $wgExtModifiedFields;
			$wgExtNewTables[] = array (
			    'bibmanager',
			    dirname( dirname( __FILE__ ) ) . '/BibManager.sql'
			);
		} else {
			// >= 1.17 support
			$updater->addExtensionUpdate(
			    array (
				'addTable',
				'bibmanager',
				dirname( dirname( __FILE__ ) ) . '/BibManager.sql',
				true
			    )
			);
		}
		return true;
	}

	/**
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return bool Always true to keep hook running
	 */
	public static function onBeforePageDisplay ( &$out, &$skin ) {
		global $wgBibManagerUseJS, $wgScriptPath;
		if ( $wgBibManagerUseJS == false )
			return true;
		if ( $out->hasHeadItem( 'BibManager' ) )
			return true; //prevent double loading

		$out->addExtensionStyle(
		    htmlspecialchars( $wgScriptPath . '/extensions/BibManager/client/BibManagerCommon.css' )
		);

		global $wgJsMimeType;
		if ( $out->getTitle()->isSpecial( 'BibManagerEdit' )
		    || $out->getTitle()->isSpecial( 'BibManagerCreate' ) ) {
			$encJsFile = htmlspecialchars( $wgScriptPath . '/extensions/BibManager/client/BibManagerEdit.js' );
			$head = '<script type="' . $wgJsMimeType . '" src="' . $encJsFile . '"></script>';
			$out->addHeadItem( 'BibManager', $head );
		}

		if ( $out->hasHeadItem( 'BibManagerList' ) )
			return true;
		if ( $out->getTitle()->isSpecial( 'BibManagerList' ) ) {
			$encJsFile = htmlspecialchars( $wgScriptPath . '/extensions/BibManager/client/BibManagerList.js' );
			$head = '<script type="' . $wgJsMimeType . '" src="' . $encJsFile . '"></script>';
			$out->addHeadItem( 'BibManagerList', $head );
		}
		return true;
	}

	/**
	 * Init-method for the BibManager-Hooks
	 * @param Parser $parser
	 * @return bool Always true to keep hooks running
	 */
	public static function onParserFirstCallInit ( &$parser ) {
		$parser->setHook( 'bib', 'BibManagerHooks::onBibTag' );
		$parser->setHook( 'biblist', 'BibManagerHooks::onBiblistTag' );
		$parser->setHook( 'bibprint', 'BibManagerHooks::onBibprintTag' );
		return true;
	}

	/**
	 * Method for the BibManager-Tag <bib id='citation' />
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return String the link to the Bib-Cit entered by id
	 */
	public static function onBibTag ( $input, $args, $parser, $frame ) {
		global $wgUser;
		global $wgBibManagerCitationArticleNamespace;
		$parser->disableCache();
		if ( !isset( $args['id'] ) )
			return '[' . wfMsg( 'bm_missing-id' ) . ']';

		$entry = BibManagerRepository::singleton()
		    ->getBibEntryByCitation( $args['id'] );

		$sTooltip = '';
		$sLink = '';
		if ( empty( $entry ) ) {
			$spTitle = SpecialPage::getTitleFor( 'BibManagerCreate' );
			$sLink = Linker::link(
			    $spTitle, $args['id'], array ( 'class' => 'new' ), array ( 'bm_bibtexCitation' => $args['id'] ), array ( 'broken' => true )
			);
			$sTooltip = '<span>' . wfMsg('bm_error_not-existing');
			if ($wgUser->isAllowed('bibmanagercreate')){
				$sLinkToEdit = SpecialPage::getTitleFor( 'BibManagerCreate' )->getLocalURL( array ( 'bm_bibtexCitation' => $args['id'] ));
				$sTooltip .= XML::element("a", array("href" => $sLinkToEdit), wfMsg( 'bm_tag_click_to_create' ));
			}
			$sTooltip .= '</span>';
		} else {
			$oCitationTitle = Title::newFromText( $args['id'], $wgBibManagerCitationArticleNamespace );
			$sLink = Linker::link( $oCitationTitle, $oCitationTitle->getText(), array ( 'title' => '' ) );
			$sTooltip = self::getTooltip( $entry, $args );
		}
		return '<span class="bibmanager-citation">[' . $sLink . ']' . $sTooltip . '</span>';
	}

	public static function getTooltip ( $entry, $args ) {
		$typeDefs = BibManagerFieldsList::getTypeDefinitions();
		$entryTypeFields = array_merge(
		    $typeDefs[$entry['bm_bibtexEntryType']]['required'], $typeDefs[$entry['bm_bibtexEntryType']]['optional']
		);

		$tooltip = array ( );
		wfRunHooks( 'BibManagerBibTagBeforeTooltip', array ( &$entry ) );
		foreach ( $entry as $key => $value ) {
			$unprefixedKey = substr( $key, 3 );
			if ( empty( $value ) || !in_array( $unprefixedKey, $entryTypeFields ) )
				continue; //Filter unnecessary fields
			if ( $unprefixedKey == 'author' ) {
				$value = implode( '; ', explode( ' and ', $value ) ); // TODO RBV (22.12.11 15:34): Duplicate code!
			}
			$tooltip[] = XML::element( 'strong', null, wfMsg( $key ) . ': ' ) . ' '
			    . XML::element( 'em', null, $value )
			    ."<br/>";//. XML::element( 'br', null, null ); //This is just a little exercise
		}

		$tooltip[] = self::getIcons( $entry );
		$tooltipString = implode( "", $tooltip );
		$tooltipString = '<span>' . $tooltipString . '</span>';

		if ( isset( $args['mode'] ) && $args['mode'] == 'full' ) {
			$format = self::formatEntry( $entry );
			$tooltipString = ' ' . $format . ' ' . $tooltipString;
		}
		return $tooltipString;
	}

	public static function getIcons ( $entry ) {
		global $wgScriptPath, $wgUser;
		global $wgBibManagerScholarLink;
		$icons = array ( );

		if ( !empty( $entry['bm_bibtexCitation'] ) && $wgUser->isAllowed('bibmanageredit') ) {
			$icons['edit'] = array (
			    'src' => $wgScriptPath . '/extensions/BibManager/client/images/pencil.png',
			    'title' => 'bm_tooltip_edit',
			    'href' => SpecialPage::getTitleFor( 'BibManagerEdit' )
				->getLocalURL( array ( 'bm_bibtexCitation' => $entry['bm_bibtexCitation'] ) )
			);
		}
		$scholarLink = str_replace( '%title%', $entry['bm_title'], $wgBibManagerScholarLink );
		$icons['scholar'] = array (
		    'src' => $wgScriptPath . '/extensions/BibManager/client/images/book.png',
		    'title' => 'bm_tooltip_scholar',
		    'href' => $scholarLink
		);

		wfRunHooks( 'BibManagerGetIcons', array ( $entry, &$icons ) );

		$out = array ( );
		foreach ( $icons as $key => $iconDesc ) {
			$text = wfMsg( $iconDesc['title'] );
			$iconEl = XML::element(
				'img', array (
				'src' => $iconDesc['src'],
				'alt' => $text,
				'title' => $text
				)
			);
			$anchorEl = XML::tags(
				'a', array (
				'href' => $iconDesc['href'],
				'title' => $text,
				'target' => '_blank',
				), $iconEl
			);
			$out[] = XML::wrapClass( $anchorEl, 'bm_icon_link' );
		}

		return implode( '', $out );
	}

	/**
	 * Method for the BibManager-Tag <biblist />
	 * @param String $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string List of used <bib />-tags
	 */
	public static function onBiblistTag ( $input, $args, $parser, $frame ) {
		global $wgTitle, $wgUser;
		global $wgBibManagerCitationArticleNamespace;
		$parser->disableCache();
		if ( $wgTitle === null )
			return '';

		$article = new Article( $wgTitle );
		$content = $article->fetchContent();

		$out = array ( );
		$out[] = XML::element( 'hr', null, null );
		$out[] = wfMsg( 'bm_tag_tag-used' );

		$bibTags = array ( );
		preg_match_all( '<bib.*?id=[\'"\ ]*(.*?)[\'"\ ].*?>', $content, $bibTags ); // TODO RBV (10.11.11 13:31): It might be better to have a db table for wikipage <-> citation relationship. This table could be updated in bib-Tag callback.
		if ( empty( $bibTags[0][0] ) )
			return wfMsg( 'bm_tag_no-tags-used' ); //No Tags found
		$entries = array ( );
		$repo = BibManagerRepository::singleton();

		natsort( $bibTags[1] ); // TODO RBV (23.12.11 13:27): Customizable sorting?

		foreach ( $bibTags[1] as $citation ) {
			// TODO RBV (10.11.11 13:14): This is not good. If a lot of citations every citation will cause db query.
			$entries[$citation] = $repo->getBibEntryByCitation( $citation );
		}

		//$out[] = XML::openElement( 'table', array ( 'class' => 'bm_list_table' ) );

		// TODO RBV (23.12.11 13:28): Remove filtering
		if ( isset( $args['filter'] ) ) {
			$filterValues = explode( ',', $args['filter'] );
			foreach ( $filterValues as $val ) {
				$temp = explode( ':', trim( $val ) );
				$filter [$temp[0]] = $temp[1];
			}
		}

		$out = self::getTable($entries);

		return $out;

		//HINT: Maybe better way to find not existing entries after a _single_ db call.
		/*
		  $aMissingCits = array_diff(self::$aBibTagUsed, $aFoundCits);
		  foreach ($aMissingCits as $sCit){
		  $aOut [$sCit] = "<li><a href='".SpecialPage::getTitleFor("BibManagerCreate")->getLocalURL()."' >[".$sCit."]</a> (".wfMsg('bm_error_not-existing').")</li>";
		  }
		 */
	}

	/**
	 * Method for the BibManager-Tag <bibprint />
	 * @global object $wgScript
	 * @param String $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function onBibprintTag ( $input, $args, $parser, $frame ) {
		global $wgUser;
		global $wgBibManagerCitationArticleNamespace;
		$parser->disableCache();

		if ( !isset( $args['filter'] ) && !isset($args['citation'] ))
			return '[' . wfMsg( 'bm_missing-filter' ) . ']';
		$repo = BibManagerRepository::singleton();
		if (isset($args['citation'])){
			$res [] = $repo->getBibEntryByCitation($args['citation']);
		}
		else {
			$filters = explode( ',', trim( $args['filter'] ) );
			$fieldsDefs = BibManagerFieldsList::getFieldDefinitions();
			$validFieldNames = array_keys( $fieldsDefs );
			$conds = array ( );
			foreach ( $filters as $val ) {
				$keyValuePairs = explode( ':', trim( $val ), 2 );
				if ( count( $keyValuePairs ) == 1 )
					continue; //No ':' included, so we skip it.

				$key = $keyValuePairs[0];
				if ( !in_array( $key, $validFieldNames ) )
					continue; //No valid DB field, so skip it.

				$values = explode( '|', $keyValuePairs[1] );
				$tmpCond = array ( );
				foreach ( $values as $value ) {
					$tmpCondPart = 'bm_' . $key . ' ';
					if ( strpos( $value, '%' ) !== false ) { //Truncating? We need a "LIKE"
						$tmpCondPart .= 'LIKE "' . $value . '"';
					} else {
						$tmpCondPart .= '= "' . $value . '"';
					}
					$tmpCond[] = $tmpCondPart;
				}

				$conds[] = implode( ' OR ', $tmpCond );
			}
			if ( empty( $conds ) )
				return '[' . wfMsg( 'bm_invalid-filter' ) . ']';

			$res = $repo->getBibEntries( $conds );
		}
		$out = self::getTable($res);
		return $out;
	}

	public static function onSkinAfterContent ( &$data ) {
		$data .= self::onBiblistTag( null, null, null, null );
		return true;
	}

	public static function formatEntry ( $entry, $formatOverride = '', $prefixedKeys = true ) {
		global $wgBibManagerCitationFormats;
		$format = $wgBibManagerCitationFormats['-']; //Use default
		if ( isset( $entry['bm_bibtexEntryType'] ) && !empty( $wgBibManagerCitationFormats[$entry['bm_bibtexEntryType']] ) ) {
			$format = !empty( $formatOverride ) ? $formatOverride : $wgBibManagerCitationFormats[$entry['bm_bibtexEntryType']];
		}

		foreach ( $entry as $key => $value ) { //Replace placeholders
			if ( empty( $value ) )
				continue;

			if ( $prefixedKeys )
				$key = substr( $key, 3 ); //'bm_title' --> 'title'

			if ( $key == 'author' || $key == 'editor' )
				$value = implode( '; ', explode( ' and ', $value ) );

			if ( $key == 'editor' )
				$value .= wfMsg( 'bm_editor_addition' );

			if ( $key == 'url' ) {
				$urlKey = $prefixedKeys ? 'bm_url' : 'url';
				$value = ' '.XML::element(
					'a',
					array(
						'href'   => $entry[$urlKey],
						'target' => '_blank',
						'class'  => 'external',
						'rel'    => 'nofollow'
					),
					wfMsg( 'bm_url')
				);
			}

			$format = str_replace( '%' . $key . '%', $value, $format );
		}

		wfRunHooks( 'BibManagerFormatEntry', array ( $entry, $prefixedKeys, &$format ) );
		return $format;
	}

	public static function getTableEntry($citLink, $citFormat, $citIcons){
		$out = '';
		$out .= XML::openElement( 'tr' );
		$out .= '<td style="width:100px; text-align: left; vertical-align: top;">[' . $citLink . ']</td>';
		$out .= '<td>' . $citFormat . '</td>';
		$out .= '<td style="width:70px">' . $citIcons . '</td>';
		$out .= XML::closeElement( 'tr' );
		return $out;
	}

	public static function getTable($res){
		global $wgUser, $wgBibManagerCitationArticleNamespace;
		$out = XML::openElement( 'table', array ( 'class' => 'bm_list_table' ) );
		if ( $res === false )
			return '[' . wfMsg( 'bm_no-data-found' ) . ']';
		foreach ( $res as $key=>$val ) {
			if (empty($val)){
				$spTitle = SpecialPage::getTitleFor( 'BibManagerCreate' ); // TODO RBV (10.11.11 13:50): Dublicate code --> encapsulate
				$citLink = Linker::link(
				    $spTitle, $key, array ( 'class' => 'new' ), array ( 'bm_bibtexCitation' => $key ), array ( 'broken' => true )
				);
				$sLinkToEdit = SpecialPage::getTitleFor( 'BibManagerCreate' )->getLocalURL( array ( 'bm_bibtexCitation' => $key ));
				$citFormat = '<em>' . wfMsg('bm_error_not-existing');
				if ($wgUser->isAllowed('bibmanagercreate'))
					$citFormat .= XML::element('a', array('href' => $sLinkToEdit), wfMsg( 'bm_tag_click_to_create' ));
				$citFormat .='</em>';
				$citIcons = '';
			}
			else {
				$title = Title::newFromText( $val['bm_bibtexCitation'], $wgBibManagerCitationArticleNamespace );
				$citLink = Linker::link(
				    $title, $title->getText()
				);
				$citFormat = self::formatEntry( $val );
				$citIcons = self::getIcons( $val );
			}

			$out .= XML::openElement( 'tr' );
			$out .= '<td style="width:100px; text-align: left; vertical-align: top;">[' . $citLink . ']</td>';
			$out .= '<td>' . $citFormat . '</td>';
			$out .= '<td style="width:70px">' . $citIcons . '</td>';
			$out .= XML::closeElement( 'tr' );
		}
		$out .= XML::closeElement( 'table' );
		return $out;
	}
}
