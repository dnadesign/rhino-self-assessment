<?php

namespace DNADesign\Rhino\Elements;

use ElementUserDefinedForm;
use SelfAssessmentController;
use SilverStripe\Control\Controller;

/**
 * @package elemental
 */
class ElementSelfAssessment extends ElementUserDefinedForm
{
    private static $title = "Self Assessment Element";

    private static $enable_title_in_template = true;

    private static $exclude_from_content = true;

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
}
