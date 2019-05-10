<?php

namespace DNADesign\Rhino\Pagetypes;

use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\ToggleCompositeField;

class SelfAssessment extends RhinoAssessment {

	private static $singular_name = 'Self Assessment';

	private static $plural_name = 'Self Assessments';

	private static $description = 'A quiz/self-assessment tool for inclusion in the Self-Assessment Element (add at root level)';

	private static $submission_class = 'SelfAssessmentSubmission';

	private static $hide_ancestor = 'RhinoAssessment';

	private static $db = [
		'StartTitle' => 'Text',
		'StartContent' => 'HTMLText',
		'EstimatedTime' => 'Varchar(255)',
		'ResultTitle' => 'VArchar(255)',
		'ResultIntro' => 'HTMLText',
		'ResultEmailText' => 'HTMLText',
		'EmailModalTitle' => 'Varchar(255)',
		'EmailModalContent' => 'HTMLText'
	];

	private static $has_one = [
		'Image' => 'Image',
		'TopLogo' => 'Image',
		'FooterLogo' => 'Image'
	];

	private static $has_many = [
		'ResultThemes' => 'ResultTheme',
		'Reports' => 'SelfAssessmentReport'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Start Screen
		$title = Textfield::create('StartTitle', 'Title');
		$image = UploadField::create('Image', 'Image')
			->setAllowedExtensions(['svg', 'jpg', 'jpeg', 'png']);
		$image->getValidator()->setAllowedMaxFileSize('2M');
		$content = HTMLEditorField::create('StartContent', 'Content');
		$time = TextField::create('EstimatedTime', 'Estimated Time to complete');
		$fields->addFieldsToTab('Root.StartScreen', [$title, $image, $content, $time]);

		// Result screen + Themes
		$resultTitle = TextField::create('ResultTitle')
			->setRightTitle('Defaults to '.sprintf('My %s Results', ucwords($this->Title)));
		$resultIntro =  HTMLEditorField::create('ResultIntro', 'Result Introduction');
		$themesConfig = GridfieldConfig_RecordEditor::create();
		$themesConfig->addComponent(new GridFieldOrderableRows('Sort'));
		$themesGrid = GridField::create('ResultThemes', 'Result Themes', $this->ResultThemes(), $themesConfig);
		$fields->addFieldsToTab('Root.ResultScreen', [$resultTitle, $resultIntro, $themesGrid]);

		// Result Email
		$modalTitle = TextField::create('EmailModalTitle');
		$modalText = HTMLEditorField::create('EmailModalContent');
		$resultEmailText = HTMLEditorField::create('ResultEmailText', 'Result Email Text');
		$resultEmailText->setDescription('Content of the email sent alongside the link to the result page.');
		$fields->addFieldsToTab('Root.ResultEmail', [
			ToggleCompositeField::create('Regular', 'Content of the modal window', [$modalTitle, $modalText]),
			$resultEmailText
		]);

		// Reports
		$report_config = GridFieldConfig_RecordEditor::create();
		$report_config->addComponent(new GridfieldDownloadReportButton());
		$add = $report_config->getComponentByType('GridFieldAddNewButton');
		$add->setButtonName('Request New Report');
		$report_config->removeComponentsByType('GridFieldEditButton');
		$reports = GridField::create('Reports', 'Reports', $this->Reports(), $report_config);
		$fields->addFieldToTab('Root.Submissions', $reports, 'Submissions');

		// Do not allow for inline editing of the title to offer better userflow, since all titles should be required
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
		// TODO: Remove this and the countering in js that is done to account for it
		return $count + 1;
	}
}
