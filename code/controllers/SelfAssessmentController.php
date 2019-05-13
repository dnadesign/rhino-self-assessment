<?php

namespace DNADesign\Rhino\Control;

use DNADesign\Elemental\Models\ElementalArea;
use DNADesign\Rhino\Elements\ElementSelfAssessment;
use DNADesign\Rhino\Model\SelfAssessmentSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\UserForm;
use SilverStripe\View\Requirements;
use SilverStripe\Control\HTTPRequest;

class SelfAssessmentController extends RhinoAssessmentController
{
    private static $allowed_actions = [
        'Form',
        'EmailSignupForm'
    ];

    /**
     * The public should not be able to access this page directly, instead Redirect them to the first element
     * that reference this assessment. If a user exists, we ar elogged in, so we should see the preview
     */
    public function index(HTTPRequest $request = null)
    {
        $loggedIn = Member::currentUserID();

        if ($loggedIn) {
            return parent::index();
        }

        // Find widget with this tool
        $element = ElementSelfAssessment::get()->filter('FormID', $this->ID)->First();
        if ($element) {
            $page = $element->getPage();
            if ($page) {
                return $this->redirect(Controller::join_links($page->AbsoluteLink(), '#e' . $element->ID));
            }
        }

        return $this->httpError(404);
    }

    /**
     * Renders form
     */
    public function Form()
    {
        $form = UserForm::create($this);
        $form->setAttribute('name', 'SelfAssessmentForm');
        $form->setTemplate('forms/SelfAssessmentForm');

        return $form;
    }

    /**
     * Simulate the Element to render form on the form page itself only when a user is logged in
     */
    public function FormPreview()
    {
        $element = new ElementSelfAssessment();
        $element->FormID = $this->ID;

        $area = new ElementalArea();
        $area->Widgets()->add($element);

        return $area;
    }

    /**
     * Form displayed on result page to allow user to email the result page link to themselves
     *
     * @return Form
     */
    public function EmailSignupForm()
    {
        $fields = new FieldList([
            $email = EmailField::create('Email', '')->setAttribute('placeholder', 'Enter your email address'),
            $redirect = HiddenField::create('RedirectURL', 'RedirectURL')
        ]);

        // Need to include the details about the submission, because they won't be in the url upon submissions.
        $submission = $this->getSubmission();
        if ($submission && $submission->exists()) {
            $fields->push(HiddenField::create('SubmissionUID', 'SubmissionUID', $submission->uid));
        }

        $actions = new FieldList([
            FormAction::create('processEmailSignup',
                'Email me a link')->setUseButtonTag(true)->addExtraClass('pure-button self-assessment-button')
        ]);

        $required = new RequiredFields(['Email']);

        $form = new Form($this, 'EmailSignupForm', $fields, $actions, $required);

        return $form;
    }

    public function processEmailSignup($data, $form)
    {
        $email = (isset($data['Email'])) ? $data['Email'] : null;
        $name = (isset($data['Name'])) ? $data['Name'] : null;
        $submissionID = (isset($data['SubmissionUID'])) ? $data['SubmissionUID'] : null;

        if ($email && $submissionID) {
            $submission = SelfAssessmentSubmission::get_by_uid($submissionID);

            if ($submission) {
                // record email against submission
                $submission->UserEmail = $email;
                $submission->write();
                // Send email
                $this->sendLinkViaEmail($submission, $email, $name);

                if (!empty($data['RedirectURL'])) {
                    return $this->redirect($data['RedirectURL']);
                }

                return $this->redirect(Controller::join_links($submission->getLink(), '?signup=1'));
            }

            return $this->htppError(404);
        }

        return $this->htppError(500);
    }

    /**
     * Send an email to the user with the link to this submission
     */
    private function sendLinkViaEmail($submission, $emailAddress, $name)
    {
        $link = $submission->getLink();

        $email = new Email();
        $email->setFrom($this->data()->ContactEmail);
        $email->setTo($emailAddress);
        $email->setSubject($this->data()->getResultPageTitle());
        $email->setTemplate('SelfAssessmentResultsEmail');

        $data = [
            'Name' => $name,
            'Link' => $link,
            'TopLogo' => $this->TopLogo(),
            'FooterLogo' => $this->FooterLogo(),
            'IntroText' => $this->data()->ResultEmailIntroText,
            'Text' => $this->data()->ResultEmailText,
        ];

        Requirements::clear();
        $email->populateTemplate($data);
        Requirements::restore();

        $email->send();
    }
}
