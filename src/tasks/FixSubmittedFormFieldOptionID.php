<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Dev\BuildTask;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class FixSubmittedFormFieldOptionID extends BuildTask
{
    protected $title = 'Fix Submitted Form Field ParentIDs';

    protected $description = 'Re-save all SubmittedFormField to populate the missing ParentFieldId and ParentOptionID';

    public function run($request)
    {
        $fields = SubmittedFormField::get()->filter('ParentFieldID', '0');

        echo sprintf('Updating %s fields...', $fields->count());

        foreach ($fields as $field) {
            $submission = $field->Parent();

            $source = EditableFormField::get()->filter([
                'ParentID' => $submission->ParentID,
                'Name' => $field->Name
            ])->First();

            if ($source) {
                $field->onPopulationFromField($source);
                $field->write();
            }
        }

        echo 'Done!';
    }
}
