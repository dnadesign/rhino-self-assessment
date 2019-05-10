<?php

namespace DNADesign\Rhino\Model;

class SelfAssessmentSubmission extends RhinoSubmittedAssessment
{
    private static $db = [
        'UserEmail' => 'Varchar(255)'
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'Created' => 'Submitted on',
        'SubmittedBy.Title' => 'SubmittedBy'
    ];
}
