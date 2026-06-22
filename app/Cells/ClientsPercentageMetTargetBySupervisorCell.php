<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;
use App\Models\KPI\KPITargetModel;

class ClientsPercentageMetTargetBySupervisorCell extends Cell
{
    public function render(): string
    {
        $KPITargetModel = new KPITargetModel();
        $data = $KPITargetModel->get_clients_percentage_met_target_by_supervisor();
        $min_month = $data['min_month'];
        foreach ($data['clientData'] as $clientData) {
            $supervisorId = $clientData['supervisorId'];
            $supervisorName = $clientData['supervisorName'];
            $months = $clientData['months'];
            if ($months != null)
                sort($months);
            $clientsByMonth = $clientData['clientsByMonth'];
            // Generate the table data
            $dataTableForGraphCalculations = new \CodeIgniter\View\Table();
            $graphData = new \CodeIgniter\View\Table();

            $template = [
                'table_open' => '<table class="table table-striped table-bordered table-hover" width="100%">',
                'thead_open' => '<thead style="width: 100%;">',
            ];
            $graphData->setTemplate($template);
            $graphData->setHeading('Month', '# Clients Met Target', '# Clients Not Met Target', 'Percentage');

            $dataTableForGraphCalculations->setTemplate($template);
            $dataTableForGraphCalculations->setHeading('Months', 'Clients', 'Met Target');

            // Prepare the bar chart data
            $chartLabels = [];
            $chartData = [];

            foreach ($months as $month) {
                if ($min_month && $month > $min_month) {
                    $chartLabels[] = date('M-Y', strtotime($month));
                    $clients = $clientsByMonth[$month];
                    $metTargetCount = 0;
                    $notMetTargetCount = 0;

                    foreach ($clients as $clientId => $client) {
                        $status_text = '';
                        $targetStatus = isset($client['target_status']) ? $client['target_status'] : null;
                        if ($targetStatus == '' || $targetStatus == null) {
                            $status_text = '';
                        } else if ($targetStatus == 1) {
                            $status_text = '<span class="d-none">1</span><i class="ri-checkbox-circle-line fs-17 text-success" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>';
                            $metTargetCount++;
                        } elseif ($targetStatus == 0) {
                            $status_text = '<span class="d-none">0</span><i class="ri-close-circle-line fs-17 text-danger" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>';
                            $notMetTargetCount++;
                        }
                        $dataTableForGraphCalculations->addRow([
                            date('M-Y', strtotime($month)),
                            $client['internal_mrn'],
                            $status_text
                        ]);
                    }

                    $percentage = ($metTargetCount + $notMetTargetCount) > 0 ? round(($metTargetCount / ($metTargetCount + $notMetTargetCount)) * 100, 2) : 0;
                    $chartData[] = $percentage;
                    $graphData->addRow([
                        date('M-Y', strtotime($month)),
                        $metTargetCount,
                        $notMetTargetCount,
                        $percentage . '%'
                    ]);
                }
            }


            $graphTableHTML = $graphData->generate();
            $tableDataHTML = $dataTableForGraphCalculations->generate();

            $chartDataJS = json_encode([
                'labels' => $chartLabels,
                'data' => $chartData,
                'colors' => $this->getRandomColor($supervisorName)
            ]);

            // Pass the table data and chart data to the view for each supervisor
            $data['supervisorData'][] = [
                'supervisorId' => $supervisorId,
                'supervisorName' => $supervisorName,
                'graphData' => $graphTableHTML,
                'tableDataHTML' => $tableDataHTML,
                'chartDataJS' => $chartDataJS
            ];
            usort($data['supervisorData'], function ($a, $b) {
                return strcmp($a['supervisorName'], $b['supervisorName']);
            });
        }

        return view('cells/kpi/target/clients_percentage_met_target_by_supervisor', ['data' => $data]);
    }
    function getRandomColor($input)
    {
        // Seed the random generator based on the input string
        mt_srand(crc32($input));

        // Generate random RGB values
        $red = mt_rand(0, 255);
        $green = mt_rand(0, 255);
        $blue = mt_rand(0, 255);

        // Return the color in rgba format
        return "rgba($red, $green, $blue, 1)";
    }
}
