<?php

namespace DNADesign\Rhino\Gridfield;

use SelfAssessmentReport;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;

class GridfieldDownloadReportButton implements GridField_ColumnProvider, GridField_ActionProvider
{
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'col-buttons'];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => ''];
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        if (!$record->File()->exists()) {
            return;
        }

        $field = GridField_FormAction::create(
            $gridField,
            'DownloadFile',
            'Download',
            "downloadfile",
            ['RecordID' => $record->ID]
        );

        $field->addExtraClass('no-ajax');

        return $field->Field();
    }

    public function getActions($gridField)
    {
        return ['downloadfile'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'downloadfile') {
            if (isset($arguments['RecordID']) && $arguments['RecordID'] > 0) {
                $report = SelfAssessmentReport::get()->byID($arguments['RecordID']);
                if ($report) {
                    $file = $report->File();
                    if ($file->exists()) {
                        $content = file_get_contents($file->getFullPath());

                        return HTTPRequest::send_file($content, $file->FileName);
                    }
                }
            }
        }
    }
}
