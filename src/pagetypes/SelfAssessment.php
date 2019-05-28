<?php

namespace DNADesign\Rhino\Pagetypes;

use DNADesign\Rhino\Control\SelfAssessmentController;
use DNADesign\Rhino\Gridfield\GridFieldRequestDeleteTestData;
use DNADesign\Rhino\Gridfield\GridfieldDownloadReportButton;
use DNADesign\Rhino\Model\ResultTheme;
use DNADesign\Rhino\Model\SelfAssessmentReport;
use DNADesign\Rhino\Model\SelfAssessmentSubmission;
use GraphQL\Error\Debug;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\UserForm;
use SilverStripe\View\ArrayData;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class SelfAssessment extends RhinoAssessment {

	private static $singular_name = 'Self Assessment';

	private static $plural_name = 'Self Assessments';

	private static $description = 'A quiz/self-assessment tool for inclusion in the Self-Assessment Element (add at root level)';

	private static $submission_class = SelfAssessmentSubmission::class;

    private static $table_name = 'SelfAssessment';

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

	private static $owns =[
	    'Image',
        'TopLogo',
        'FooterLogo'
    ];

	private static $has_many = [
		'ResultThemes' => ResultTheme::class,
		'Reports' => SelfAssessmentReport::class
	];

    /**
     * Self Assessment are pages, that will be later included via an element so they need to be hidden by default
     */
    private static $defaults = [
        'ShowInMenus' => false,
        'ShowInSearch' => false,
        'SubmitButtonText' => "Show my results"
    ];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

        // Clean up
        $fields->removeByName([
            'Metadata',
            'FeedbackOnSubmission',
            'Recipients',
            'MenuTitle',
            'Content'
        ]);

		// Start Screen
		$title = Textfield::create('StartTitle', 'Title');
		$image = UploadField::create('Image', 'Image')
			->setAllowedExtensions(['svg', 'jpg', 'jpeg', 'png']);
		$image->getValidator()->setAllowedMaxFileSize('2M');
		$content = HTMLEditorField::create('StartContent', 'Content');
		$time = TextField::create('EstimatedTime', 'Estimated Time to complete');
		$fields->addFieldsToTab('Root.StartScreen', [$title, $image, $content, $time]);

        // Fields
        $formFields = $fields->dataFieldByName('Fields');
        $this->modifyGridField($formFields);
        $fields->addFieldToTab('Root.Main', $formFields);

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
		$add = $report_config->getComponentByType(GridFieldAddNewButton::class);

        $add->setButtonName('Request New Report');

		$report_config->removeComponentsByType('GridFieldEditButton');
		$reports = GridField::create('Reports', 'Reports', $this->Reports(), $report_config);
		$fields->addFieldToTab('Root.Reports', $reports);

		$content->setRows(20);
		$resultIntro->setRows(25);

        // Add DeleteTestData action to submission
        $submissions = $fields->fieldByName('Root.Submissions.Submissions');
        $config = $submissions->getConfig();
        $config->addComponent(new GridFieldRequestDeleteTestData());

		return $fields;
	}

    /**
    * Remove unwanted controls on the form field gridfield
    * and make sure that when adding a field, it's class is set properly
    */
    public function modifyGridfield($gridField)
    {
        $config = $gridField->getConfig();

        // Remove Field Group and Page break button
        $adders = $config->getComponentsByType(GridFieldAddClassesButton::class)
            ->filterByCallBack(function ($item) {
                $classes = $item->getClasses();

                return (is_array($classes) && !in_array(EditableFormStep::class, $classes) && !in_array(EditableFieldGroup::class,
                        $classes));
            });

        $config->removeComponentsByType(GridFieldAddClassesButton::class);

        // Make sure the adder button adds a field of the first class available in the dropdown
        $adder = $adders->First();
        $allowedFieldTypes = $this->owner->config()->allowed_field_types;
        if (!$allowedFieldTypes) {
            $allowedFieldTypes = singleton(EditableFormField::class)->getEditableFieldClasses();
        }

        // Check if we have at least one field type allowed and set the button to create this field type
        $firstAllowedFieldType = (is_array($allowedFieldTypes) && isset($allowedFieldTypes[0])) ? $allowedFieldTypes[0] : [];
        $adder->setClasses($firstAllowedFieldType);

        // Re-include add buttons
        foreach ($adders->toArray() as $component) {
            $config->addComponent($component);
        }
    }

    /**
     * We don't want to be able to save a SelfAssesment which contains questions without a title Title field is required
     * on SelfAssessmentQuestion but because of inline editing, it is possible to save the page with blank question
     */
    public function validate()
    {
        $result = parent::validate();

        if ($this->isInDB()) {

           // Look for fields without a title
           $blankFields = $this->owner->Fields()->where('Title IS NULL')->Count();

           if ($blankFields > 0) {
               $result->addError("Questions must have a title ($blankFields missing)", 'validation');
           }
       }

       return $result;
    }

	public function getResultPageTitle()
    {
		return sprintf('My %s Results', ucwords($this->Title));
	}

	public function TotalQuestionCount() {
		$count = $this->getQuestions()->Count();
		// Add the Business Information step
		// TODO: Remove this and the countering in js that is done to account for it
		return $count + 1;
	}
}