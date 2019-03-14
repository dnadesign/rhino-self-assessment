<?php

class SelfAssessment extends RhinoAssessment  {

	private static $singular_name = 'Self Assessment';

	private static $plural_name = 'Self Assessments';

	private static $submission_class = 'SelfAssessmentSubmission';

	private static $hide_ancestor = 'RhinoAssessment';

	private static $db = array(
		'StartTitle' => 'Text',
		'StartContent' => 'HTMLText',
		'EstimatedTime' => 'Varchar(255)',
		'ResultTitle' => 'VArchar(255)',
		'ResultIntro' => 'HTMLText',
		'ResultEmailText' => 'HTMLText',
		'CMNotificationClient' => 'Varchar',
		'CMNotificationList' => 'Varchar',
		'NotificationListCheckboxLabel' => 'Varchar(255)',
		'EmailModalTitle' => 'Varchar(255)',
		'EmailModalContent' => 'HTMLText',
		'EmailReminderModalTitle' => 'Varchar(255)',
		'EmailReminderModalContent' => 'HTMLText'
	);

	private static $has_one = array(
		'Image' => 'Image'
	);

	private static $has_many = array(
		'ResultThemes' => 'ResultTheme',
		'Reports' => 'SelfAssessmentReport'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Search');

		// Campaign Monitor
		$newsletterTitle = HeaderField::create('Newsletter');
		$fields->insertBefore($newsletterTitle, 'CMClient');

		// Note: the newsletter list is set up as part of the UserDefinedForm extension
		$apiKey = SiteConfig::current_site_config()->CampaignMonitorAPIKey;

		if ($apiKey) {
			$notificationTitle = HeaderField::create('Business Email Notification');
			$fields->addFieldToTab('Root.CampaignMonitor', $notificationTitle);

			$resources = new CMResources($apiKey);
			// Get clients under our account
			$clients = $resources->Clients()->map();
			$fields->addFieldToTab('Root.CampaignMonitor', new DropdownField('CMNotificationClient', 'Notification Client', $clients));

			// check if client is available to select
			if($this->CMClient && ($client = $resources->getClient($this->CMClient))) {
				$lists = $client->Lists()->map();
				$fields->addFieldsToTab('Root.CampaignMonitor', array(
					DropdownField::create('CMNotificationList', 'Business Notification List', $lists)->setEmptyString('Select List')
				));
			}

			$notificationListLabel = TextField::create('NotificationListCheckboxLabel');
			$notificationListLabel->setRightTitle('Defaults to "Sign up for Business Notifications.');
			$fields->addFieldToTab('Root.CampaignMonitor', $notificationListLabel);

		} else {
			$fields->addFieldToTab('Root.Main', LiteralField::create('warn', '<p class="message notice">To use CampaignMonitor, please set an api key in the admin settings.</p>'));
		}

		// Reports
		$report_config = GridFieldConfig_RecordEditor::create();

		$add = $report_config->getComponentByType('GridFieldAddNewButton');
		$add->setButtonName('Request New Report');

		$report_config->removeComponentsByType('GridFieldEditButton');

		$reports = GridField::create('Reports', 'Reports', $this->Reports(), $report_config);
		$fields->addFieldToTab('Root.Submissions', $reports, 'Submissions');

		// Start Screen
		$title = Textfield::create('StartTitle', 'Title');
		$content = HTMLEditorField::create('StartContent', 'Content');
		$time = TextField::create('EstimatedTime', 'Estimated Time to complete');
		$fields->addFieldsToTab('Root.StartScreen', array($title, $content, $time));

		// Image
		$image = UploadField::create('Image', 'Image')->setDescription('Square ratio. SVG recommended. Minimum size 300x300px.');
		$image->setAllowedExtensions(array('svg', 'jpg', 'jpeg', 'png'));
		$image->getValidator()->setAllowedMaxFileSize('2M');
		$fields->addFieldToTab('Root.StartScreen', $image, 'StartContent');

		// Results + Themes
		$resultTitle = TextField::create('ResultTitle')->setRightTitle('Defaults to '.sprintf('My %s Results', ucwords($this->Title)));
		$resultIntro =  HTMLEditorField::create('ResultIntro', 'Result Introduction');
		$fields->addFieldsToTab('Root.ResultScreen', array($resultTitle, $resultIntro));

		$config = GridfieldConfig_RecordEditor::create();

		// Allow to sort the questions
		$config->addComponent(new GridFieldOrderableRows('Sort'));
		$gridfield = GridField::create('ResultThemes', 'Result Themes', $this->ResultThemes(), $config);

		$fields->addFieldToTab('Root.ResultScreen', $gridfield);

		// Result Email
		$modalTitle = TextField::create('EmailModalTitle');
		$modalText = HTMLEditorField::create('EmailModalContent');

		// Result Email Reminder
		// This is the text for the modal that shows up when a user click on a link
		// before he has emailed the result page
		$modalTitle2 = TextField::create('EmailReminderModalTitle');
		$modalText2 = HTMLEditorField::create('EmailReminderModalContent');

		// Body of the email
		$resultEmailText = HTMLEditorField::create('ResultEmailText', 'Result Email Text');
		$resultEmailText->setDescription('Content of the email sent alongside the link to the result page.');

		$fields->addFieldsToTab('Root.ResultEmail', array(
			ToggleCompositeField::create('Regular', 'Content of the modal window', array($modalTitle, $modalText)),
			ToggleCompositeField::create('Reminder', 'Content of the modal window when navigating away from the result page', array($modalTitle2, $modalText2)),
			$resultEmailText
		));

		/**
		* Do not allow for inline editing of the title
		* to offer better userflow, since all titles should be required
		*/
		$formfields = $fields->dataFieldByName('Fields');
		$config = $formfields->getConfig();
		$editableColumns = $config->getComponentByType('GridFieldEditableColumns');
		$columns = $editableColumns->getDisplayFields($formfields);
		if (isset($columns['Title'])) {
			$columns['Title'] = function ($record, $column, $grid) {
				if ($record instanceof EditableFormField) {
					return $record->getInlineTitleField($column)->performReadOnlyTransformation();
				}
			};
		}
		$editableColumns->setDisplayFields($columns);

		$content->setRows(20);
		$resultIntro->setRows(25);

		return $fields;
	}

