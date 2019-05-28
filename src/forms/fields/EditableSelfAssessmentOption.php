<?php

namespace DNADesign\Rhino\Fields;

use DNADesign\Rhino\Fields\EditableMultiChoiceOption;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;

class EditableSelfAssessmentOption extends EditableMultiChoiceOption
{
    private static $singular_name = 'Self Assessment Question Option';

    private static $table_name = 'EditableSelfAssessmentOption';

    private static $db = [
        'Advice' => 'HTMLText',
        'Rating' => "Enum('1,2,3,4,5', '1')"
    ];

    private static $summary_fields = [
        'Title' => 'Answer',
        'Advice.Summary' => 'Advice',
        'Rating' => 'Rating'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('IsCorrectAnswer');

        // Star rating
        $rating = OptionsetField::create('Rating', 'Rating', $this->dbObject('Rating')->enumValues(), $this->Rating);
        $fields->addFieldToTab('Root.Main', $rating, 'Advice');

        return $fields;
    }

    public function getCMSValidator()
    {
        return new RequiredFields(['Value', 'Rating']);
    }
}
