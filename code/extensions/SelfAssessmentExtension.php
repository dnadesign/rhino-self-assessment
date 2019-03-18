<?php

/**
 * Self Assessment is the base of the self assessment tool
 */
class SelfAssessmentExtension extends DataExtension
{

	/**
	 * Self Assessment are pages, that will be later included via an element so they need to be hidden by default
	 */
	public function populateDefaults()
	{
		$this->owner->ShowInMenus = false;
		$this->owner->ShowInSearch = false;
		$this->owner->SubmitButtonText = "Show my results";
	}

	/**
	 * A lot of fields inherited from Page are not used
	 */
	public function updateCMSFields(FieldList $fields)
	{
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
		$fields->addFieldsToTab('Root.Main', [$formFields, $submitButtonText]);

		$formFieldsConfig = $formFields->getConfig();

		// Remove Field Group and Page break button
		$adders = $formFieldsConfig->getComponentsByType('GridFieldAddClassesButton')
			->filterByCallBack(function ($item) {
			$classes = $item->getClasses();
			return (is_array($classes) && !in_array('EditableFormStep', $classes) && !in_array('EditableFieldGroup', $classes));
		});

		$formFieldsConfig->removeComponentsByType('GridFieldAddClassesButton');

		// Make sure the adder button adds a field of the first class available in the dropdown
		$adder = $adders->First();
		$allowedFieldTypes = $this->owner->config()->allowed_field_types;
		if (!$allowedFieldTypes) {
			$allowedFieldTypes = singleton('EditableFormField')->getEditableFieldClasses();
		}

		// Check if we have at least one field type allowed and set the button to create this field type
		$firstAllowedFieldType = (is_array($allowedFieldTypes) && isset($allowedFieldTypes[0])) ? $allowedFieldTypes[0] : [];
		$adder->setClasses($firstAllowedFieldType);

		// Re-include add buttons
		foreach ($adders->toArray() as $component) {
			$formFieldsConfig->addComponent($component);
		}

		// Add DeleteTestData action to submission
		$submissions = $fields->fieldByName('Root.Submissions.Submissions');
		$config = $submissions->getConfig();
		$config->addComponent(new GridFieldRequestDeleteTestData());
	}

	/**
	 * We don't want to be able to save a SelfAssesment which contains questions without a title Title field is required
	 * on SelfAssessmentQuestion but because of inline editing, it is possible to save the page with blank question
	 */
	public function validate(ValidationResult $result)
	{
		// Look for fields without a title
		$blankFields = $this->owner->Fields()->filter('Title', null)->Count();
		if ($blankFields > 0) {
			$result->error("Please add missing $blankFields  \"Titles\" to all the questions.", 'validation');

			// TODO: add error message on the form itself
			// Currently doesn't work, only shows the ajax validation popup
			// $validator = Controller::curr()->getEditForm();
			// $validator->setMessage('Please add "TItles" to all the questions', 'error');
		}

		return $result;
	}
}
