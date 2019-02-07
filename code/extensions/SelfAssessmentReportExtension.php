<?php

class SelfAssessmentReportExtension extends DataExtension {

	/**
	* If a report is valid ie has submission
	* a QueuedJob is created, so we cannot allow to amend it afterward
	*/
	public function updateBetterButtonsActions($actions) {
		$actions->removeByName('action_save');

		$saveAndClose = $actions->fieldByName('action_doSaveAndQuit');
		$saveAndClose->setButtonContent('Request Report');
	}

	// No need to the buttons at the top right
	public function updateBetterButtonsUtils(&$buttons) {
		$buttons = new FieldList(array());
	}

}