# Rhino Self Assessment

SilverStripe module making use of Rhino (rhino-lite and rhino-fields) to scaffold a quiz.

## Install
	"dnadesign/rhino-self-assessment": "^2"

## Dependencies
	"silverstripe/framework": "^4"
	"dnadesign/silverstripe-elemental": "^4"
	"symbiote/silverstripe-queuedjobs": "^4"
	"parsecsv/php-parsecsv": "^1.1"

Note: at this stage the DNA Design are not on Packagist, so need ot be required manually in the project via vcs

	"require": {
        "dnadesign/rhino-fields": "^2.0",
        "dnadesign/rhino-lite": "^2.0",
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/dnadesign/rhino-fields.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/dnadesign/rhino-lite.git"
        }
    ]

## Features

### Quiz

A self assessment is essentially a quiz with multiple choice questions. Each question can be followed by a tidbit (a screen including some text and an optional image).
Each options for each question is given a score (1-5) which will be used on the result page.

### Element

This quiz is designed to be displayed as an element on an elemental page.
By default, SelfAssessment pages are elemental and you can add an Element Self Assessment to it to preview the quiz.

### Result

The result page lists all the answers given by the user, grouped by themes, with their associated score and advice. This page exists as long as the submission exists in the database. A link to the result page can be emailed via the supplied form.

### Report

You can generate a CSV report for a given date range.
The report will contain every answer for each submissions.
The report can be requested via the self assessment report tab.
Note: requesting a report will create a queued job, which will email the requester upon completion. Make sure to set the email from in the Site Config (Settings)

### Styling

The module comes with basic styling and functional javascript.
You can block the css and javascript via config:

	DNADesign\Rhino\Control\SelfAssessmentController
	  include_default_javascript: false
	  include_default_css: false
