<?php

/**
* Rhino Assessment is the base of the self assessment tool
*/
class SelfAssessmentExtension extends DataExtension {

	/**
	* RhinoAssessment are pages, that will be later included via an element
	* so they need to be hidden by default
	*/
	public function populateDefaults() {
		$this->owner->ShowInMenus = false;
		$this->owner->ShowInSearch = false;
		$this->owner->SubmitButtonText = "Show my results";
 	}

 	/**
 	* A lot of fields inherited from Page are not used
 	*/
 	public function updateCMSFields(FieldList $fields) {

 		$submitButtonText = $fields->fieldByName('Root.FormOptions.SubmitButtonText');
 		$submitButtonText->setRightTitle('Deaults to "Show My Results"');

 		$fields->removeByName(array(
 			'NavigationPromoTileID',
 			'Scheme',
 			'Metadata',
 			'FeedbackOnSubmission',
 			'Terms',
 			'Tags',
 			'SearchKeywords',
 			'Recipients',
 			'Translations',
 			'warnemail',
 			'MenuTitle',
			'Content'
		));

		$formFields = $fields->fieldByName('Root.FormFields.Fields');
		$fields->removeByName('FormFields');
		$fields->addFieldsToTab('Root.Main', array($formFields, $submitButtonText));

		$formFieldsConfig = $formFields->getConfig();
		$adders = $formFieldsConfig->getComponentsByType('GridFieldAddClassesButton');
		$formFieldsConfig->removeComponent($adders->pop()); // First remove Firl Group
		$formFieldsConfig->removeComponent($adders->pop()); // Then remove Page break

		// Add DeleteTestData action to submission
		$submissions = $fields->fieldByName('Root.Submissions.Submissions');
		$config = $submissions->getConfig();
		$config->addComponent(new GridFieldRequestDeleteTestData());
 	}

}


