## Hallo Welt! Medienwerkstatt GmbH
## Copyright 2012 by Hallo Welt! Medienwerkstatt GmbH
## http://www.hallowelt.biz

###############################################################################
#                   BibManager Extension for MW 1.16.1                        #
###############################################################################

==Prerequisites==
* Downloaded the extension
* MediaWiki 1.16.1 (might work with other versions, not tested yet)
* Shell access

== Setup ==
* Add the following line to 'LocalSettings.php':
	include_once('extensions/BibManager/BibManager.php');
* Run 'maintenance/update.php' to add the SQL tables

== Configuration ==
You can configure the BibTeX-Requirements for a valid BibTeX-Syntax in 'BibManager/includes/BibManagerFieldsList.php'.
http://de.wikipedia.org/wiki/BibTeX#Literaturtypen_.28Entry_Types.29 exemplified for the default array.

===Config-Variables===
$wgBibManagerUseJS
$wgBibManagerRepoClass
$wgBibManagerCitationFormats
$wgBibManagerCitationArticleNamespace
$wgBibManagerScholarLink

== Tags usage ==
To use one of the three BibManager-Tags proceed as follows:
- include '<bib id="citation" />' in a text to get a link to the Citation you have set up before (e.g. <bib id='testing:2010' /> => points to the WikiPage 'testing:2010')
- include '<biblist />' to list all the <bib>-tags embedded in the text (list in alphabetical order)
- include '<bibprint />' to render full information about a specific citation

== Uninstallation ==
* Remove the BibManager include line from LocalSettings.php.
* Drop the tables in BibManager.sql to free disk space. You can use the following query:

	-- Replace /*_*/ with the proper DB prefix
	DROP TABLE IF EXISTS /*_*/bibmanager;


== Ideas for futher development ==
* Add additional fields like on "http://www.fb10.uni-bremen.de/anglistik/langpro/bibliographies/jacobsen-bibtex.html" on section "Other fields".
* Implement API for remote access and shared repos
* Add a page<->entry relationship table for automatic purging at ernty change and sophisticated querys like "Other pages using this entry"

== Licensing ==
© GPL, see subfolder "docs/"

Icons from famfamfam (http://www.famfamfam.com/) are used within this extension.