	public function getResultPageTitle(){
		return sprintf('My %s Results', ucwords($this->Title));
	}

	public function TotalQuestionCount() {

		$count = $this->getQuestions()->Count();
		// Add the Business Information step
		return $count + 1;
	}
}

class SelfAssessment_Controller extends RhinoAssessment_Controller {

	private static $allowed_actions = array(
		'Form',
		'EmailSignupForm',
		'finished'
	);

	/**
	* THe public should not be able to access this page directly
	* Instead Redirect them to the first element that reference this assessment.
	* If a user exists, we ar elogged in, so we should see the preview
	*/
	public function index() {
		$loggedIn = Member::currentUserID();

		if ($loggedIn) return parent::index();

		// Find widget with this tool
		$element = ElementSelfAssessment::get()->filter('FormID', $this->ID)->First();
		if ($element) {
			$page = $element->getPage();
			if ($page) {
				return $this->redirect(Controller::join_links($page->AbsoluteLink(), '#e'.$element->ID));
			}
		}

		return $this->httpError(404);
	}


	/**
	* When a user views his results
	* we increase the view count on the submission
	*/
	public function finished() {
		$submission = $this->getSubmission();
		if ($submission) {
			$viewCount = (int) $submission->Views + 1;
			$submission->Views = $viewCount++;
			$submission->write();
		}

		return parent::finished();
	}

	/**
	* Renders form
	*/
	public function Form() {
		$form = UserForm::create($this);
		$form->setAttribute('name', 'SelfAssessmentForm');
		$form->setTemplate('forms/SelfAssessmentForm');
		return $form;
	}

	/**
	* Simulate the Element to render form
	* on the form page itself
	* only when a user is logged in
	*/
    public function FormPreview() {
    	$element = new ElementSelfAssessment();
    	$element->FormID = $this->ID;

    	$area = new ElementalArea();
    	$area->Widgets()->add($element);

        return $area;
    }

	/**
	* If a user has emailed himself the link,
	* the submission will have the UserEmail set
	*/
	public function getHasEmailedResults() {
		$submission = $this->getSubmission();
		if ($submission && $submission->exists() && $submission->UserEmail !== null) {
			return true;
		}

		return false;
	}

	/**
	* Form displayed on result page
	* to allow user to email the result page link to themselves
	* and signup to newsletter and business notification
	*
	* @return Form
	*/
	public function EmailSignupForm() {
		$fields = new FieldList(array(
			$email = EmailField::create('Email', '')->setAttribute('placeholder', 'Enter your email address'),
			$newsletter = CheckboxField::create('SignUpForNewsletter', 'Keep up to date with changes that affect your business. Sign up to the monthly newsletter.', '1'),
			$redirect = HiddenField::create('RedirectURL', 'RedirectURL')
		));

		if ($this->CMNotificationList) {
			$label = ($this->NotificationListCheckboxLabel) ? $this->NotificationListCheckboxLabel : 'Sign up for Business Notifications.';
			$notification = CheckboxField::create('SignUpForNotification', $label, '1');
			$fields->push($notification);
		}

		// Need to include the details about the submission
		// Because they won't be in the url upon submissions
		$submission = $this->getSubmission();
		if ($submission && $submission->exists()) {
			$fields->push(HiddenField::create('SubmissionUID', 'SubmissionUID', $submission->uid));
		}

		$actions = new FieldList(array(
			FormAction::create('processEmailSignup', 'Email me a link')->setUseButtonTag(true)->addExtraClass('pure-button self-assessment-button')
		));

		$required = new RequiredFields(array('Email'));

		$form = new Form($this, 'EmailSignupForm', $fields, $actions, $required);

		return $form;
	}

