<?php

namespace DNADesign\Rhino\Model;

use DNADesign\Rhino\Reports\CreateSelfAssessmentReportJob;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\Security\Member;
use DNADesign\Rhino\Pagetypes\SelfAssessment;
use SilverStripe\Assets\File;

class SelfAssessmentReport extends DataObject
{
    private static $default_task = 'CreateSelfAssessmentReportJob';

    private static $field_classes_to_include = ['SelfAssessmentQuestion'];

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

        $from = $fields->fieldByName('Root.Main.SubmissionFrom');

        //TODO: SS4 - check not needed
//        $from->getDateField()->setConfig('showcalendar', 1);
//        $from->getDateField()->setValue(date('Y-m-d'));
//        $from->getTimeField()->setValue('00:00:00');

        $to = $fields->fieldByName('Root.Main.SubmissionTo');

        //TODO: SS4 - check not needed
//        $to->getDateField()->setConfig('showcalendar', 1);
//        $to->getDateField()->setValue(date('Y-m-d'));
//        $to->getTimeField()->setValue('23:59:00');

        return $fields;
    }

    public function validate()
    {
        $valid = parent::validate();
//
//        if (!$this->getSubmissions() || !$this->getSubmissions()->exists()) {
//            $valid = $valid->error('This report has no submission. Please change the date parameters and/or include test data. ');
//        }
//
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
            singleton('QueuedJobService')->queueJob($job);
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
                    'Created:GreaterThan' => $this->dbObject('SubmissionFrom')->format('Y-m-d H:i:s'),
                ]);
            }

            if ($this->SubmissionTo) {
                $submissions = $submissions->filter([
                    'Created:LessThan' => $this->dbObject('SubmissionTo')->format('Y-m-d H:i:s')
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
            $fieldToReportOn = $assessment->Fields()->filter('ClassName', $this->stat('field_classes_to_include'));

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

    public function find_or_create_file()
    {
        $filetitle = $this->file_title();
        $folder = $this->file_folder();

        $filename = ($folder && $filetitle) ? sprintf('%s%s', $folder->getFullPath(), $filetitle) : null;

        if ($filename) {
            $file = File::find($filename);

            if (!$file) {
                $file = new File();
                $file->setFilename($filename);
                $file->setParentID($folder->ID);
                $file->write();
            }

            return $file;
        }

        return null;
    }

    public function file_folder()
    {
        $assessment = $this->Assessment();
        if ($assessment && $assessment->exists()) {
            $path = sprintf($this->stat('file_path'), $assessment->URLSegment);

            return Folder::find_or_make($path);
        }
    }

    public function file_title()
    {
        $assessment = $this->Assessment();
        if ($assessment && $assessment->exists()) {
            return sprintf('%s(%s)--%s--to--%s.csv', $assessment->URLSegment, $this->ID,
                $this->dbObject('SubmissionFrom')->Format('d-m-Y'), $this->dbObject('SubmissionTo')->Format('d-m-Y'));
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
        $email->setFrom($fromEmail[0]);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setBody(sprintf('The report for %s is ready. <a href="%s">Click here</a> to download it.',
            $this->Assessment()->Title, $this->File()->AbsoluteLink()));

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
