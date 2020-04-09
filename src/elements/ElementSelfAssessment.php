<?php

namespace DNADesign\Rhino\Elements;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Rhino\Control\SelfAssessmentController;
use DNADesign\Rhino\Pagetypes\SelfAssessment;
use SilverStripe\Control\Controller;

/**
 * @package elemental
 */
class ElementSelfAssessment extends BaseElement
{
    private static $title = "Self Assessment Element";

    private static $enable_title_in_template = true;

    private static $table_name = 'ElementSelfAssessment';

    private static $has_one = array(
        'SelfAssessment' => SelfAssessment::class
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
        if ($this->SelfAssessment()->exists()) {

            $controller = new SelfAssessmentController($this->SelfAssessment());
            $controller->init();

            // We want to redirect to the result page upon submission
            // so do render the element controller when the "finished" action
            // is detected as opposed to ElementUserDefinedForm
            $form = $controller->Form();
            $form->setFormAction(Controller::join_links($controller->Link(), 'Form'));

            return $form;
        }
    }

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'SelfAssessment');
    }
}
