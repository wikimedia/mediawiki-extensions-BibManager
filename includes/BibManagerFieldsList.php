<?php

use MediaWiki\MediaWikiServices;

class BibManagerFieldsList {

	/**
	 * @return array[]
	 */
	public static function getFieldDefinitions() {
		// HINT: https://semantic-mediawiki.org/wiki/Help:BibTeX_format
		$fieldDefinitions = [
			'address' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_address' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateAddress',
			], // Publisher's address (usually just the city, but can be the full address for lesser-known publishers)
			'annote' => [
				'class' => 'HTMLTextAreaField',
				'label' => wfMessage( 'bm_annote' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateAnnote',
				'rows' => 5
			], // An annotation for annotated bibliography styles (not typical)
			'author' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_author' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateAuthor',
			], // The name(s) of the author(s) (in the case of more than one author, separated by and)
			'booktitle' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_booktitle' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateBooktitle',
			], // The title of the book, if only part of it is being cited
			'chapter' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_chapter' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateChapter',
			], // The chapter number
			'crossref' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_crossref' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateCrossref',
			], // The key of the cross-referenced entry
			'edition' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_edition' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateEdition',
			], // The edition of a book, long form (such as "first" or "second")
			'editor' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_editor' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateEditor',
			], // The name(s) of the editor(s)
			'eprint' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_eprint' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateEprint',
			], // A specification of an electronic publication, often a preprint or a technical report
			'howpublished' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_howpublished' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateHowpublished',
			], // How it was published, if the publishing method is nonstandard
			'institution' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_institution' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateInstitution',
			], // The institution that was involved in the publishing, but not necessarily the publisher
			'journal' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_journal' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateJournal',
			], // The journal or magazine the work was published in
			'key' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_key' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateKey',
			], // A hidden field used for specifying or overriding the alphabetical order of entries (when the "author" and "editor" fields are missing). Note that this is very different from the key (mentioned just after this list) that is used to cite or cross-reference the entry.
			'month' => [
				'class' => 'HTMLSelectField',
				'label' => wfMessage( 'bm_month' )->escaped(),
				'options' => [
					'' => '',
					wfMessage( 'bm_month_jan' )->escaped() => 'january',
					wfMessage( 'bm_month_feb' )->escaped() => 'february',
					wfMessage( 'bm_month_mar' )->escaped() => 'march',
					wfMessage( 'bm_month_apr' )->escaped() => 'april',
					wfMessage( 'bm_month_may' )->escaped() => 'may',
					wfMessage( 'bm_month_jun' )->escaped() => 'june',
					wfMessage( 'bm_month_jul' )->escaped() => 'july',
					wfMessage( 'bm_month_aug' )->escaped() => 'august',
					wfMessage( 'bm_month_sep' )->escaped() => 'september',
					wfMessage( 'bm_month_oct' )->escaped() => 'october',
					wfMessage( 'bm_month_nov' )->escaped() => 'november',
					wfMessage( 'bm_month_dec' )->escaped() => 'december',
			],
				'validation-callback' => 'BibManagerValidator::validateMonth',
			], // The month of publication (or, if unpublished, the month of creation)
			'note' => [
				'class' => 'HTMLTextAreaField',
				'label' => wfMessage( 'bm_note' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateNote',
				'rows' => 5
			], // Miscellaneous extra information
			'number' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_number' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateNumber',
			], // The "(issue) number" of a journal, magazine, or tech-report, if applicable. (Most publications have a "volume", but no "number" field.)
			'organization' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_organization' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateOrganization',
			], // The conference sponsor
			'pages' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_pages' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validatePages',
			], // Page numbers, separated either by commas or double-hyphens.
			'publisher' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_publisher' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validatePublisher',
			], // The publisher's name
			'school' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_school' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateSchool',
			], // The school where the thesis was written
			'series' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_series' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateSeries',
			], // The series of books the book was published in (e.g. "The Hardy Boys" or "Lecture Notes in Computer Science")
			'title' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_title' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateTitle',
			], // The title of the work
			'type' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_type' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateType',
			// validate ?
			], // The field overriding the default type of publication (e.g. "Research Note" for techreport, "{PhD} dissertation" for phdthesis, "Section" for inbook/incollection)
			'url' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_url' )->escaped(),
				'default' => 'http://',
				'validation-callback' => 'BibManagerValidator::validateUrl',
			], // The WWW address
			'volume' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_volume' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateVolume',
			], // The volume of a journal or multi-volume book
			'year' => [
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'bm_year' )->escaped(),
				'validation-callback' => 'BibManagerValidator::validateYear',
			] // The year of publication (or, if unpublished, the year of creation)
		];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerGetFieldDefinitions', [ &$fieldDefinitions ] );
		return $fieldDefinitions;
	}

	/**
	 * @return array[]
	 * @throws Exception
	 */
	public static function getTypeDefinitions() {
		// TODO RBV (23.12.11 13:36): Make arrays global (label sould be only the language key!): Iterate over them, i18n-ify lables and run hook.
		$typeDefinitions = [
			'article' => [
				'label' => wfMessage( 'bm_entry_type_article' )->escaped(),
				'required' => [ 'author', 'title', 'journal', 'year' ],
				'optional' => [ 'volume', 'number', 'pages', 'month', 'note', 'key', 'url' ]
			],
			'book' => [
				'label' => wfMessage( 'bm_entry_type_book' )->escaped(),
				'required' => [ 'author', 'editor', 'title', 'publisher', 'year' ],
				'optional' => [ 'volume', 'number', 'series', 'address', 'edition', 'month', 'note', 'key', 'url' ]
			],
			'booklet' => [
				'label' => wfMessage( 'bm_entry_type_booklet' )->escaped(),
				'required' => [ 'title' ],
				'optional' => [ 'author', 'howpublished', 'address', 'month', 'year', 'note', 'key' ]
			],
			'conference' => [
				'label' => wfMessage( 'bm_entry_type_conference' )->escaped(),
				'required' => [ 'author', 'title', 'booktitle', 'year' ],
				'optional' => [ 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' ]
			],
			'inbook' => [
				'label' => wfMessage( 'bm_entry_type_inbook' )->escaped(),
				'required' => [ 'author', 'editor', 'title', 'chapter', 'pages', 'publisher', 'year' ],
				'optional' => [ 'volume', 'number', 'series', 'type', 'address', 'edition', 'month', 'note', 'key', 'url' ]
			],
			'incollection' => [
				'label' => wfMessage( 'bm_entry_type_incollection' )->escaped(),
				'required' => [ 'author', 'title', 'booktitle', 'publisher', 'year' ],
				'optional' => [ 'editor', 'volume', 'number', 'series', 'type', 'address', 'chapter', 'pages', 'address', 'edition', 'month', 'note', 'key', 'url' ]
			],
			'inproceedings' => [
				'label' => wfMessage( 'bm_entry_type_inproceedings' )->escaped(),
				'required' => [ 'author', 'title', 'booktitle', 'year' ],
				'optional' => [ 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' ]
			],
			'manual' => [
				'label' => wfMessage( 'bm_entry_type_manual' )->escaped(),
				'required' => [ 'title' ],
				'optional' => [ 'author', 'organization', 'address', 'edition', 'month', 'year', 'note', 'key', 'url' ]
			],
			'mastersthesis' => [
				'label' => wfMessage( 'bm_entry_type_mastersthesis' )->escaped(),
				'required' => [ 'author', 'title', 'school', 'year' ],
				'optional' => [ 'type', 'address', 'month', 'note', 'key', 'url' ]
			],
			'misc' => [
				'label' => wfMessage( 'bm_entry_type_misc' )->escaped(),
				'required' => [],
				'optional' => [ 'author', 'title', 'howpublished', 'month', 'year', 'note', 'key', 'url' ]
			],
			'phdthesis' => [
				'label' => wfMessage( 'bm_entry_type_phdthesis' )->escaped(),
				'required' => [ 'author', 'title', 'school', 'year' ],
				'optional' => [ 'type', 'address', 'month', 'note', 'key', 'url' ]
			],
			'proceedings' => [
				'label' => wfMessage( 'bm_entry_type_proceedings' )->escaped(),
				'required' => [ 'title', 'year' ],
				'optional' => [ 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note', 'key', 'url' ]
			],
			'techreport' => [
				'label' => wfMessage( 'bm_entry_type_techreport' )->escaped(),
				'required' => [ 'author', 'title', 'institution', 'year' ],
				'optional' => [ 'type', 'note', 'number', 'address', 'month', 'key', 'url' ]
			],
			'unpublished' => [
				'label' => wfMessage( 'bm_entry_type_unpublished' )->escaped(),
				'required' => [ 'author', 'title', 'note' ],
				'optional' => [ 'month', 'year', 'key', 'url' ]
			]
		];

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BibManagerGetTypeDefinitions', [ &$typeDefinitions ] );
		return $typeDefinitions;
	}

}
