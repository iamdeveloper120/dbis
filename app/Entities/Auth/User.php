<?php

namespace App\Entities\Auth;


use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    /**
     * Returns the full name of the user.
     */
    public function name(): string
    {
        return trim(implode(' ', [$this->first_name, $this->last_name]));
    }
    /**
     * Returns the user main group name.
     */
    public function mainGroup(): string
    {
        $config = setting('AuthGroups.groups');
        $groups = $this->getGroups();

        $out = [];

        foreach ($groups as $group) {
            $out[] = $config[$group]['title'];
            break;
        }

        return implode(', ', $out);
    }
    /**
     * Returns a list of the groups the user is involved in.
     */
    public function groupsList(): string
    {
        $config = setting('AuthGroups.groups');
        $groups = $this->getGroups();

        $out = [];

        foreach ($groups as $group) {
            $out[] = $config[$group]['title'];
        }

        return implode(', ', $out);
    }

    public function is_user_client(): string
    {
        //$config = setting('AuthGroups.groups');
        $groups = $this->getGroups();

        $out = [];

        foreach ($groups as $group) {
            $out[] = $group;
        }

        if (sizeof($out) == 1 && $out[0] == 'user') {
            return true;
        } else {
            return false;
        }
    }
}
