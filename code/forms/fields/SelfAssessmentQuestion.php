<?php

class SelfAssessmentQuestion extends EditableMultiChoiceField {

	private static $singular_name = 'Self Assessment Question';

	private static $optionClass = 'EditableSelfAssessmentOption';

	private static $casting = array(
		"Options" => 'EditableSelfAssessmentOption'
	);

	private static $summary_fields = array(
		'ID' => 'ID',
		'Title' => 'Title'
	);

	private static $defaults = array(
		'Required' => '1'
	);

	private static $db = array(
		'TidbitTitle' => 'Varchar(255)',
		'Tidbit' => 'HTMLText'
	);

	private static $has_one = array(
		'ResultTheme' => 'ResultTheme',
		'TidbitImage' => 'File'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('RightTitle');
		$fields->removeByName('ExtraClass');
		$fields->removeByName('DisplayRules');
		$fields->removeByName('CustomErrorMessage');
		$fields->removeByName('ShowInSummary');
		$fields->removeByName('warning');
		$fields->removeByName('Validation');

		$tidbitTitle = TextField::create('TidbitTitle')->setRightTitle('eg: Did you know...');
		$tidbit = HTMLEditorField::create('Tidbit', 'Tidbit');
		$fields->addFieldsToTab('Root.Tidbit', array($tidbitTitle, $tidbit));

		$image = UploadField::create('TidbitImage', 'Tidbit Image')->setDescription('Square ratio. SVG recommended. Minimum size 300x300px.');
		$image->setAllowedExtensions(array('svg', 'jpg', 'jpeg', 'png'));
		$image->getValidator()->setAllowedMaxFileSize('2M');
		$fields->addFieldToTab('Root.Tidbit', $image);

		return $fields;
	}

	public function getFormField() {
		$field = parent::getFormField();

		$field->setFieldHolderTemplate('SelfAssessmentQuestion_holder');
		$field->setTemplate('SelfAssessmentQuestion');

		$field->customise(array(
			'Image' => $this->Image(),
			'TidbitTitle' => $this->TidbitTitle,
			'Tidbit' => $this->Tidbit, 
			'TidbitImage' => $this->TidbitImage(),
			'ResultTheme' => $this->ResultTheme(),
			'SelfAssessmentTitle' => $this->Parent()->Title,
			'TotalQuestionCount' => $this->Parent()->TotalQuestionCount()
		));
	
		return $field;
	}

	/**
	* Return the advice from a submittedFormField
	*/
	public function getAdviceForAnswer($answer) {
		$option = $this->Options()->filter('Value', $answer->Value)->First();
		if ($option) {
			return $option->Advice;
		}
		return null;
	}
	
	/**
	* Return the rating from a submittedFormField
	*/
	public function getRatingForAnswer($answer) {
		$option = $this->Options()->filter('Value', $answer->Value)->First();
		if ($option) {
			return $option->Rating;
		}
		return null;
	}

}
