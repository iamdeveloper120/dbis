<?php

namespace App\Controllers\ClientConfiguration;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\Auth\UserModel;

use App\Models\ClientConfiguration\ClientPermissionModel;
use App\Entities\ClientConfiguration\ClientPermission;


class ClientPermissionController extends AdminController
{

    public function index()
    {
        $model = model(UserModel::class);
        $supervisors = $model->getUsersByGroups(['supervisor']);
        $instructors = $model->getUsersByGroups(['instructor', 'externalInstructor']);

        $this->page_title = 'Client Access';
        return  view('ClientConfiguration/Permissions/index', ['supervisors' => $supervisors, 'instructors' => $instructors,  'page_title' => $this->page_title]);
    }
    /******************************************************************** */
    public function list()
    {
        $clientModel = model(ClientModel::class);
        $clients = $clientModel->where('status', 1)->findAll();
        $u_id = $this->request->getPost('user_id');
        $type = $this->request->getPost('type');

        $client_list = [];
        if ($u_id !== "") {

            $user_clients = $clientModel->get_user_clients($u_id);

            if (isset($clients) && count($clients)) {
                foreach ($clients as $data) {
                    $isAssigned = $this->getObjectByClientId($user_clients, $data->id);
                    $checked = '';
                    $default = '';
                    if ($isAssigned != null) {
                        $checked = 'checked="checked"';
                        $default = $isAssigned->is_default ? 'checked="checked"' : '';
                    }
                    $client = [];
                    $client[] = $data->internal_mrn;
                    $client[] = $data->name();
                    /* if ($data->status == 1) {
                        $client[] = '<span class="badge text-bg-success">Active</span>';
                    } else {
                        $client[] = '<span class="badge text-bg-danger">In-active</span>';
                    }*/


                    if ($type == 'supervisor') {
                        $client[] = '<div class="form-check form-switch form-switch-md form-switch-info"><input u_id= ' . $u_id . ' client_id = ' . $data->id . ' class="form-check-input default-permission" type="checkbox" id="is-default-' . $data->id . '" name="is_defaults[]"  ' . $default . '></div>';
                        $client[] = '<div class="form-check form-switch form-switch-md form-switch-primary"><input u_id= ' . $u_id . ' client_id = ' . $data->id . ' class="form-check-input client-permission" type="checkbox" id="s_customCheck-' . $data->id . '" name="s_clients_permissions[]"  ' . $checked . '></div>';
                    } else {
                        $client[] = '<div class="form-check form-switch form-switch-md form-switch-primary"><input u_id= ' . $u_id . ' client_id = ' . $data->id . ' class="form-check-input client-permission" type="checkbox" id="customCheck-' . $data->id . '" name="clients_permissions[]"  ' . $checked . '></div>';
                    }

                    $client_list[] = $client;
                }
            }
        }

        return $this->response->setJSON($client_list);
    }
    private function getObjectByClientId($array, $client_id)
    {
        foreach ($array as $item) {
            if ($item->client_id == $client_id) {
                return $item;
            }
        }

        // If no match is found, return null or handle accordingly
        return null;
    }

    /******************************************************************** */
    public function save()
    {
        $response = [];
        $clientModel = new ClientModel();

        $status = false;
        if ($this->request->getPost('permission') == 0) {
            $clientPermissionModel = new ClientPermissionModel();
            $p = $clientPermissionModel->where(['user_id' => $this->request->getPost('user_id'), 'client_id' => $this->request->getPost('client_id'), 'is_default' => 1])->first();
            if (isset($p)) {
                $response = [
                    'status' => 'error',
                    'statusText' => '',
                    'message' => 'First, Exempted client from direct supervision then unassign.',
                    'data' => ''
                ];
                return $this->response->setJSON($response);
            }

            $status = $clientModel->detach_client_from_user($this->request->getPost('user_id'), $this->request->getPost('client_id'));
        } else {
            $status = $clientModel->attach_client_to_user($this->request->getPost('user_id'), $this->request->getPost('client_id'));
        }

        $response = [];
        if ($status == false) {

            $response = [
                'status' => 'error',
                'statusText' => '',
                'message' => 'Something went wrong. Contact Programmer',
                'data' => ''
            ];
        } else {
            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Updated successfully',
                'data' => ''
            ];
        }


        return $this->response->setJSON($response);
    }
    public function update_default_supervisor()
    {
        $user_id = $this->request->getPost('user_id');
        $client_id = $this->request->getPost('client_id');
        $is_default = $this->request->getPost('is_default');
        $response = [];
        $clientPermissionModel = new ClientPermissionModel();
        $p1 = $clientPermissionModel->where(['user_id' => $user_id, 'client_id' => $client_id])->first();

        if (!isset($p1)) {
            $response = [
                'status' => 'error',
                'statusText' => '',
                'message' => 'First, allocate a client. Following that, you can appoint a supervisor for direct oversight',
                'data' => ''
            ];
            return $this->response->setJSON($response);
        }

        $p2 = $clientPermissionModel->where(['client_id' => $client_id, 'is_default' => 1])->first();
        if (isset($p2)) {
            $data = [
                'id' => $p2->id,
                'is_default' => 0,
            ];
            $cp = new ClientPermission();
            $cp->fill($data);
            $clientPermissionModel->save($cp);
        }

        $status = false;
        if ($is_default == 0) {
            $data = [
                'id' => $p1->id,
                'is_default' => 0,
            ];

            $cp = new ClientPermission();
            $cp->fill($data);
            $status = $clientPermissionModel->save($cp);
        } else {
            $data = [
                'id' => $p1->id,
                'is_default' => 1,
            ];

            $cp = new ClientPermission();
            $cp->fill($data);
            $status = $clientPermissionModel->save($cp);
        }

        $response = [];
        if ($status == false) {
            $response = [
                'status' => 'error',
                'statusText' => '',
                'message' => 'Something went wrong. Contact Programmer',
                'data' => ''
            ];
        } else {
            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => $is_default == 0 ? 'The client has been exempted from direct monitoring.' : 'The client has been delegated for direct monitoring.',
                'data' => ''
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
}
