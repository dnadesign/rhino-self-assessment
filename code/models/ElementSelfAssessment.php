<?php

/**
 * @package elemental
 */
class ElementSelfAssessment extends ElementUserDefinedForm
{
    private static $title = "Self Assessment Element";

	private static $db = array(
		'HTML' => 'HTMLText',
	);

	private static $field_labels = array(
		'HTML' => 'Content'
    );
    
    private static $enable_title_in_template = true;

    private static $defaults = array(
        'ElementSocialOn' => '1'
    );

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