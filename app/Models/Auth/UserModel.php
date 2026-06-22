<?php

namespace App\Models\Auth;

use \App\Entities\Auth\User;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;


/**
 * This User model is ready for your customization.
 * It extends Shield's UserModel, providing many auth
 * features built right in.
 */
class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'first_name', // Added
            'last_name',  // Added
            'avatar',  // Added
        ];
    }
    protected $returnType    = User::class;
    protected $allowedFields = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'deleted_at',
        'avatar',
        'first_name',
        'last_name',
    ];

    /***** Returns group or group list as string */
    public function groupsList($groups): string
    {
        $config = setting('AuthGroups.groups');
        $out = [];
        foreach ($groups as $group) {
            $out[] = $config[$group]['title'];
        }
        return implode(', ', $out);
    }

    /***** Active user list where deleted_at is null */
    public function get_all_users()
    {
        $user_list = [];

        $users = [];
        if (auth()->user()->inGroup('superadmin')) {
            $users = $this->findAll();
        } else {
            //$users = $this->select('users.*')->join('auth_groups_users agu', 'agu.user_id = users.id')->whereNotIn('agu.group', ['superadmin'])->findAll();
            $query = $this->db->table('users')
                ->select('users.*')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('users.deleted_at IS NULL')
                ->whereNotIn('auth_groups_users.group', ['superadmin'])

                ->groupBy('users.id')
                ->get();

            $users = $query->getResult(User::class);
        }

        if (count($users) > 0) {
            foreach ($users as $user) {
                $user->groupList = $this->groupsList($user->getGroups());
                $user_list[] = $user;
            }
        }

        return $user_list;
    }
    /***** Inactive user list where deleted_at is not null */
    public function get_inactive_users()
    {
        $user_list = [];

        $users = $this->withDeleted()->findAll();
        /*$query = "SELECT * FROM users WHERE deleted_at IS NOT NULL";
        $users = $this->db->query($query)->getResult(User::class);*/

        if (count($users) > 0) {
            foreach ($users as $user) {
                if ($user->deleted_at !== null) {
                    $user->groupList = $this->groupsList($user->getGroups());
                    $user_list[] = $user;
                }
            }
        }

        return $user_list;
    }

    /* Perform actions when user deactivated */
    public function user_deactivation_actions($user_id)
    {
        $builder = $this->db->table('client_user_mapping');
        return $builder->delete(['user_id' => $user_id]);
    }
    /* Perform actions when user deleted */
    public function user_delete_actions($user_id) {}
    /****** Delete user permanently */
    public function permanently_delete($user_id)
    {
        $this->db->transStart();

        // Execute the delete queries
        $builder = $this->db->table('users');
        $builder->where('id', $user_id)->delete();

        $builder = $this->db->table('auth_groups_users');
        $builder->where('user_id', $user_id)->delete();

        $builder = $this->db->table('auth_identities');
        $builder->where('user_id', $user_id)->delete();

        $builder = $this->db->table('auth_logins');
        $builder->where('user_id', $user_id)->delete();

        $builder = $this->db->table('auth_permissions_users');
        $builder->where('user_id', $user_id)->delete();

        $builder = $this->db->table('auth_remember_tokens');
        $builder->where('user_id', $user_id)->delete();

        $builder = $this->db->table('auth_token_logins');
        $builder->where('user_id', $user_id)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        } else {
            return true;
        }
    }

    /* check if user is used in other table */
    public function is_inuse($userId)
    {
        $builder = $this->db->table('client_user_mapping');
        $builder->select('*');
        $builder->where('user_id', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }



        $builder = $this->db->table('daily_sessions');
        $builder->select('*');
        $builder->where('instructor_id', $userId);
        $builder->orWhere('supervisor_id', $userId);
        $builder->orWhere('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }


        $builder = $this->db->table('clients');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->orWhere('deleted_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('program_master_domains');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('program_master_goals');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('program_master_targets');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_program_domains');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_program_goals');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_program_targets');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('daily_session_data_processed');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('daily_session_data_collection');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('mands_session_data');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('mands_reinforcer');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_probe_set');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->orWhere('updated_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_program_change');
        $builder->select('*');
        $builder->where('created_by', $userId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }


        return false;
    }

    /*********************** */

    public function getUsersByGroups($groupName)
    {
        $user_list = [];
        $users = $this->select('users.*')
            ->join('auth_groups_users agu', 'agu.user_id = users.id')
            ->where('users.deleted_at is NULL')
            ->whereIn('agu.group', $groupName)
            ->orderBy('agu.group', 'ASC')
            ->findAll();

        if (count($users) > 0) {
            foreach ($users as $user) {
                $user->groupList = $this->groupsList($user->getGroups());
                $user_list[] = $user;
            }
        }

        return $user_list;
    }

    public function getClientDefaultSupervisor($clientId)
    {
        return $this->db->table('auth_groups_users')
            ->select('users.*')
            ->join('users', 'users.id = auth_groups_users.user_id and users.deleted_at is NULL')
            ->join('client_user_mapping', 'client_user_mapping.user_id = users.id and client_user_mapping.is_default=1 and client_user_mapping.client_id = ' . $clientId)
            ->where('auth_groups_users.group', 'supervisor')
            ->get()
            ->getRow();
    }
    public function getClientInstructors(int $clientId): array
    {
        return $this->db->table('client_user_mapping m')
            ->select('u.id, u.first_name, u.last_name')
            ->join('users u', 'u.id = m.user_id AND u.deleted_at IS NULL')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('m.client_id', $clientId)
            ->where('agu.group', 'instructor')
            ->get()
            ->getResult();
    }
    public function isClientBelongsToInstructor($clientId, $user_id)
    {
        $result =  $this->db->table('auth_groups_users')
            ->select('users.*')
            ->join('users', 'users.id = auth_groups_users.user_id and users.deleted_at is NULL')
            ->join('client_user_mapping', 'client_user_mapping.user_id = users.id and client_user_mapping.client_id = ' . $clientId)
            ->where('users.id', $user_id)
            ->where('auth_groups_users.group', 'instructor')
            ->get()
            ->getRow();

        return $result ? true : false; // Explicitly return true or false
    }
    public function isClientBelongsToSupervisor($clientId, $user_id)
    {
        $result =  $this->db->table('auth_groups_users')
            ->select('users.*')
            ->join('users', 'users.id = auth_groups_users.user_id and users.deleted_at is NULL')
            ->join('client_user_mapping', 'client_user_mapping.user_id = users.id and client_user_mapping.is_default=1 and client_user_mapping.client_id = ' . $clientId)
            ->where('users.id', $user_id)
            ->where('auth_groups_users.group', 'supervisor')
            ->get()
            ->getRow();

        return $result ? true : false; // Explicitly return true or false
    }
    public function isInstructor($user_id)
    {
        $result = $this->db->table('auth_groups_users')
            ->select('users.*')
            ->join('users', 'users.id = auth_groups_users.user_id AND users.deleted_at IS NULL')
            ->where('auth_groups_users.group', 'instructor')
            ->where('users.id', $user_id)
            ->get()
            ->getRow();

        return $result ? true : false; // Explicitly return true or false
    }
    /**********************  */
}
