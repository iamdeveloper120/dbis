<?php

namespace App\Controllers\UserConfiguration;

use App\Controllers\AdminController;

use App\Entities\Auth\User;
use App\Models\Auth\UserModel;

use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use ReflectionException;


class UserController extends AdminController
{
    public function active_user_list()
    {

        $userModel = model(UserModel::class);

        return  view('UserConfiguration/Users/list', ['users' => $userModel->get_all_users(), 'page_title' => 'User Management']);
    }
    public function inactive_user_list()
    {

        $userModel = model(UserModel::class);

        return  view('UserConfiguration/Users/inactive_list', ['users' => $userModel->get_inactive_users(), 'page_title' => 'User Management']);
    }
    /**
     * Display the "new user" form.
     */
    public function create()
    {

        $groups = setting('AuthGroups.groups');
        unset($groups['superadmin']); 
        /*if (!auth()->user()->inGroup('superadmin')) {
            unset($groups['superadmin']);
            unset($groups['admin']);
        }*/
        asort($groups);
        //$user = new User();

        return view('UserConfiguration/Users/form', ['groups' => $groups, 'page_title' => 'New User']);
    }

    /**
     * Creates or saves the basic user details.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     *
     * @throws ReflectionException
     */
    public function save(?int $userId = null)
    {

        $users = new UserModel();
        /**
         * @var User
         */
        $user = $userId !== null
            ? $users->find($userId)
            : new User();

        /** @phpstan-ignore-next-line */
        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'resourceNotFound');
        }

        /**
         * Perform validation here so we can merge the
         * basic model validation rules with the meta info rules.
         *
         * @var array
         */
        $rules = [
            'id' => [
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email|unique_email[{id}]',
                'errors' => [
                    'unique_email' => 'This email is already in use. Could belong to a current or a deleted user.',
                ],
            ],
            'username' => [
                'label' => 'Username', 'rules' => 'required|string|is_unique[users.username,id,{id}]',
            ],
            'first_name' => [
                'label' => 'First Name', 'rules' => 'permit_empty|string|min_length[3]',
            ],
            'last_name' => [
                'label' => 'Last Name', 'rules' => 'permit_empty|string|min_length[3]',
            ],
        ];


        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Fill in basic details
        $user->fill($this->request->getPost());



        // Save basic details
        $users->save($user);

        // We need an ID to on the entity to save groups.
        if ($user->id === null) {
            $user->id = $users->getInsertID();
        }


        // Check for an avatar to upload
        /*if ($file = $this->request->getFile('avatar')) {
            if ($file->isValid()) {
                // Check if the avatar is to be resized
                $avatarResize     = setting('Users.avatarResize') ?? false;
                $maxDimension     = setting('Users.avatarSize') ?? 140;
                [$width, $height] = getimagesize($file->getPathname());
                if ($avatarResize && ($width > (int) $maxDimension || $height > (int) $maxDimension)) {
                    $image = service('image')->withFile($file->getPathname());
                    $image->resize($maxDimension, $maxDimension, true);
                    $image->save();
                }

                $avatarDir = ROOTPATH . (setting('Users.avatarDirectory') ?? 'public/uploads/avatars');
                helper('text');
                $randomString = random_string('alnum', 5);
                $filename     = $user->id . '_' . $randomString . '.jpg';

                // Create if uploads/avatar directories not exist
                if (!is_dir($avatarDir)) {
                    mkdir($avatarDir, 0755, true);
                }

                // delete the previous file if there is one in db & filesystem
                if ($user->avatar && file_exists($avatarDir . '/' . $user->avatar)) {
                    @unlink($avatarDir . '/' . $user->avatar);
                }

                // move the uploaded file and update user object
                if ($file->move($avatarDir, $filename, true)) {
                    $users->update($user->id, ['avatar' => $filename]);
                }
            }
        }*/

        // Save the new user's email/password
        $password = $this->request->getPost('password');
        $identity = $user->getEmailIdentity();
        if ($identity === null) {
            helper('text');
            $user->createEmailIdentity([
                'email'    => $this->request->getPost('email'),
                'password' => !empty($password) ? $password : generate_random_string(12),
            ]);
        }
        // Update existing user's email identity
        else {
            $identity->secret = $this->request->getPost('email');
            if ($password !== null) {
                $identity->secret2 = service('passwords')->hash($password);
            }
            if ($identity->hasChanged()) {
                model(UserIdentityModel::class)->save($identity);
            }
        }

        // Save the user's groups
        $groups = $this->request->getPost('groups') ?? ['user'];
        $user->syncGroups(...$groups);


        return redirect()->to('/user-configuration/users/edit/' . $user->id)->with('message', 'Updated successfully');
    }
    /**
     * Display the Edit form for a single user.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function edit(int $userId)
    {

        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);
        if (!auth()->user()->inGroup('superadmin')) {
            if ($user->inGroup('superadmin')) {
                $user = null;
            }
        }

        if ($user === null) {
            return redirect()->route('/');
        }

        $groups = setting('AuthGroups.groups');
        if (!auth()->user()->inGroup('superadmin')) {
            unset($groups['superadmin']);
        }
        asort($groups);

        return  view('UserConfiguration/Users/form', ['user'   => $user, 'groups' => $groups, 'page_title' => 'User Detail']);
    }

    public function delete()
    {
        // Need to perform check if user is not used in any database table then allow to delte permanently otherwise not

        $data = [
            'id' => $this->request->getPost('id'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'User',
                'rules'  => 'required|integer',
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
                'message' => $this->validator->listErrors('common_list'),
                'data' => ''
            ];
        } else {

            $userModel = model(UserModel::class);
            // Find the user by ID
            $user = $userModel->withDeleted()->find($data['id']);
            // Check if the user exists
            if (!$user) {
                $response = [
                    'status' => 'error',
                    'statusText' => 'Error',
                    'message' => 'User not exist',
                    'data' => ''
                ];
            } else {

                $isExists = $userModel->is_inuse($data['id']);

                if ($isExists) {
                    $response = [
                        'status' => 'error',
                        'statusText' => 'Error',
                        'message' => 'User exists in the client clinical data. First delete entries from daily, weekly, phase line and target dates data belong to user.',
                        'data' => ''
                    ];
                } else {
                    $result = $userModel->permanently_delete($data['id']);
                    if ($result) {
                        $response = [
                            'status' => 'success',
                            'statusText' => '',
                            'message' => 'User Deleted successfully',
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
            }
        }

        return $this->response->setJSON($response);
    }
    public function activation()
    {
        $data = [
            'id' => $this->request->getPost('id'),
            'type' => $this->request->getPost('type'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'User',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'type' => [
                'label'  => 'Type',
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
                'message' => $this->validator->listErrors('common_list'),
                'data' => ''
            ];
        } else {

            $userModel = model(UserModel::class);
            // Find the user by ID
            $user = null;
            if ($data['type'] == 'deactivate') {
                $user = $userModel->find($data['id']);
            } else {
                $user = $userModel->withDeleted()->find($data['id']);
            }

            // Check if the user exists
            if (!$user) {
                $response = [
                    'status' => 'error',
                    'statusText' => 'Error',
                    'message' => 'User not exist',
                    'data' => ''
                ];
            } else {
                // check if active then deactivate and vice versa. 
                $result = false;
                if ($data['type'] == 'deactivate') {
                    $result = $userModel->delete($data['id']);
                    $userModel->user_deactivation_actions($data['id']);
                } else {
                    $result =  $userModel->update($data['id'], ['deleted_at' => null]);
                }


                if ($result) {
                    $response = [
                        'status' => 'success',
                        'statusText' => '',
                        'message' => 'User status updated successfully',
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
        }

        return $this->response->setJSON($response);
    }
    /**
     * Displays basic security info, like previous login info,
     * and ability to force a password reset, ban, etc.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function security(int $userId)
    {

        $users = model(UserModel::class);
        /** @var User|null $user */
        $user = $users->find($userId);
        if (!auth()->user()->inGroup('superadmin')) {
            if ($user->inGroup('superadmin')) {
                $user = null;
            }
        }
        if ($user === null) {
            return redirect()->route('/');
        }



        /** @var LoginModel $loginModel */
        $loginModel = model(LoginModel::class);
        $logins     = $loginModel->where('identifier', $user->email)->orderBy('date', 'desc')->limit(20)->findAll();

        return  view('UserConfiguration/Users/security', ['user'   => $user, 'logins' => $logins, 'page_title' => 'Password Change | ']);
    }

    public function LoggedInLogs()
    {
        /** @var LoginModel $loginModel */
        $loginModel = model(LoginModel::class);
        $allLogins     = $loginModel->orderBy('date', 'desc')->findAll();

        return  view('UserConfiguration/Users/logs', ['allLogins' => $allLogins, 'page_title' => 'Logs']);
    }

    public function changePassword(?int $userId = null)
    {

        $users = new UserModel();
        /**
         * @var User
         */
        $user = $userId !== null
            ? $users->find($userId)
            : new User();

        /** @phpstan-ignore-next-line */
        if ($user !== null) {
            if (!auth()->user()->inGroup('superadmin')) {
                if ($user->inGroup('superadmin')) {
                    $user = null;
                }
            }
        }

        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'resourceNotFound');
        }

        if (!$this->validate(['password' => 'required|strong_password', 'pass_confirm' => 'required|matches[password]'])) {
            return redirect()->back()->withInput()->with('errors', service('validation')->getErrors());
        }

        // Save the new user's email/password
        $password = $this->request->getPost('password');
        $identity = $user->getEmailIdentity();

        if ($password !== null) {
            $identity->secret2 = service('passwords')->hash($password);
        }

        if ($identity->hasChanged()) {
            model(UserIdentityModel::class)->save($identity);
        }

        return redirect()->to('/user-configuration/users/edit/' . $user->id . '/security')->with('message', 'Updated successfully');
    }

    /**
     * Displays basic security info, like previous login info,
     * and ability to force a password reset, ban, etc.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function permissions(int $userId)
    {


        $users = model(UserModel::class);
        $user  = $users->find($userId);
        if ($user === null) {
            return redirect()->back()->with('error', 'resourceNotFound');
        }

        $permissions = setting('AuthGroups.permissions');
        if (is_array($permissions)) {
            ksort($permissions);
        }

        return  view('UserConfiguration/Users/permissions', ['user'   => $user, 'permissions' => $permissions, 'page_title' => 'User Permissions']);
    }

    /**
     * Updates the permissions for a single user.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function savePermissions(int $userId)
    {

        $users = model(UserModel::class);
        /** @var User|null $user */
        $user = $users->find($userId);
        if ($user === null) {
            return redirect()->back()->with('error', 'resourceNotFound');
        }
        $user->syncPermissions(...($this->request->getPost('permissions') ?? []));

        return redirect()->back()->with('message', 'Updated successfully');
    }
}
