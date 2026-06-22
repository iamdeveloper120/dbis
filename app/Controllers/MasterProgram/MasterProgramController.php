<?php

namespace App\Controllers\MasterProgram;

use App\Controllers\AdminController;
use App\Models\MasterProgram\MasterProgramViewModel;
use App\Entities\MasterProgram\MasterDomain;


class MasterProgramController extends AdminController
{
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new MasterProgramViewModel();
    }
    public function index()
    {
        $this->page_title = 'Master Program Setups';
        $masterProgram = $this->model->getMasterProgramTree();
        return  view('MasterProgram/program_view', ['masterProgram' => $masterProgram, 'page_title' => $this->page_title]);
    }

    public function list()
    {
        $this->page_title = 'Master Program Setups';
        $masterProgram = $this->model->getMasterProgramTreeForReact();
        return $this->response->setJSON(['masterProgram' => $masterProgram]); 

    }
    /************************************************************************* */
}
