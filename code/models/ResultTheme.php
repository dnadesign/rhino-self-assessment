<?php

class ResultTheme extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(255)',
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'SelfAssessment' => 'SelfAssessment'
	);

	private static $has_many = array(
		'Questions' => 'SelfAssessmentQuestion'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'getQuestionsList' => 'Questions'
	);

	private static $default_sort = 'Sort ASC';

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');

		if ($this->IsInDB()) {
			$questions = $fields->fieldByName('Root.Questions.Questions');
			$fields->removeByName('Questions');

			$config = $questions->getConfig();
			$config->removeComponentsByType('GridFieldAddNewButton');

			// Allow to search only the Question of this assessment
			$questionField = SelfAssessmentQuestion::get()->filter(array('ParentID' => $this->SelfAssessment()->ID, 'ResultThemeID:LessThan' => '1'));
			$autoCompleter = $config->getComponentByType('GridFieldAddExistingAutocompleter');
			$autoCompleter->setSearchList($questionField);

			$fields->addFieldtoTab('Root.Main', $questions);
		}

		return $fields;
	}

	/**
	* Need to make sure we release the question when deleting the theme
	*/
	public function onBeforeDelete() {
		parent::onBeforeDelete();

		$updateQuestions = new SQLUpdate('SelfAssessmentQuestion', array('ResultThemeID' => 0), array('ResultThemeID' => $this->ID));
		$updateQuestions->execute();

		$updateQuestions->setTable('SelfAssessmentQuestion_Live');
		$updateQuestions->execute();
	}

	/**
	* Used by Gridfield Summary
	*/
	public function getQuestionsList() {
		if ($this->Questions()->exists()) {
			$titles = $this->Questions()->column('Title');
			$titles = implode('<br/>', $titles);

			$list = HTMLText::create();
			$list->setValue($titles);
			return $list;
		}
	}

	/**
	* Look up the submission from the URL UID
	* and return a collection of advice for each questions
	* present in this theme
	*
	* @return ArrayList
	*/
	public function getAdviceForCurrentSubmission() {
		$controller = Controller::curr();
		$submission = null;

		if ($controller->hasMethod('getSubmission')) {
			$submission = $controller->getSubmission();
		}

		if (!$submission) user_error('Controller is unable to retrieve submission. Check method getSubmission() is present.');

		$collection = [];
						
		$options = EditableSelfAssessmentOption::get()
					// Get all the options which parent question are in this theme
					// And which value has been submitted by user
					->filter(array('ParentID' => $this->Questions()->column('ID'), 'ID' =>  $submission->Values()->column('ParentOptionID')))
					// Exlcude options that do not have an advice
					->exclude(array('Advice' => ''))
					// Sort by Star Rating
					->sort('Rating ASC');

		foreach ($options as $option) {
			$advice = array(
				'Question' => $option->Title,
				'Advice' => $option->dbObject('Advice'),
				'Rating' => $option->Rating
			);

			array_push($collection, $advice);
		}

		return new ArrayList($collection);
	}

}
