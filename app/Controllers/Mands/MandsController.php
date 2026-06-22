<?php

namespace App\Controllers\Mands;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Mands\MandsSessionDataModel;
use App\Entities\Mands\MandsSessionData;

class MandsController extends AdminController
{
    use ResponseTrait;
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new MandsSessionDataModel();
    }
    /************************************************************************* */
    public function index()
    {
        return  '';
    }
    /************************************************************************* */
    public function dataSheetList()
    {
        $clientId = $this->request->getPost('clientId');
        $mandsSummaryData = $this->model->getSummaryData($clientId);

        // Pass data to the view
        $data = [
            'mandsSummaryData' => $mandsSummaryData,
        ];

        return view('admin/mandsprogram/dataSheet/summary_data', $data);
    }
    /************************************************************************* */
    public function dailyData()
    {
        $client_id = $this->request->getPost('client_id');
        $session_date = $this->request->getPost('session_date');
        $mandsData = $this->model->getDailyData($client_id, $session_date);

        // Pass data to the view
        $data = [
            'mandsData' => $mandsData,
        ];

        return view('admin/mandsprogram/dataSheet/daily_data', $data);
    }
    /************************************************************************* */
}
