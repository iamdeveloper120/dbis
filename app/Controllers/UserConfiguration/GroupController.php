<?php

/**
 * This file is part of Bonfire.
 *
 * (c) Lonnie Ezell <lonnieje@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Controllers\UserConfiguration;


use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authorization\Groups;
use App\Controllers\AdminController;

class GroupController extends AdminController
{
    /**
     * Displays a list of all Roles in the system.
     */
    public function index()
    {
        $groups = setting('AuthGroups.groups');
        asort($groups);

        // Find the number of users in this group
        foreach ($groups as $alias => &$group) {
            $group['user_count'] = db_connect()
                ->table('auth_groups_users')
                ->where('group', $alias)
                ->countAllResults(true);
        }

        return  view('UserConfiguration/Groups/index', ['groups' => $groups, 'page_title' => 'User Groups']);
    }

    /**
     * Allows the user to choose the permissions for a group.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function show(string $alias)
    {

        $group = setting('AuthGroups.groups')[$alias];

        if (empty($group)) {
            //need to check alias exist in group if not then error message
        }

        return view('UserConfiguration/Groups/form', ['group' => $group, 'groupAlias' => $alias, 'page_title' => 'User Group Detail']);
    }

    /**
     * Save the group settings
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function save(string $alias)
    {
        $group = setting('AuthGroups.groups')[$alias];

        if (empty($group)) {
            return redirect()->back()->with('error', 'resourceNotFound');
        }

        // Validate
        $rules = [
            'title'       => 'required|string',
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Save the settings
        $groupConfig         = setting('AuthGroups.groups');
        $groupConfig[$alias] = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
        ];

        setting('AuthGroups.groups', $groupConfig);

        return redirect()->back()->with('message', 'Updated successfully');
    }
    /**
     * Displays a list of all Permissions for a single group
     *
     * @return RedirectResponse|string
     */
    public function permissions(string $groupName)
    {

        $groups = new Groups();
        $group  = $groups->info($groupName);
        if ($group === null) {
            //need to check group exist in group if not then error message
        }

        $permissions = setting('AuthGroups.permissions');
        /*if (is_array($permissions)) {
            ksort($permissions);
        }*/

        return  view('UserConfiguration/Groups/permissions', ['group' => $group, 'permissions' => $permissions, 'page_title' => 'Group Permissions']);
    }

    /**
     * Updates the permissions for a single group.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function savePermissions(string $group)
    {

        $groups = new Groups();
        $group  = $groups->info($group);
        if ($group === null) {
            return redirect()->back()->with('error', 'resourceNotFound');
        }

        $permissions = $this->request->getPost('permissions');
        $group->setPermissions($permissions ?? []);

        return redirect()->back()->with('message', 'Updated successfully');
    }
    
    /**
     * Updates the permissions for a single group.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
}
