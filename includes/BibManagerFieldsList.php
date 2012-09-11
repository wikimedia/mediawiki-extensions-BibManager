<?php

class BibManagerFieldsList {

	public static function getFieldDefinitions () {
		//HINT: http://semantic-mediawiki.org/wiki/Help:BibTeX_format
		$fieldDefinitions = array (
		    'address' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_address' ),
			'validation-callback' => 'BibManagerValidator::validateAddress',
		    ), //Publisher's address (usually just the city, but can be the full address for lesser-known publishers)
		    'annote' => array (
			'class' => 'HTMLTextAreaField',
			'label' => wfMsg( 'bm_annote' ),
			'validation-callback' => 'BibManagerValidator::validateAnnote',
			'rows' => 5
		    ), //An annotation for annotated bibliography styles (not typical)
		    'author' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_author' ),
			'validation-callback' => 'BibManagerValidator::validateAuthor',
		    ), //The name(s) of the author(s) (in the case of more than one author, separated by and)
		    'booktitle' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_booktitle' ),
			'validation-callback' => 'BibManagerValidator::validateBooktitle',
		    ), // The title of the book, if only part of it is being cited
		    'chapter' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_chapter' ),
			'validation-callback' => 'BibManagerValidator::validateChapter',
		    ), // The chapter number
		    'crossref' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_crossref' ),
			'validation-callback' => 'BibManagerValidator::validateCrossref',
		    ), // The key of the cross-referenced entry
		    'edition' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_edition' ),
			'validation-callback' => 'BibManagerValidator::validateEdition',
		    ), // The edition of a book, long form (such as "first" or "second")
		    'editor' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_editor' ),
			'validation-callback' => 'BibManagerValidator::validateEditor',
		    ), // The name(s) of the editor(s)
		    'eprint' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_eprint' ),
			'validation-callback' => 'BibManagerValidator::validateEprint',
		    ), // A specification of an electronic publication, often a preprint or a technical report
		    'howpublished' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_howpublished' ),
			'validation-callback' => 'BibManagerValidator::validateHowpublished',
		    ), // How it was published, if the publishing method is nonstandard
		    'institution' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_institution' ),
			'validation-callback' => 'BibManagerValidator::validateInstitution',
		    ), // The institution that was involved in the publishing, but not necessarily the publisher
		    'journal' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_journal' ),
			'validation-callback' => 'BibManagerValidator::validateJournal',
		    ), // The journal or magazine the work was published in
		    'key' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_key' ),
			'validation-callback' => 'BibManagerValidator::validateKey',
		    ), // A hidden field used for specifying or overriding the alphabetical order of entries (when the "author" and "editor" fields are missing). Note that this is very different from the key (mentioned just after this list) that is used to cite or cross-reference the entry.
		    'month' => array (
			'class' => 'HTMLSelectField',
			'label' => wfMsg( 'bm_month' ),
			'options' => array (
			    '' => '',
			    wfMsg( 'bm_month_jan' ) => 'january',
			    wfMsg( 'bm_month_feb' ) => 'february',
			    wfMsg( 'bm_month_mar' ) => 'march',
			    wfMsg( 'bm_month_apr' ) => 'april',
			    wfMsg( 'bm_month_may' ) => 'may',
			    wfMsg( 'bm_month_jun' ) => 'june',
			    wfMsg( 'bm_month_jul' ) => 'july',
			    wfMsg( 'bm_month_aug' ) => 'august',
			    wfMsg( 'bm_month_sep' ) => 'september',
			    wfMsg( 'bm_month_oct' ) => 'october',
			    wfMsg( 'bm_month_nov' ) => 'november',
			    wfMsg( 'bm_month_dec' ) => 'december',
			),
			'validation-callback' => 'BibManagerValidator::validateMonth',
		    ), // The month of publication (or, if unpublished, the month of creation)
		    'note' => array (
			'class' => 'HTMLTextAreaField',
			'label' => wfMsg( 'bm_note' ),
			'validation-callback' => 'BibManagerValidator::validateNote',
			'rows' => 5
		    ), // Miscellaneous extra information
		    'number' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_number' ),
			'validation-callback' => 'BibManagerValidator::validateNumber',
		    ), // The "(issue) number" of a journal, magazine, or tech-report, if applicable. (Most publications have a "volume", but no "number" field.)
		    'organization' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_organization' ),
			'validation-callback' => 'BibManagerValidator::validateOrganization',
		    ), // The conference sponsor
		    'pages' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_pages' ),
			'validation-callback' => 'BibManagerValidator::validatePages',
		    ), // Page numbers, separated either by commas or double-hyphens.
		    'publisher' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_publisher' ),
			'validation-callback' => 'BibManagerValidator::validatePublisher',
		    ), // The publisher's name
		    'school' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_school' ),
			'validation-callback' => 'BibManagerValidator::validateSchool',
		    ), // The school where the thesis was written
		    'series' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_series' ),
			'validation-callback' => 'BibManagerValidator::validateSeries',
		    ), // The series of books the book was published in (e.g. "The Hardy Boys" or "Lecture Notes in Computer Science")
		    'title' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_title' ),
			'validation-callback' => 'BibManagerValidator::validateTitle',
		    ), // The title of the work
		    'type' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_type' ),
			'validation-callback' => 'BibManagerValidator::validateType',
		    //validate ?
		    ), // The field overriding the default type of publication (e.g. "Research Note" for techreport, "{PhD} dissertation" for phdthesis, "Section" for inbook/incollection)
		    'url' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_url' ),
			'default' => 'http://',
			'validation-callback' => 'BibManagerValidator::validateUrl',
		    ), // The WWW address
		    'volume' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_volume' ),
			'validation-callback' => 'BibManagerValidator::validateVolume',
		    ), // The volume of a journal or multi-volume book
		    'year' => array (
			'class' => 'HTMLTextField',
			'label' => wfMsg( 'bm_year' ),
			'validation-callback' => 'BibManagerValidator::validateYear',
		    ) // The year of publication (or, if unpublished, the year of creation)
		);
		wfRunHooks( 'BibManagerGetFieldDefinitions', array ( &$fieldDefinitions ) );
		return $fieldDefinitions;
	}

	public static function getTypeDefinitions () {
		// TODO RBV (23.12.11 13:36): Make arrays global (label sould be only the language key!): Iterate over them, i18n-ify lables and run hook.
		$typeDefinitions = array (
		    'article' => array (
			'label' => wfMsg( 'bm_entry_type_article' ),
			'required' => array ( 'author', 'title', 'journal', 'year' ),
			'optional' => array ( 'volume', 'number', 'pages', 'month', 'note', 'key', 'url' )
		    ),
		    'book' => array (
			'label' => wfMsg( 'bm_entry_type_book' ),
			'required' => array ( 'author', 'editor', 'title', 'publisher', 'year' ),
			'optional' => array ( 'volume', 'number', 'series', 'address', 'edition', 'month', 'note', 'key', 'url' )
		    ),
		    'booklet' => array (
			'label' => wfMsg( 'bm_entry_type_booklet' ),
			'required' => array ( 'title' ),
			'optional' => array ( 'author', 'howpublished', 'address', 'month', 'year', 'note', 'key' )
		    ),
		    'conference' => array (
			'label' => wfMsg( 'bm_entry_type_conference' ),
			'required' => array ( 'author', 'title', 'booktitle', 'year' ),
			'optional' => array ( 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' )
		    ),
		    'inbook' => array (
			'label' => wfMsg( 'bm_entry_type_inbook' ),
			'required' => array ( 'author', 'editor', 'title', 'chapter', 'pages', 'publisher', 'year' ),
			'optional' => array ( 'volume', 'number', 'series', 'type', 'address', 'edition', 'month', 'note', 'key', 'url' )
		    ),
		    'incollection' => array (
			'label' => wfMsg( 'bm_entry_type_incollection' ),
			'required' => array ( 'author', 'title', 'booktitle', 'publisher', 'year' ),
			'optional' => array ( 'editor', 'volume', 'number', 'series', 'type', 'address', 'chapter', 'pages', 'address', 'edition', 'month', 'note', 'key', 'url' )
		    ),
		    'inproceedings' => array (
			'label' => wfMsg( 'bm_entry_type_inproceedings' ),
			'required' => array ( 'author', 'title', 'booktitle', 'year' ),
			'optional' => array ( 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' )
		    ),
		    'manual' => array (
			'label' => wfMsg( 'bm_entry_type_manual' ),
			'required' => array ( 'title' ),
			'optional' => array ( 'author', 'organization', 'address', 'edition', 'month', 'year', 'note', 'key', 'url' )
		    ),
		    'mastersthesis' => array (
			'label' => wfMsg( 'bm_entry_type_mastersthesis' ),
			'required' => array ( 'author', 'title', 'school', 'year' ),
			'optional' => array ( 'type', 'address', 'month', 'note', 'key', 'url' )
		    ),
		    'misc' => array (
			'label' => wfMsg( 'bm_entry_type_misc' ),
			'required' => array ( ),
			'optional' => array ( 'author', 'title', 'howpublished', 'month', 'year', 'note', 'key', 'url' )
		    ),
		    'phdthesis' => array (
			'label' => wfMsg( 'bm_entry_type_phdthesis' ),
			'required' => array ( 'author', 'title', 'school', 'year' ),
			'optional' => array ( 'type', 'address', 'month', 'note', 'key', 'url' )
		    ),
		    'proceedings' => array (
			'label' => wfMsg( 'bm_entry_type_proceedings' ),
			'required' => array ( 'title', 'year' ),
			'optional' => array ( 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' )
		    ),
		    'techreport' => array (
			'label' => wfMsg( 'bm_entry_type_techreport' ),
			'required' => array ( 'author', 'title', 'institution', 'year' ),
			'optional' => array ( 'type', 'note', 'number', 'address', 'month', 'key', 'url' )
		    ),
		    'unpublished' => array (
			'label' => wfMsg( 'bm_entry_type_unpublished' ),
			'required' => array ( 'author', 'title', 'note' ),
			'optional' => array ( 'month', 'year', 'key', 'url' )
		    )
		);

		wfRunHooks( 'BibManagerGetTypeDefinitions', array ( &$typeDefinitions ) );
		return $typeDefinitions;
	}

}