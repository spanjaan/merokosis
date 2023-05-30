<?php

namespace Spanjaan\Workspace\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

/**
 * Records Backend Controller
 */
class Records extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Spanjaan.Workspace', 'workspace', 'records');
    }

    public function create()
    {
        BackendMenu::setContextSideMenu('new_record');

        return $this->asExtension('FormController')->create();
    }

    public function preview($id)
    {
        $record = $this->formFindModelObject($id);

        // Initialize the ListController
        $this->makeLists();

        // Get the position of the current record within the current list set
        $listQuery = $this->listGetWidget()->prepareQuery();
        $totalRecords = $listQuery->count();
        \DB::statement(\DB::raw('set @row_num=0'));
        $listQuery->selectRaw('@row_num:= @row_num + 1 as `record_position`');

        $previousId = 0;
        $nextId = 0;
        $currentIndex = null;
        // Note, if you have few records overall but massive sizes per record you can use
        // $listQuery->cursor() to make one DB query per record instead of loading them all at once
        foreach ($listQuery->get() as $listRecord) {
            if ($listRecord->getKey() === $record->getKey()) {
                $currentIndex = $listRecord->record_position;
                continue;
            } elseif ($currentIndex) {
                $nextId = $listRecord->getKey();
                break;
            } else {
                $previousId = $listRecord->getKey();
            }
        }

        $this->vars = array_merge($this->vars, [
            'previousUrl'  => $previousId ? Backend::url('spanjaan/workspace/records/preview/' . $previousId) : '',
            'nextUrl'      => $nextId ? Backend::url('spanjaan/workspace/records/preview/' . $nextId) : '',
            'currentIndex' => $currentIndex,
            'totalRecords' => $totalRecords,
            'updateUrl'    => Backend::url('spanjaan/workspace/records/update/' . $id),

        ]);

        return $this->asExtension('FormController')->preview($id);
    }
}
