<?php

namespace DNADesign\Rhino\Forms;

use SilverStripe\UserForms\Form\UserForm;

class RhinoUserForm extends UserForm
{
    /**
     * Push fields into the RequiredFields array if they are used by any Email recipients.
     * Ignore if there is a backup i.e. the plain string field is set
     *
     * @return array required fields names
     */
    protected function getEmailRecipientRequiredFields()
    {
        $requiredFields = [];

        return $requiredFields;
    }
}
