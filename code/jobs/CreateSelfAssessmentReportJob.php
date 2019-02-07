<?php

class CreateSelfAssessmentReportJob extends AbstractQueuedJob implements QueuedJob {

	/**
	* ALlows to pass a year as parameter from the CMS model admin
	*/
	public function __construct() {
		$args = array_filter(func_get_args());

		if (isset($args[0])) {
			$this->ReportID = $args[0];
		}
	}

	public function getReport() {
		return ($this->ReportID) ? SelfAssessmentReport::get()->byID($this->ReportID) : null;
	}

	/**
	 * Defines the title of the job
	 *
	 * @return string
	 */
	public function getTitle() {
		$report = $this->getReport();
		if ($report && $report->exists() && $report->Assessment() && $report->Assessment()->exists()) {
			return sprintf('Create "%s" report from %s to %s (%s test data)', 
				$report->Assessment()->Title, 
				$report->dbObject('SubmissionFrom')->Format('d, M Y H:i:s'),
				$report->dbObject('SubmissionTo')->Format('d, M Y H:i:s'),
				($report->IncludeTestData) ? 'includes' : 'excludes');
		}

		return 'Cannot find the assemment to report on!';		
	}

	/**
	 * Indicate to the system which queue we think we should be in based
	 * on how many objects we're going to touch on while processing.
	 *
	 * We want to make sure we also set how many steps we think we might need to take to
	 * process everything - note that this does not need to be 100% accurate, but it's nice
	 * to give a reasonable approximation
	 *
	 * @return int
	 */
	public function getJobType() {
		$report = $this->getReport();
		if ($report) {
			$this->totalSteps = $report->getSubmittedFields()->Count();
		}

		return QueuedJob::QUEUED;
	}

	/**
	* Retrieve all the organisation that do not have an annual report yet
	*/
	public function setup() {
		$report = $this->getReport();
		
		$remainingChildren = $report->getSubmittedFields()->column('ID');
		$this->remainingChildren = $remainingChildren;

		$fileObj = $report->find_or_create_file();
		$filename = ($fileObj) ? $fileObj->getFullPath() : null;

		$csvHeader = array(
			'Tool Title',
			'Date Completed',
			'UID',
			'Industry',
			'Location',
			'Employee Number',
			'Age',
			'Question',
			'Answer',
			'Advice',
			'Rating',
			'Theme'
		);

		// If file does not exists yet, create it and add the csv headers
		if (!file_exists($filename)) {
			if ($file = fopen($filename, 'w')) {
				fwrite($file, implode(',',$csvHeader).PHP_EOL);
				fclose($file);
				$this->addMessage('Created CSV file '.$filename, 'INFO');
			} else {
				$this->addMessage('Unable to create CSV file!', 'WARNING');
			}
		} else {
			$this->addMessage('File already exists!', 'WARNING');
			$this->remainingChildren = array();
			$this->isComplete = true;
		}

		$this->csvFilename = $filename;
		$this->FileObjID = $fileObj->ID;
	}

	/**
	 * Lets process a single node, and  create the PDF and CSV
	 */
	public function process() {
		// Update the sattus of the report
		$report = $this->getReport();
		if ($report && $report->Status == 'Pending') {
			$report->Status = 'Started';
			$report->write();
		}

		$remainingChildren = $this->remainingChildren;

		// if there's no more, we're done!
		if (!count($remainingChildren)) {
			$this->isComplete = true;
			return;
		}

		// we need to always increment! This is important, because if we don't then our container
		// that executes around us thinks that the job has died, and will stop it running.
		$this->currentStep++;

		// lets process our first item - note that we take it off the list of things left to do
		$ID = array_shift($remainingChildren);

		// get the field
		$answer = DataObject::get_by_id('SubmittedFormField', $ID);
		// And the question
		$question = ($answer && $answer->exists()) ? $answer->getParentEditableFormField() : null;

		if ($answer && $question) {
			// We need to get the submnission
			$submission = $answer->Parent();
			// And the tool
			$assessment = $submission->Parent();			

			// Build data 
			$line = array(
				$assessment->Title,
				$submission->Created,
				$submission->uid,
				$submission->Industry,
				$submission->Location,
				$submission->Employees,
				$submission->Age,
				$question->Title,
				$answer->Value,
				($question->hasMethod('getAdviceForAnswer')) ? $question->getAdviceForAnswer($answer) : '',
				($question->hasMethod('getRatingForAnswer')) ? $question->getRatingForAnswer($answer) : '',
				($question->ResultThemeID) ? $question->ResultTheme()->Title : ''				
			);

			if ($file = fopen($this->csvFilename, 'a')) {
				fputcsv($file, $line);
				fclose($file);
			}
			 else {
				$message = 'Could not write '.$this->csvFilename;
				$this->addMessage($message, 'WARNING');
				SS_Log::log($message, SS_Log::WARN);
			}			

			$answer->destroy();
			unset($answer);

			$question->destroy();
			unset($question);

			$submission->destroy();
			unset($submission);

			$assessment->destroy();
			unset($assessment);

		} else {
			$message = 'Could not find Answer or Question for Answer ID: '.$ID;
			$this->addMessage($message, 'WARNING');
			SS_Log::log($message , SS_Log::WARN);
		}

		// and now we store the new list of remaining children
		$this->remainingChildren = $remainingChildren;

		if (!count($remainingChildren)) {

			// Create a file object
			$file = File::get()->byID($this->FileObjID);
			$file->updateFilesystem();

			if ($report) {
				$report->Status = 'Done';
				$report->Completed = SS_DateTime::now();
				$report->FileID = $file->ID;
				$report->write();

				$report->sendNotificationEmail();
			}

			$this->addMessage('Done!');
			$this->isComplete = true;
			return;
		}
	}

}