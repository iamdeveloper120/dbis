<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientGraphs\PhaseLineModel;
use App\Entities\ClientGraphs\PhaseLine;

class PhaseLineController extends AdminController
{
    use ResponseTrait;
    protected $model;
    protected $clientModel;
    public function __construct()
    {
        $this->model = new PhaseLineModel();
    }
    /*************************************************************************** */
    public function list()
    {
        $graph_type = $this->request->getPost('graph_type');
        $client_id = $this->request->getPost('client_id');

        $data = $this->model->list($client_id, $graph_type);

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

        $graph_type = $this->request->getPost('graph_type');
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
            'graph_type' => [
                'label'  => 'Graph Type',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];
        $data = [
            'id'   => $id,
            'graph_type'   => $graph_type

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
            $rowData->p_date = stringToDate($rowData->p_date, CC_DATE_FORMAT);
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
            'p_date' => $this->request->getPost('p_date'),
            'client_id' => $this->request->getPost('client_id'),
            'graph_type' => $this->request->getPost('graph_type'),
            'p_key' => $this->request->getPost('p_key')
        ];
        $response = [];

        $rules =    [
            'p_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_phase_line_date_exist[p_date]',
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
            'p_key' => [
                'label'  => 'Phase Line Key',
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
            $p_date = stringToDate($this->request->getPost('p_date'), "Y-m-d");
            $data['p_date'] = $p_date;
            $data['created_by'] = auth()->user()->id;

            $entity = new PhaseLine();
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
            'p_date' => $this->request->getPost('p_date'),
            'client_id' => $this->request->getPost('client_id'),
            'graph_type' => $this->request->getPost('graph_type'),
            'p_key' => $this->request->getPost('p_key')

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
            'p_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_phase_line_date_exist[p_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                ],
            ],
            'p_key' => [
                'label'  => 'Phase Line Key',
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
            $p_date = stringToDate($this->request->getPost('p_date'), "Y-m-d");
            $data['p_date'] = $p_date;
            $data['updated_by'] = auth()->user()->id;

            $entity = new PhaseLine();
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
