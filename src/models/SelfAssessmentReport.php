<?php

namespace DNADesign\Rhino\Model;

use DNADesign\Rhino\Fields\SelfAssessmentQuestion;
use DNADesign\Rhino\Pagetypes\SelfAssessment;
use DNADesign\Rhino\Jobs\CreateSelfAssessmentReportJob;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DateField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class SelfAssessmentReport extends DataObject
{
    private static $default_task = 'CreateSelfAssessmentReportJob';

    private static $field_classes_to_include = [SelfAssessmentQuestion::class];

    private static $table_name = 'SelfAssessmentReport';

    private static $file_path = 'reports/%s';

    private static $db = [
        'Status' => "Enum('Pending, Started, Done')",
        'Completed' => 'DBDatetime',
        'SubmissionCount' => 'Int',
        'SubmissionFrom' => 'DBDatetime',
        'SubmissionTo' => 'DBDatetime',
        'IncludeTestData' => 'Boolean'
    ];

    private static $has_one = [
        'Assessment' => SelfAssessment::class,
        'RequestedBy' => Member::class,
        'File' => File::class
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'RequestedBy.Name' => 'Requested By',
        'Created' => 'Requested On',
        'getStatusLabel' => 'Status',
        'SubmissionFrom' => 'Submitted From',
        'SubmissionTo' => 'Submitted Until',
        'SubmissionCount' => 'Total Submissions',
        'IncludeTestData.Nice' => 'Include Test Data'
    ];

    private static $default_sort = 'Created DESC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Status',
            'Completed',
            'SubmissionCount',
            'RequestedByID',
            'File'
        ]);

        $from = DateField::create('SubmissionFrom');
        $from->setHTML5(true);
        $fields->replaceField('SubmissionFrom', $from);

        $to = DateField::create('SubmissionTo');
        $to->setHTML5(true);
        $fields->replaceField('SubmissionTo', $to);

        return $fields;
    }

    public function validate()
    {
        $valid = parent::validate();

        if (!$this->getSubmissions() || !$this->getSubmissions()->exists()) {
            $valid = $valid->addError('This report has no submission. Please change the date parameters and/or include test data. ');
        }

        return $valid;
    }

    /**
     * Since we are not using the CMS edit field we need to set the status manually
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Status) {
            $this->Status = 'Pending';
        }

        $submissions = $this->getSubmissions();
        if (!$this->SubmissionCount && $submissions->exists()) {
            $this->SubmissionCount = $submissions->Count();
        }

        if (!$this->RequestedByID) {
            $this->RequestedByID = Member::currentUserID();
        }
    }

    /**
     * Create Task when saving the report object
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->Status == 'Pending' && $this->SubmissionCount > 0) {
            $job = new CreateSelfAssessmentReportJob($this->ID);
            QueuedJobService::singleton()->queueJob($job);
        }
    }

    public function getSubmissions()
    {
        $submissions = null;
        $assessment = $this->Assessment();

        if ($assessment && $assessment->exists()) {
            // Get sorted submissions
            $submissions = $assessment->Submissions()->sort('Created ASC');

            // Filter the requested date
            if ($this->SubmissionFrom) {
                $submissions = $submissions->filter([
                    'Created:GreaterThan' => sprintf('%s 00:00:00', $this->dbObject('SubmissionFrom')->format('y-MM-dd'))
                ]);
            }

            if ($this->SubmissionTo) {
                $submissions = $submissions->filter([
                    'Created:LessThan' => sprintf('%s 23:59:59', $this->dbObject('SubmissionTo')->format('y-MM-dd'))
                ]);
            }

            // Include test data
            if (!$this->IncludeTestData) {
                $submissions = $submissions->filter('SubmittedByID', 0);
            }
        }

        return $submissions;
    }

    public function getSubmittedFields()
    {
        $assessment = $this->Assessment();
        if ($assessment && $assessment->exists()) {
            $fieldToReportOn = $assessment->Fields()->filter('ClassName', $this->config()->field_classes_to_include);

            // Get sorted submissions
            $submissions = $this->getSubmissions();

            if ($submissions->exists()) {
                $fields = SubmittedFormField::get()->filter([
                    'Name' => $fieldToReportOn->column('Name'),
                    'ParentID' => $submissions->column('ID')
                ]);

                return $fields;
            }
        }

        return null;
    }

    public function file_path()
    {
        $assessment = $this->Assessment();
        if ($assessment && $assessment->exists()) {
            $path = sprintf($this->config()->file_path, $assessment->URLSegment);
            return $path;
        }
    }

    public function file_title()
    {
        $assessment = $this->Assessment();
        if ($assessment && $assessment->exists()) {
            return sprintf(
                '%s(%s)--%s--to--%s.csv',
                $assessment->URLSegment,
                $this->ID,
                $this->dbObject('SubmissionFrom')->Format('y-MM-dd'),
                $this->dbObject('SubmissionTo')->Format('y-MM-dd')
            );
        }
    }

    public function sendNotificationEmail()
    {
        $to = ($this->RequestedBy()->exists()) ? $this->RequestedBy()->Email : null;
        if (!$to) {
            return;
        }

        $file = $this->File();
        $subject = $this->Assessment()->Title . ' report is ready!';

        $fromEmail = Config::inst()->get('SiteConfig', 'selfassessment_email_from');

        $email = new Email();
        $email->setFrom($fromEmail);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setBody(sprintf(
            'The report for %s is ready. <a href="%s">Click here</a> to download it.',
            $this->Assessment()->Title,
            $this->File()->AbsoluteLink()
        ));

        $email->send();
    }

    /**
     * Used in gridfield
     */
    public function getStatusLabel()
    {
        if ($this->Status == 'Done' && $this->Completed) {
            return 'Completed on ' . $this->Completed;
        }

        return $this->Status;
    }
}
