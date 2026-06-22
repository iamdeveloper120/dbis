<?php

namespace App\Controllers\KPI;

use App\Controllers\AdminController;
use App\Models\KPI\KPITargetModel;


class KPIController extends AdminController
{

    public function rate_data()
    {
        helper('setting');
        $this->page_title = 'KPIs | Rate Data';
        $template = 'KPI/rate_data';

        $data['page_title'] = $this->page_title;
        return view($template, $data);
    }
    public function client_target()
    {
        helper('setting');
        $this->page_title = 'KPIs | Clients Target';
        $template = 'KPI/client_target';

        $data['page_title'] = $this->page_title;
        return view($template, $data);
    }
    public function supervisor_target()
    {
        helper('setting');
        $this->page_title = 'KPIs | Supervisor Target';
        $template = 'KPI/supervisor_target';

        $data['page_title'] = $this->page_title;
        return view($template, $data);
    }
    public function client_target_data()
    {
        $client_id = $this->request->getPost('client_id');
        $internal_mrn = $this->request->getPost('internal_mrn');
        $kPITargetModel = model(KPITargetModel::class);
        $data = $kPITargetModel->get_selected_client_month_vise_target($client_id);

        // Generate the table data
        $table = new \CodeIgniter\View\Table();

        $table = new \CodeIgniter\View\Table();
        $template = [
            'table_open' => '<table class="table table-striped table-bordered table-hover">',
        ];
        $table->setTemplate($template);
        $table->setHeading('Month', 'Skills Montly Rate', 'DOI Montly Rate', 'Met Target(Yes/No)');
        $skills_target = '';
        $doi_target = '';


        foreach ($data as $row) {


            if ($row['target_status'] == '') {
                $target_status = '';
            } else if ($row['target_status'] == 1) {
                $target_status = '<i class="ri-checkbox-circle-line fs-17 text-success" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>';
            } else if ($row['target_status'] == 0) {
                $target_status = '<i class="ri-close-circle-line fs-17 text-danger" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>';
            }
            if ($row['skills_target'] != null && $row['skills_target'] != '') {
                $skills_target = $row['skills_target'];
            }
            if ($row['doi_target'] != null && $row['doi_target'] != '') {
                $doi_target = $row['skills_target'];
            }

            $table->addRow([
                date('M-Y', strtotime($row['months'])),
                //$row['skills_target'],
                $row['skill_rate'],
                //$row['doi_target'],
                $row['doi_rate'],
                $target_status
            ]);
        }

        $tableHTML = $table->generate();
        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'message' => 'List',
            'skills_target' => $skills_target,
            'doi_target' => $doi_target,
            'data' => $tableHTML
        ];
        return $this->response->setJSON($response);
    }
    public function client_target_data_by_month()
    {
        $month = '01-' . $this->request->getPost('month');
        $month = new \DateTime($month);
        $month = $month->format("Y-m");

        $kPITargetModel = model(KPITargetModel::class);
        $data = $kPITargetModel->get_client_month_vise_target_by_month($month);


        // Generate the table data
        $table = new \CodeIgniter\View\Table();

        $table = new \CodeIgniter\View\Table();
        $template = [
            'table_open' => '<table class="table table-striped table-bordered table-hover">',
        ];
        $table->setTemplate($template);
        $table->setHeading('Clients', 'Skills Target', 'Skills Monthly Rate', 'DOI Target', 'DOI Monthly Rate', 'Met Target(Yes/No)');



        foreach ($data as $row) {


            if ($row['target_status'] == '') {
                $target_status = '';
            } else if ($row['target_status'] == '1') {
                $target_status = '<i class="ri-checkbox-circle-line fs-17 text-success" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>';
            } else if ($row['target_status'] == '0') {
                $target_status = '<i class="ri-close-circle-line fs-17 text-danger" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>';
            }


            $table->addRow([
                $row['internal_mrn'],
                $row['target_skills'],
                $row['skill_rate'],
                $row['target_doi'],
                $row['doi_rate'],
                $target_status,
            ]);
        }

        $tableHTML = $table->generate();
        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'data' => $tableHTML
        ];
        return $this->response->setJSON($response);
    }
    
}
