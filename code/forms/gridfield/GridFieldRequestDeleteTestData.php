<?php

/**
 * Adds an "Request Report" button to the top of a {@link GridField}.
 */

class GridFieldRequestDeleteTestData implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler {

	/**
	 * Fragment to write the button to
	 */
	protected $targetFragment;

	/**
	 * @param string $targetFragment The HTML fragment to write the button into
	 */
	public function __construct($targetFragment = "buttons-after-left") {
		$this->targetFragment = $targetFragment;
	}

	/**
	 * Place the export button in a <p> tag below the field
	 */
	public function getHTMLFragments($gridField) {
		$button = new GridField_FormAction(
			$gridField,
			'deletetestdata',
			'Delete Test Data',
			'deletetestdata',
			null
		);
		$button->setForm($gridField->getForm());
		// $button->addExtraClass('no-ajax');
		return array(
			$this->targetFragment => $button->Field()
		);
	}

	/**
	 * export is an action button
	 */
	public function getActions($gridField) {
		return array('deletetestdata');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'deletetestdata') {
			return $this->handleDeleteTestData($gridField);
		}
	}

	/**
	 * it is also a URL
	 */
	public function getURLHandlers($gridField) {
		return array(
			'deletetestdata' => 'handleDeleteTestData',
		);
	}

	/**
	 * Handle the export, for both the action button and the URL
 	 */
	public function handleDeleteTestData($gridField, $request = null) {
		$assessmentID = $gridField->getRequest()->postVar('ID');
		if ($assessmentID) {
			$submissions = SelfAssessmentSubmission::get()->filter('ParentID', $assessmentID)->exclude('SubmittedByID', 0);

			if ($count = $submissions->Count()) {	
			
				$deleteFields = SQLDelete::create('SubmittedFormField', sprintf('ParentID IN (%s)', implode(',',$submissions->column('ID'))));
				$deleteFields->execute();

				$delete = SQLDelete::create(array('SubmittedForm'), sprintf('ID IN (%s)', implode(',',$submissions->column('ID'))));
				$delete->execute();

				$delete->setFrom('SelfAssessmentSubmission')->execute();

				Controller::curr()->getResponse()->setStatusCode('200', sprintf('Deleted %s test records.', $count));
			}
		}

		return $request;
	}
	
	


}
