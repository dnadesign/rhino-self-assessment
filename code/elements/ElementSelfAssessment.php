<?php

namespace DNADesign\Rhino\Elements;

use DNADesign\ElementalUserForms\Model\ElementForm;
use DNADesign\Rhino\Control\SelfAssessmentController;
use DNADesign\Rhino\Pagetypes\SelfAssessment;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;

/**
 * @package elemental
 */
class ElementSelfAssessment extends ElementForm
{
    private static $title = "Self Assessment Element";

    private static $enable_title_in_template = true;

    private static $exclude_from_content = true;

    private static $table_name = 'ElementSelfAssessment';

    //TODO: SS4 - check not needed
    // SS3 achieves this somehow without showing all the fields of the self assessment in the element
//    public function getCMSFields()
//    {
//        $fields = parent::getCMSFields();
//
//        $fields->removeByName([
//            'Root.Configuration'
//        ]);
//
//        $fields->addFieldsToTab('Root.Main', [
//            DropdownField::create('s', 's', SelfAssessment::get()->map())
//        ]);
//
//        return $fields;
//    }

    public function ElementForm()
    {
        if ($this->Form()->exists()) {
            $controller = SelfAssessmentController::create($this->Form());

            $current = Controller::curr();

            // We want to redirect to the result page upon submission
            // so do render the element controller when the "finished" action
            // is detected as opposed to ElementUserDefinedForm

            $form = $controller->Form();

            return $form;
        }
    }

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'SelfAssessment');
    }
}
