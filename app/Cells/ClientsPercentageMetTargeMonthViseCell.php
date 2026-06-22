<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;
use App\Models\KPI\KPITargetModel;

class ClientsPercentageMetTargeMonthViseCell extends Cell
{
    public function render(): string
    {
        $KPITargetModel = new KPITargetModel();
        $data = $KPITargetModel->get_month_vise_percentage_clients_met_target();
        return view('cells/kpi/target/clients_percentage_met_target_month_vise', ['labels' => $data['months'], 'dataset' => $data['percentages']]);
    }
}
