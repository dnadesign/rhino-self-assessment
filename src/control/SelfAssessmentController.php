<?php

namespace DNADesign\Rhino\Control;

use DNADesign\Rhino\Forms\RhinoUserForm;
use DNADesign\Rhino\Model\SelfAssessmentSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\View\Requirements;

class SelfAssessmentController extends RhinoAssessmentController
{
    public $IncludeFormTag = false;

    private static $include_default_javascript = true;

    private static $include_default_css = true;

    private static $allowed_actions = [
        'Form',
        'EmailSignupForm'
    ];

    public function init()
    {
        parent::init();

        if ($this->config()->include_default_javascript) {
            Requirements::javascript('dnadesign/rhino-self-assessment:resources/js/self-assessment.src.js');
        }
        if ($this->config()->include_default_css) {
            Requirements::css('dnadesign/rhino-self-assessment:resources/css/self-assessment.css');
        }
    }

    /**
     * Renders form
     */
    public function Form()
    {
        $form = RhinoUserForm::create($this);
        $form->setAttribute('name', 'SelfAssessmentForm');
        $form->setTemplate('DNADesign\Rhino\Forms\SelfAssessmentForm');

        return $form;
    }

    /**
     * Form displayed on result page to allow user to email the result page link to themselves
     *
     * @return Form
     */
    public function EmailSignupForm()
    {
        $fields = new FieldList([
            EmailField::create('Email', '')->setAttribute('placeholder', 'Enter your email address')
        ]);

        // Need to include the details about the submission, because they won't be in the url upon submissions.
        $submission = $this->getSubmission();
        if ($submission && $submission->exists()) {
            $fields->push(HiddenField::create('SubmissionUID', 'SubmissionUID', $submission->uid));
        }

        $actions = new FieldList([
            FormAction::create(
                'processEmailSignup',
                'Email me a link'
            )->setUseButtonTag(true)->addExtraClass('self-assessment-button')
        ]);

        $required = new RequiredFields(['Email']);

        $form = new Form($this, 'EmailSignupForm', $fields, $actions, $required);

        $this->extend('updateEmailSignupForm', $form);

        return $form;
    }

    public function processEmailSignup($data, $form)
    {
        $email = (isset($data['Email'])) ? $data['Email'] : null;
        $submissionID = (isset($data['SubmissionUID'])) ? $data['SubmissionUID'] : null;

        if ($email && $submissionID) {
            $submission = SelfAssessmentSubmission::get_by_uid($submissionID);

            if ($submission) {
                // record email against submission
                $submission->UserEmail = $email;
                $submission->write();
                // Send email
                $this->sendLinkViaEmail($submission, $email);
                // Update
                $this->extend('onAfterProcessEmailSignup', $email, $data, $submission);
                // Redirect to result page with flag
                if (Director::is_ajax()) {
                    return true;
                }

                return $this->redirect(Controller::join_links($submission->getLink(), '?sent=1'));
            }

            return $this->htppError(404);
        }

        return $this->htppError(500);
    }

    /**
     * Send an email to the user with the link to this submission
     */
    private function sendLinkViaEmail($submission, $emailAddress)
    {
        $link = $submission->getLink();

        $email = new Email();

        if ($this->data()->ContactEmail) {
            $email->setFrom($this->data()->ContactEmail);
        }
        $email->setTo($emailAddress);
        $email->setSubject($this->data()->getResultPageTitle());
        $email->setHTMLTemplate('DNADesign\Rhino\SelfAssessmentResultsEmail');

        $data = [
            'Link' => $link,
            'Text' => $this->data()->ResultEmailText
        ];

        $email->setData($data);

        $this->extend('onBeforeSendLinkViaEmail', $email, $submission, $emailAddress);
        $email->send();
    }

    /**
     * Check if email has been sent
     * 
     * @return Boolean
     */
    public function getEmailSent()
    {
        return $this->getRequest()->getVar('sent');
    }
}
