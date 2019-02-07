<?php

/**
 * @package elemental
 */
class ElementSelfAssessment extends ElementUserDefinedForm
{
    private static $title = "Self Assessment Element";

    private static $enable_title_in_template = true;

	public function ElementForm()
    {
        if ($this->Form()->exists()) {
            $controller = new SelfAssessment_Controller($this->Form());

            $current = Controller::curr();

            // We want to redirect to the result page upon submission
            // so do render the element controller when the "finished" action
            // is detected as opposed to ElementUserDefinedForm

            $form = $controller->Form();

            return $form;
        }
    }
}