	public function processEmailSignup($data, $form) {
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
				// Subscribe to newsletter
				if (isset($data['SignUpForNewsletter']) && $data['SignUpForNewsletter'] == 1 ) {
					$this->subscribeToNewsletter($email);
				}
				// Subscribe to Notification
				if (isset($data['SignUpForNotification']) && $data['SignUpForNotification'] == 1 ) {
					$this->signupForNotification($email);
				}

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
	private function sendLinkViaEmail($submission, $emailAddress) {
		$utm_parameters = http_build_query(array(
			'utm_source' => 'view results',
			'utm_medium' => 'email',
			'utm_campaign' => 'self-assessment tool'
		), null, '&', PHP_QUERY_RFC3986);

		$link = $submission->getLink() . '?' . $utm_parameters;
		$config = SiteConfig::current_site_config();

		$email = new Email();
		$email->setFrom($config->ContactEmail);
		$email->setTo($emailAddress);
		$email->setSubject($this->data()->getResultPageTitle());
		$email->setTemplate('SelfAssessmentEmail');

		$data = array(
			'Link' => $link,
			'Title' => $this->data()->getResultPageTitle(),
			'Text' => $this->data()->ResultEmailText,
		);

		// Include Left Logo/Link
		$left = null;
		$logo1 = $config->EmailLogo1();
		$link1 = $config->EmailLogoLink1();

		if ($logo1->exists() && $link1->exists()) {
			$left = sprintf('<a href="%s"><img src="%s" alt="%s" width="120px" height:"48px" style="width:120px" /></a>', $link1->getLinkURL(), $logo1->AbsoluteLink(), $logo1->Title);
		} elseif ($logo1->exists()) {
			$left = sprintf('<img src="%s" alt="%s" width="120px" height:"48px" style="width:120px" />', $logo1->AbsoluteLink(), $logo1->Title);
		} elseif ($link1->exists()) {
			$left = $link1->forTemplate();
		}

		$data['Left'] = $left;

		// Include Right Logo/Link
		$right = null;
		$logo2 = $config->EmailLogo2();
		$link2 = $config->EmailLogoLink2();

		if ($logo2->exists() && $link2->exists()) {
			$right = sprintf('<a href="%s"><img src="%s" alt="%s" width="120px" height:"48px" style="width:120px" /></a>', $link2->getLinkURL(), $logo2->AbsoluteLink(), $logo2->Title);
		} elseif ($logo2->exists()) {
			$right = sprintf('<img src="%s" alt="%s" width="120px" height:"48px" style="width:120px" />', $logo2->AbsoluteLink(), $logo2->Title);
		} elseif ($link2->exists()) {
			$right = $link2->forTemplate();
		}

		$data['Right'] = $right;

		Requirements::clear();
		$email->populateTemplate($data);
		Requirements::restore();

		$email->send();
	}

	/**
	* Subscribe to Campaign monitor newsletter list
	*/
	private function subscribeToNewsletter($email) {
		$apiKey = SiteConfig::current_site_config()->CampaignMonitorAPIKey;
		$resources = new CMResources($apiKey);

		if($resources
			&& $this->CMDefaultList
			&& $list = $resources->getList($this->CMDefaultList)
		){
			// Create subscriber
			$fields = array(
				'EmailAddress' => $email,
				'Resubscribe' => true,
				'RestartSubscriptionBasedAutoresponders' => true
			);

			$subscriber = new CMSubscriber(null, $fields, $list);
			$subscriber->setCustomFields(array('Source' => $this->data()->Title));
			$subscriber->Save();
		}
	}

	/**
	* Signup for the business area notification
	*/
	private function signupForNotification($email) {
		$apiKey = SiteConfig::current_site_config()->CampaignMonitorAPIKey;
		$resources = new CMResources($apiKey);

		if($resources
			&& $this->CMNotificationList
			&& $list = $resources->getList($this->CMNotificationList)
		){
			// Create subscriber
			$fields = array(
				'EmailAddress' => $email,
				'Resubscribe' => true,
				'RestartSubscriptionBasedAutoresponders' => true
			);

			$subscriber = new CMSubscriber(null, $fields, $list);
			$subscriber->setCustomFields(array('Source' => $this->data()->Title));
			$subscriber->Save();
		}
	}

}
