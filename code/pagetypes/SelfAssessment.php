<?php

namespace DNADesign\Rhino\Pagetypes;

use DNADesign\Rhino\Control\SelfAssessmentController;
use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\ToggleCompositeField;
use DNADesign\Rhino\Model\SelfAssessmentSubmission;
use DNADesign\Rhino\Model\ResultTheme;
use SilverStripe\Assets\Image;
use DNADesign\Rhino\Model\SelfAssessmentReport;
use DNADesign\Rhino\Gridfield\GridfieldDownloadReportButton;

class SelfAssessment extends RhinoAssessment {

	private static $singular_name = 'Self Assessment';

	private static $plural_name = 'Self Assessments';

	private static $description = 'A quiz/self-assessment tool for inclusion in the Self-Assessment Element (add at root level)';

	private static $submission_class = SelfAssessmentSubmission::class;

    private static $table_name = 'SelfAssessment';

	private static $hide_ancestor = 'RhinoAssessment';

    private static $controller_name = SelfAssessmentController::class;

	private static $db = [
		'StartTitle' => 'Text',
		'StartContent' => 'HTMLText',
		'EstimatedTime' => 'Varchar(255)',
		'ResultTitle' => 'Varchar(255)',
		'ResultIntro' => 'HTMLText',
		'ResultEmailText' => 'HTMLText',
		'EmailModalTitle' => 'Varchar(255)',
		'EmailModalContent' => 'HTMLText'
	];

	private static $has_one = [
		'Image' => Image::class,
		'TopLogo' => Image::class,
		'FooterLogo' => Image::class
	];

	private static $has_many = [
		'ResultThemes' => ResultTheme::class,
		'Reports' => SelfAssessmentReport::class
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
//TODO: SS4 - rhino
//		$report_config->addComponent(GridfieldDownloadReportButton::create());
//		$add = $report_config->getComponentByType('GridFieldAddNewButton');
//TODO: SS4 - rhino
//$add->setButtonName('Request New Report');

		$report_config->removeComponentsByType('GridFieldEditButton');
		$reports = GridField::create('Reports', 'Reports', $this->Reports(), $report_config);
		$fields->addFieldToTab('Root.Submissions', $reports, 'Submissions');

		// Do not allow for inline editing of the title to offer better userflow, since all titles should be required
		$formfields = $fields->dataFieldByName('Fields');
		$config = $formfields->getConfig();
		$editableColumns = $config->getComponentByType('GridFieldEditableColumns');

		//TODO: SS4 - rhino
//		$columns = $editableColumns->getDisplayFields($formfields);
//
//		if (isset($columns['Title'])) {
//			$columns['Title'] = function ($record, $column, $grid) {
//				if ($record instanceof EditableFormField) {
//					return $record->getInlineTitleField($column)->performReadOnlyTransformation();
//				}
//			};
//		}
//		$editableColumns->setDisplayFields($columns);

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
