<?php
/*
  (c) Tobias Weichart, Robert Vogel, Hallo Welt! Medienwerkstatt GmbH, 2011 GPL

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
  http://www.gnu.org/copyleft/gpl.html
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo 'To install BibManager, put the following line in LocalSettings.php: '
	.'include_once( "$IP/extensions/BibManager/BibManager.php" );' . "\n";
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array (
    'path'           => __FILE__,
    'name'           => 'BibManager',
    'author'         => array (
		'Hornemann Institut', 'Hallo Welt! Medienwerkstatt GmbH',
		'Tobias Weichart', 'Robert Vogel'
	),
    'url'            => 'http://www.mediawiki.org/wiki/Extension:BibManager',
    'version'        => '1.1.0',
    'descriptionmsg' => 'bibmanager-desc',
);

$dir = dirname( __FILE__ ) . '/';

//Register classes
$wgAutoloadClasses['Structures_BibTex'] = $dir . 'lib/Structures_BibTex/BibTex.php'; //External library for parsing

$wgAutoloadClasses['BibManagerHooks']               = $dir . 'includes/BibManagerHooks.php';
$wgAutoloadClasses['BibManagerFieldsList']          = $dir . 'includes/BibManagerFieldsList.php';
$wgAutoloadClasses['BibManagerLocalMWDatabaseRepo'] = $dir . 'includes/BibManagerLocalMWDatabaseRepo.php';
$wgAutoloadClasses['BibManagerPagerList']           = $dir . 'includes/BibManagerPagerList.php';
$wgAutoloadClasses['BibManagerPagerListAuthors']    = $dir . 'includes/BibManagerPagerListAuthors.php';
$wgAutoloadClasses['BibManagerRepository']          = $dir . 'includes/BibManagerRepository.php';
$wgAutoloadClasses['BibManagerValidator']           = $dir . 'includes/BibManagerValidator.php';
$wgAutoloadClasses['BibManagerLocalMWDatabaseRepo'] = $dir . 'includes/BibManagerLocalMWDatabaseRepo.php';
$wgAutoloadClasses['BibManagerRepository']          = $dir . 'includes/BibManagerRepository.php';

//SpecialPages
$wgAutoloadClasses['BibManagerList']        = $dir . 'specialpages/BibManagerList_body.php';
$wgAutoloadClasses['BibManagerListAuthors'] = $dir . 'specialpages/BibManagerListAuthors_body.php';
$wgAutoloadClasses['BibManagerImport']      = $dir . 'specialpages/BibManagerImport_body.php';
$wgAutoloadClasses['BibManagerExport']      = $dir . 'specialpages/BibManagerExport_body.php';
$wgAutoloadClasses['BibManagerCreate']      = $dir . 'specialpages/BibManagerCreate_body.php';
$wgAutoloadClasses['BibManagerDelete']      = $dir . 'specialpages/BibManagerDelete_body.php';
$wgAutoloadClasses['BibManagerEdit']        = $dir . 'specialpages/BibManagerEdit_body.php';

//Add I18N
$wgMessagesDirs['BibManager'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['BibManager'] = $dir . 'BibManager.i18n.php';
$wgExtensionMessagesFiles['BibManagerAlias']  = $dir . 'BibManager.alias.php';

//Add SpecialPages
$wgSpecialPages['BibManagerList']        = 'BibManagerList';
$wgSpecialPages['BibManagerListAuthors'] = 'BibManagerListAuthors';
$wgSpecialPages['BibManagerImport']      = 'BibManagerImport';
$wgSpecialPages['BibManagerExport']      = 'BibManagerExport';
$wgSpecialPages['BibManagerDelete']      = 'BibManagerDelete';
$wgSpecialPages['BibManagerCreate']      = 'BibManagerCreate';
$wgSpecialPages['BibManagerEdit']        = 'BibManagerEdit';

//Add SpecialPages to group
$wgSpecialPageGroups['BibManagerList']        = 'BibManager';
$wgSpecialPageGroups['BibManagerListAuthors'] = 'BibManager';
$wgSpecialPageGroups['BibManagerImport']      = 'BibManager';
$wgSpecialPageGroups['BibManagerExport']      = 'BibManager';
$wgSpecialPageGroups['BibManagerDelete']      = 'BibManager';
$wgSpecialPageGroups['BibManagerCreate']      = 'BibManager';
$wgSpecialPageGroups['BibManagerEdit']        = 'BibManager';

//Add Hookhandler
$wgHooks['ParserFirstCallInit'][] = 'BibManagerHooks::onParserFirstCallInit';
$wgHooks['BeforePageDisplay'][]   = 'BibManagerHooks::onBeforePageDisplay';
//$wgHooks['SkinAfterContent'][]    = 'BibManagerHooks::onSkinAfterContent';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'BibManagerHooks::onLoadExtensionSchemaUpdates'; //Register SQL File

//Config Variables
$wgBibManagerUseJS = true;
$wgBibManagerRepoClass = 'BibManagerLocalMWDatabaseRepo';
$wgBibManagerCitationFormats = array (
    '-'             => '%author%: %title%, %year%',
    'article'       => '%author% (%year%): %title%. <em>%journal%</em>, %volume%, %pages%',
    'book'          => '%author% (%year%): %title%. (%edition%). <em>%publisher%</em>, %address%, %pages%',
    'booklet'       => '%title%',
    'conference'    => '%author% (%year%): %title%. %booktitle%',
    'inbook'        => '%author% (%year%): %title%. (%edition%). <em>%publisher%</em>, %address%, %pages%, %editor%, %chapter%',
    'incollection'  => '%author% (%year%): %title%.  %booktitle%',
    'inproceedings' => '%author% (%year%): %title%. <em>%publisher%</em>, %booktitle%',
    'manual'        => '%title%',
    'mastersthesis' => '%author% (%year%): %title%. %school%',
    'misc'          => '%author%: %title%, %year%',
    'phdthesis'     => '%author% (%year%): %title%. %school%',
    'proceedings'   => '%title% (%year%)',
    'techreport'    => '%author% (%year%): %title%. %institution%.',
    'unpublished'   => '%author%: %title%. %note%.'
);

//$wgBibManagerSkinAfterContentBibListConfig = array( 'hr' => true, 'hideonemptylist' => true ); //For future use.
$wgBibManagerCitationArticleNamespace = NS_MAIN;
$wgBibManagerScholarLink = 'http://scholar.google.com/scholar?q=%title%';

// Basic permissions
$wgGroupPermissions['sysop']['bibmanageredit']   = true;
$wgGroupPermissions['sysop']['bibmanagerdelete'] = true;
$wgGroupPermissions['sysop']['bibmanagercreate'] = true;
unset( $dir );
