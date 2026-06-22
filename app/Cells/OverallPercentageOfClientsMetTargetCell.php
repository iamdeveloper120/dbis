<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;
use App\Models\KPI\KPITargetModel;

class OverallPercentageOfClientsMetTargetCell extends Cell
{
    public function render(): string
    {
        $KPITargetModel = new KPITargetModel();
        $chartData =$KPITargetModel->get_overall_percentage_of_clients_met_target();
        return view('cells/kpi/target/overall_percentage_of_clients_met_target', ['chartLabels' => $chartData['internal_mrns'], 'chartPercentages' => $chartData['percentages'], 'ids' => $chartData['clients']]);
    }
}
