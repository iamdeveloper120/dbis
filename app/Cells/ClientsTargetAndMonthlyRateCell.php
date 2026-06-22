<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;
use App\Models\KPI\KPITargetModel;

class ClientsTargetAndMonthlyRateCell extends Cell
{
    public function render(): string
    {
        $KPITargetModel = new KPITargetModel();

        $clientData  = $KPITargetModel->get_clients_target_and_rate_table_data();


        return view('cells/kpi/target/clients_target_and_rate_table', ['clients' => $clientData['clients'], 'months' => $clientData['months']]);
    }
}
