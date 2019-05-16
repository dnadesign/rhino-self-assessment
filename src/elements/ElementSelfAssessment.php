<?php

namespace DNADesign\Rhino\Elements;

use DNADesign\ElementalUserForms\Model\ElementForm;
use DNADesign\Rhino\Control\SelfAssessmentController;
use DNADesign\Rhino\Pagetypes\SelfAssessment;

/**
 * @package elemental
 */
class ElementSelfAssessment extends ElementForm
{
    private static $title = "Self Assessment Element";

    private static $enable_title_in_template = true;

    // Using the HTML5 module in conjunction with elemental and using $SVG() in the elements template gave an error on
    // save (`getelementsbytagname does not exist on SS_HTML5Value`).
    private static $exclude_from_content = true;

    private static $table_name = 'ElementSelfAssessment';

    private static $has_one = array(
        'Form' => SelfAssessment::class
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'FormFields',
            'FormOptions',
            'Submissions',
            'Recipients'
        ]);

        return $fields;
    }

    public function ElementForm()
    {
        if ($this->Form()->exists()) {

            $controller = SelfAssessmentController::create($this->Form());

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
