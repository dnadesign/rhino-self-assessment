<?php

namespace DNADesign\Rhino\Jobs;

use DNADesign\Rhino\Model\SelfAssessmentReport;
use ParseCsv\Csv;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

class CreateSelfAssessmentReportJob extends AbstractQueuedJob implements QueuedJob
{
    /**
     * ALlows to pass a year as parameter from the CMS model admin
     */
    public function __construct()
    {
        $args = array_filter(func_get_args());

        if (isset($args[0])) {
            $this->ReportID = $args[0];
        }
    }

    public function getReport()
    {
        return ($this->ReportID) ? SelfAssessmentReport::get()->byID($this->ReportID) : null;
    }

    /**
     * Defines the title of the job
     *
     * @return string
     */
    public function getTitle()
    {
        $report = $this->getReport();
        if ($report && $report->exists() && $report->Assessment() && $report->Assessment()->exists()) {
            return sprintf('Create "%s" report from %s to %s (%s test data)',
                $report->Assessment()->Title,
                $report->dbObject('SubmissionFrom')->Format('y-MM-dd'),
                $report->dbObject('SubmissionTo')->Format('y-MM-dd'),
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
    public function getJobType()
    {
        $report = $this->getReport();
        if ($report) {
            $this->totalSteps = $report->getSubmittedFields()->Count();
        }

        return QueuedJob::QUEUED;
    }

     /**
     * By default the job descriptor is only ever updated when process() is
     * finished, so for long running single tasks the user see's no process.
     *
     * This method manually updates the count values on the QueuedJobDescriptor
     */
    public function updateJobDescriptor()
    {
        if (!$this->descriptor && $this->jobDescriptorId) {
            $this->descriptor = QueuedJobDescriptor::get()->byId($this->jobDescriptorId);
        }

        // rate limit the updater to only 1 query every sec, our front end only
        // updates every 1s as well.
        if ($this->descriptor && (!$this->lastUpdatedDescriptor || $this->lastUpdatedDescriptor < (strtotime('-1 SECOND')))) {
            Injector::inst()->get(QueuedJobProgressService::class)
                ->copyJobToDescriptor($this, $this->descriptor);

            $this->lastUpdatedDescriptor = time();
        }
    }

    /**
     * Retrieve all the organisation that do not have an annual report yet
     */
    public function setup()
    {
        $report = $this->getReport();

        // Create File assets
        $this->filename = $report->file_title();
        $this->writablePath = ASSETS_PATH.'/temp-'.$this->filename;
        $this->folderPath = $report->file_path();

        $csvHeader = [
            'Tool Title',
            'Date Completed',
            'UID',
            'Question',
            'Answer',
            'Advice',
            'Rating',
            'Theme',
            'UserEmail'
        ];

        // Write CSV
        $csv = new Csv();
        $csv->save($this->writablePath, array(array_values($csvHeader)), true);

        $this->addMessage('Will write CSV file in: '.$this->writablePath);

        // Set up remaining children
        $remainingChildren = $report->getSubmittedFields()->column('ID');
        $this->remainingChildren = $remainingChildren;
    }

    /**
     * Lets process a single node, and  create the PDF and CSV
     */
    public function process()
    {
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

            $folder = Folder::find_or_make($this->folderPath);
            $path = File::join_paths($folder->getFilename(), $this->filename);

            $file = new File();
            $file->ParentID = $folder->ID;
            $file->setFromLocalFile($this->writablePath, $path);
            $file->write();
            $file->publishRecursive();

            unlink($this->writablePath);

            if ($file && $file->exists()) {
                $this->addMessage('CSV can be downloaded here:');
                $this->addMessage($file->AbsoluteLink());
                // record File ID for FileFromQueuedJobController
                $this->FileID = $file->ID;
            }

            if ($report) {
                $report->Status = 'Done';
                $report->Completed = DBDatetime::now();
                $report->FileID = $file->ID;
                $report->write();

                $report->sendNotificationEmail();
            }

            $this->updateJobDescriptor();

            return;
        }

        // we need to always increment! This is important, because if we don't then our container
        // that executes around us thinks that the job has died, and will stop it running.
        $this->currentStep++;

        // lets process our first item - note that we take it off the list of things left to do
        $ID = array_shift($remainingChildren);

        // get the field
        $answer = DataObject::get_by_id(SubmittedFormField::class, $ID);
        // And the question
        $question = ($answer && $answer->exists()) ? $answer->getParentEditableFormField() : null;

        if ($answer && $question) {
            // We need to get the submnission
            $submission = $answer->Parent();
            // And the tool
            $assessment = $submission->Parent();

            // Build data
            $line = [
                $assessment->Title,
                $submission->Created,
                $submission->uid,
                $question->Title,
                $answer->Value,
                ($question->hasMethod('getAdviceForAnswer')) ? $question->getAdviceForAnswer($answer) : '',
                ($question->hasMethod('getRatingForAnswer')) ? $question->getRatingForAnswer($answer) : '',
                ($question->ResultThemeID) ? $question->ResultTheme()->Title : '',
                $submission->UserEmail
            ];

            // Write CSV
            $csv = new Csv();
            $csv->save($this->writablePath, array(array_values($line)), true);

            $answer->destroy();
            unset($answer);

            $question->destroy();
            unset($question);

            $submission->destroy();
            unset($submission);

            $assessment->destroy();
            unset($assessment);

        } else {
            $message = 'Could not find Answer or Question for Answer ID: ' . $ID;
            $this->addMessage($message, 'WARNING');
        }

        // and now we store the new list of remaining children
        $this->remainingChildren = $remainingChildren;
    }

}
