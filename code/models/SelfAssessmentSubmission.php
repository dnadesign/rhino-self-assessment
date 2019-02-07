<?php

class SelfAssessmentSubmission extends RhinoSubmittedAssessment {

	private static $db = array(
		'UserEmail' => 'Varchar(255)',
		'Location' => 'Varchar',
		'Industry' => 'Varchar',
		'Employees' => 'Varchar',
		'Age' => 'Varchar',
 		'Views' => 'Int' // Number of time a user has returned to the result page
	);

	private static $summary_fields = array(
		'ID' => 'ID',
		'Created' => 'Submitted on',
		'Views' => 'Viewed Count',
		'SubmittedBy.Title' => 'SubmittedBy'
	);

	public function onAfterUpdateAfterProcess($data = null, $form = null) {
		if ($data) {
			$this->Location = (isset($data['Location'])) ? $data['Location'] : null;
			$this->Industry = (isset($data['Industry'])) ? $data['Industry'] : null;
			$this->Employees = (isset($data['Employees'])) ? $data['Employees'] : null;
			$this->Age = (isset($data['Age'])) ? $data['Age'] : null;
			$this->write();
		}
	}
}