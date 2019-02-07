rhino-self-assessment
======================

SilverStripe module making use of Rhino (rhino-lite and rhino-fields) to scaffold a quiz.

## Requires

	"dnadesign/rhino-lite": "dev-master",
	"dnadesign/rhino-fields": "dev-master"

## Use

	ElementalPage:
	  allowed_elements:
	    - ElementSelfAssessment
	    
	SiteConfig:
	  selfassessment_email_from:
		- info@website.com
