<?php

namespace DNADesign\Rhino\Fields;

use DNADesign\Rhino\Fields\EditableMultiChoiceField;
use DNADesign\Rhino\Fields\EditableSelfAssessmentOption;
use DNADesign\Rhino\Model\ResultTheme;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;

class SelfAssessmentQuestion extends EditableMultiChoiceField
{
    private static $singular_name = 'Self Assessment Question';

    private static $optionClass = EditableSelfAssessmentOption::class;

    private static $table_name = 'SelfAssessmentQuestion';

    private static $casting = [
        'Options' => 'EditableSelfAssessmentOption'
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'Title' => 'Title'
    ];

    private static $defaults = [
        'Required' => '1'
    ];

    private static $db = [
        'TidbitTitle' => 'Varchar(255)',
        'Tidbit' => 'HTMLText'
    ];

    private static $has_one = [
        'ResultTheme' => ResultTheme::class,
        'TidbitImage' => File::class
    ];

    private static $owns = [
        'TidbitImage'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'RightTitle',
            'ExtraClass',
            'DisplayRules',
            'CustomErrorMessage',
            'ShowInSummary',
            'warning',
            'Validation',
            'Questions'
        ]);

        $tidbitTitle = TextField::create('TidbitTitle')->setRightTitle('eg: Did you know...');
        $tidbit = HTMLEditorField::create('Tidbit', 'Tidbit');
        $fields->addFieldsToTab('Root.Tidbit', [$tidbitTitle, $tidbit]);

        $image = UploadField::create(
            'TidbitImage',
            'Tidbit Image'
        )->setDescription('Square ratio. SVG recommended. Minimum size 300x300px.');
        $image->setAllowedExtensions(['svg', 'jpg', 'jpeg', 'png']);
        $image->getValidator()->setAllowedMaxFileSize('2M');
        $fields->addFieldToTab('Root.Tidbit', $image);

        return $fields;
    }

    /**
     * A question without a question is not a question
     * so make title required
     */
    public function getCMSValidator()
    {
        $validator = parent::getCMSValidator();
        $validator->addRequiredField('Title');

        return $validator;
    }

    public function getFormField()
    {
        $field = parent::getFormField();

        $field->setFieldHolderTemplate('DNADesign\Rhino\Fields\SelfAssessmentQuestion_holder');
        $field->setTemplate('DNADesign\Rhino\Fields\SelfAssessmentQuestion');

        $field->customise([
            'Image' => $this->Image(),
            'TidbitTitle' => $this->TidbitTitle,
            'Tidbit' => $this->dbObject('Tidbit'),
            'TidbitImage' => $this->TidbitImage(),
            'ResultTheme' => $this->ResultTheme(),
            'SelfAssessmentTitle' => $this->Parent()->Title,
            'TotalQuestionCount' => $this->Parent()->TotalQuestionCount(),
            'Options' => $this->Options(),
            'Last' => $this->getIsLastQuestion()
        ]);

        return $field;
    }

    public function getIsLastQuestion()
    {
        $fields = $this->Parent()->Fields()->column('ID');
        return array_search($this->ID, $fields) === count($fields) - 1;
    }

    /**
     * Return the advice from a submittedFormField
     */
    public function getAdviceForAnswer($answer)
    {
        $option = $this->Options()->filter('Value', $answer->Value)->First();
        if ($option) {
            return $option->Advice;
        }

        return null;
    }

    /**
     * Return the rating from a submittedFormField
     */
    public function getRatingForAnswer($answer)
    {
        $option = $this->Options()->filter('Value', $answer->Value)->First();
        if ($option) {
            return $option->Rating;
        }

        return null;
    }
}
