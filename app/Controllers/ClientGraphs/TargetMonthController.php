<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientGraphs\TargetMonthModel;;

use App\Entities\ClientGraphs\TargetMonth;

class TargetMonthController extends AdminController
{
    use ResponseTrait;
    protected $model;
    protected $clientModel;
    public function __construct()
    {
        $this->model = new TargetMonthModel();
    }
    /*************************************************************************** */
    public function list()
    {

        $client_id = $this->request->getPost('client_id');

        $data = $this->model->list($client_id);

        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'message' => 'List',
            'data' => $data
        ];
        return $this->response->setJSON($response);
    }
    /*************************************************************************** */
    public function get_selected()
    {
        $id   = $this->request->getPost('id');

        $response = [];

        $rules =    [
            'id' => [
                'label'  => 'Record ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];
        $data = [
            'id'   => $id,

        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $rowData = $this->model->find($data['id']);
            $t_date = new \DateTime($rowData->t_date);
            $rowData->t_date = $t_date->format("M-Y");
            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => '',
                'data' => $rowData
            ];
        }

        return $this->response->setJSON($response);
    }
    /*************************************************************************** */
    public function create()
    {
        $data = [
            't_date' => $this->request->getPost('t_date'),
            'client_id' => $this->request->getPost('client_id'),
            'graph_type' => $this->request->getPost('graph_type'),

        ];
        $response = [];

        $rules =    [
            't_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_target_date_exist[t_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                ],
            ],
            'client_id' => [
                'label'  => 'Client ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'graph_type' => [
                'label'  => 'Graph Type',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $t_date = stringToDate($this->request->getPost('t_date'), "Y-m-d");
            $data['t_date'] = $t_date;
            $data['created_by'] = auth()->user()->id;

            $entity = new TargetMonth();
            $entity->fill($data);
            $this->model->save($entity);


            $rowData  = $this->model->single($this->model->getInsertID());

            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record created successfully',
                'data' => $rowData
            ];
        }

        return $this->response->setJSON($response);
    }
    /*************************************************************************** */
    public function update()
    {

        $data = [
            'id' => $this->request->getPost('id'),
            't_date' => $this->request->getPost('t_date'),
            'client_id' => $this->request->getPost('client_id'),
            'graph_type' => $this->request->getPost('graph_type')

        ];
        $rules =    [
            'id' => [
                'label'  => 'Record ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'client_id' => [
                'label'  => 'Record ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            't_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_target_date_exist[t_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                ],
            ],
            'graph_type' => [
                'label'  => 'Graph Type',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $p_date = stringToDate($this->request->getPost('t_date'), "Y-m-d");
            $data['t_date'] = $p_date;
            $data['updated_by'] = auth()->user()->id;

            $entity = new TargetMonth();
            $entity->fill($data);
            $this->model->save($entity);


            $rowData  = $this->model->single($data['id']);

            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record updated successfully',
                'data' => $rowData
            ];
        }

        return $this->response->setJSON($response);
    }
    /*************************************************************************** */
    public function delete()
    {
        $data = [
            'id' => $this->request->getPost('id'), 
        ];

        $rules =    [
            'id' => [
                'label'  => 'Record ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ] 
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {;
            $result = $this->model->delete($data['id']);

            if ($result) {
                $response = [
                    'status' => 'success',
                    'statusText' => '',
                    'message' => 'Record deleted successfully',
                    'data' => ''
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'statusText' => '',
                    'message' => 'Contact system administrator',
                    'data' => ''
                ];
            }
        }

        return $this->response->setJSON($response);
    }
}
