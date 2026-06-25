<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superAdmin');
     *
     * @var array<string, array<string, string>>
     *
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Administrator',
            'description' => 'Has complete control over the entire site.',
        ],
        'management' => [
            'title'       => 'Management',
            'description' => 'Handles site management operations.',
        ],
        'supervisor' => [
            'title'       => 'Supervisor',
            'description' => 'Oversees supervisor-level operations.',
        ],
        'instructor' => [
            'title'       => 'Instructor',
            'description' => 'Manages instructor-related activities.',
        ],
        'externalinstructor' => [
            'title'       => 'External Instructor',
            'description' => 'Handles external instructor operations.',
        ],
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Performs day-to-day administrative tasks.',
        ],
        'parent' => [
            'title'       => 'Client parent access',
            'description' => 'Client detail view',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Regular user of the site.',
        ]
    ];


    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        // Sessions Menu Access
        'sessions.access' => 'Sessions → Menu Access',

        // Live Sessions
        'sessions.live.run' => 'Sessions → Run Session',

        // Session Review
        'sessions.review' => 'Sessions → Session Review (Review, Update, Process)',
        'sessions.review.modification' => 'Sessions → Session Review Data Modification (Add, Update, Delete, Resolve) - Any Time',

        // Completed Sessions
        'sessions.daily.view' => 'Sessions → Completed Sessions View',
        'sessions.daily.single' => 'Sessions → Completed Session Detail View',
        'sessions.daily.update' => 'Sessions → Completed Sessions Update',
        'sessions.daily.create' => 'Sessions → Completed Sessions Create',
        'sessions.daily.delete' => 'Sessions → Completed Sessions Delete',

        // Data Menu Access
        'data-sheet.access' => 'Data → Menu Access',

        // Data Sheets
        'data-sheet.view' => 'Data → Datasheets View',

        // Program Change
        'sessions.program-change' => 'Sessions → Program Change View & Update',

        // Daily Data Management
        'daily-data.computed-data.view' => 'Data → Daily Data View (Live & Manual)',
        'daily-data.manual.create' => 'Data → Daily Data Manual Create',
        'daily-data.manual.update' => 'Data → Daily Data Manual Update',
        'daily-data.manual.create-no-session' => 'Data → Daily Data Manual Create (No Session)',
        'daily-data.manual.delete' => 'Data → Daily Data Manual Delete',

        // Weekly Data Management
        'weekly-data.manual.view' => 'Data → Weekly Data View',
        'weekly-data.manual.create' => 'Data → Weekly Data Create',
        'weekly-data.manual.update' => 'Data → Weekly Data Update',
        'weekly-data.manual.create-no-session' => 'Data → Weekly Data Create (No Session)',
        'weekly-data.manual.delete' => 'Data → Weekly Data Delete',

        // Client Profile (order matches profile-sidebar.php)
        'client-profile.access' => 'Client Profile → Menu Access',
        'client-profile.dashboard.view' => 'Client Profile → Client Dashboard View',
        'client-profile.dashboard.key-information.view' => 'Client Profile → Dashboard → Key Information View',
        'client-profile.dashboard.session-quality.view' => 'Client Profile → Dashboard → Session Quality Rating View',
        'client-profile.dashboard.cumulative-graph.view' => 'Client Profile → Dashboard → Cumulative Graph View',
        'client-profile.dashboard.active-targets.view' => 'Client Profile → Dashboard → Active Targets View',
        'client-profile.dashboard.mands-graphs.view' => 'Client Profile → Dashboard → Mands Graphs View',
        'client-profile.dashboard.behaviour-reduction.view' => 'Client Profile → Dashboard → Behaviour Reduction Graphs View',
        'client-profile.dashboard.session-overview.view' => 'Client Profile → Dashboard → Session Overview View',
        'client-profile.dashboard.wow-moments.view' => 'Client Profile → Dashboard → Wow Moments View',

        // Client
        'client-profile.client.access' => 'Client Profile → Client → Menu Access',
        'client-profile.client.client-detail.view' => 'Client Profile → Client → Client Details View',
        'client-profile.client.key-information.view' => 'Client Profile → Client → Key Information View',

        // Skill Acquisition Programme
        'client-profile.programs-skills.access' => 'Client Profile → Skill Acquisition Programme → Menu Access',
        'client-profile.programs-skills.active-targets.view' => 'Client Profile → Skill Acquisition Programme → Active Targets View',
        'client-profile.programs-skills.program-history.view' => 'Client Profile → Skill Acquisition Programme → Programme History View',
        'client-profile.programs-skills.program-adjustments.view' => 'Client Profile → Skill Acquisition Programme → Programme Adjustments View',
        'client-profile.programs-skills.mastered-skills.view' => 'Client Profile → Skill Acquisition Programme → Mastered Skills View',
        'client-profile.programs-skills.developing-independence.view' => 'Client Profile → Skill Acquisition Programme → Developing Independence View',
        'client-profile.programs-skills.probe-data.view' => 'Client Profile → Skill Acquisition Programme → Probe Data View',
        'client-profile.graphs.daily.view' => 'Client Profile → Skill Acquisition Programme → Daily Graphs View',
        'client-profile.graphs.stimulus-response-chain.view' => 'Client Profile → Skill Acquisition Programme → Stimulus Response Chain Graphs View',
        'client-profile.graphs.cumulative.view' => 'Client Profile → Skill Acquisition Programme → Cumulative Weekly Graphs View',
        'client-profile.graphs.rate.view' => 'Client Profile → Skill Acquisition Programme → Rate Graphs View',

        // Mands
        'client-profile.mands.access' => 'Client Profile → Mands → Menu Access',
        'client-profile.mands.active-targets.view' => 'Client Profile → Mands → Active Mand Targets View',
        'client-profile.mands.data.view' => 'Client Profile → Mands → Mand Data View',
        'client-profile.mands.dictionary.view' => 'Client Profile → Mands → Mand Dictionary View',
        'client-profile.graphs.mands.view' => 'Client Profile → Mands → Mand Graphs View',
        'client-profile.mands.dictionary.create' => 'Client Profile → Mands → Mand Dictionary Create',
        'client-profile.mands.dictionary.update' => 'Client Profile → Mands → Mand Dictionary Update',
        'client-profile.mands.dictionary.delete' => 'Client Profile → Mands → Mand Dictionary Delete',
        'client-profile.mands.dictionary.media.manage' => 'Client Profile → Mands → Mand Dictionary Media Manage',

        // Behaviour Reduction
        'client-profile.problem-behaviour.access' => 'Client Profile → Behaviour Reduction → Menu Access',
        'client-profile.problem-behaviour.reduction-data.view' => 'Client Profile → Behaviour Reduction → Behaviour Reduction Data View',
        'client-profile.problem-behaviour.abc-template.view' => 'Client Profile → Behaviour Reduction → ABC Template View',
        'client-profile.problem-behaviour.graphs.view' => 'Client Profile → Behaviour Reduction → Behaviour Reduction Graphs View',

        // Summary Data
        'client-profile.summary-data.access' => 'Client Profile → Summary Data → Menu Access',
        'client-profile.summary-data.session-overview.view' => 'Client Profile → Summary Data → Session Overview View',
        'client-profile.summary-data.daily-data.view' => 'Client Profile → Summary Data → Daily Data View',
        'client-profile.summary-data.weekly-data.view' => 'Client Profile → Summary Data → Weekly Data View',

        // Reports
        'client-profile.reports.access' => 'Client Profile → Reports → Menu Access',
        'client-profile.reports.daily.view' => 'Client Profile → Reports → Session Summary View',
        'client-profile.reports.daily.generate' => 'Client Profile → Reports → Daily Report Generate Draft',
        'client-profile.reports.daily.save-draft' => 'Client Profile → Reports → Daily Report Save Draft',
        'client-profile.reports.daily.pull-section' => 'Client Profile → Reports → Daily Report Pull Section',
        'client-profile.reports.daily.finalize' => 'Client Profile → Reports → Daily Report Finalize',
        'client-profile.reports.daily.regenerate' => 'Client Profile → Reports → Daily Report Regenerate',
        'client-profile.reports.daily.delete-version' => 'Client Profile → Reports → Daily Report Delete Version',
        'client-profile.reports.daily.delete-all' => 'Client Profile → Reports → Daily Report Delete All Versions',
        'client-profile.reports.daily.send' => 'Client Profile → Reports → Daily Report Send',
        'client-profile.reports.progress.view' => 'Client Profile → Reports → Progress Report View',
        'client-profile.reports.progress.generate' => 'Client Profile → Reports → Progress Report Generate Draft',
        'client-profile.reports.progress.save-draft' => 'Client Profile → Reports → Progress Report Save Draft',
        'client-profile.reports.progress.pull-section' => 'Client Profile → Reports → Progress Report Pull Section',
        'client-profile.reports.progress.finalize' => 'Client Profile → Reports → Progress Report Finalize',
        'client-profile.reports.progress.regenerate' => 'Client Profile → Reports → Progress Report Regenerate',
        'client-profile.reports.progress.delete-version' => 'Client Profile → Reports → Progress Report Delete Version',
        'client-profile.reports.progress.delete-all' => 'Client Profile → Reports → Progress Report Delete All Versions',

        // Client Configuration Menu Access
        'client-configuration.access' => 'Client Configuration → Menu Access',

        // Client Management
        'clients.view' => 'Client Management → Client List View',
        'clients.create' => 'Client Management → Client Create',
        'clients.update' => 'Client Management → Client Update',
        'clients.delete' => 'Client Management → Client Delete',
        'clients.activate' => 'Client Management → Client Activate',
        'clients.deactivate' => 'Client Management → Client Deactivate',
        'clients.permissions.view' => 'Client Management → Client Instructor & Supervisor Assignment View',
        'clients.permissions.save' => 'Client Management → Client Instructor & Supervisor Assignment Update',

        // Client Program Management
        'client-program.view' => 'Client Program Management → Client Program View',
        'client-program.domain.create' => 'Client Program Management → Program Domain Create',
        'client-program.domain.update' => 'Client Program Management → Program Domain Update',
        'client-program.domain.delete' => 'Client Program Management → Program Domain Delete',
        'client-program.goal.create' => 'Client Program Management → Program Goal Create',
        'client-program.goal.update' => 'Client Program Management → Program Goal Update',
        'client-program.goal.delete' => 'Client Program Management → Program Goal Delete',
        'client-program.target.create' => 'Client Program Management → Program Target Create',
        'client-program.target.update' => 'Client Program Management → Program Target Update',
        'client-program.target.delete' => 'Client Program Management → Program Target Delete',
        'client-program.target.on-hold' => 'Client Program Management → Program Target On Hold',
        'client-program.edit-used-items' => 'Client Program Management → Override Data Lock: Edit Domain/Goal/Target Code & Name for Existing Data (Changes Reflect Across MIS and May Affect Supervisor/Tutor Context)',
        'client-program.manage-probe-set' => 'Client Program Management → Goal Target Probe Set Define/Update/Delete',
        'client-program.wizard-access' => 'Client Program Management → Program Wizard Access',

        // Master Program Management
        'master-program.view' => 'Master Program Management → Master Program View',
        'master-program.create' => 'Master Program Management → Master Program Create',
        'master-program.update' => 'Master Program Management → Master Program Update',
        'master-program.delete' => 'Master Program Management → Master Program Delete',
        'master-program.phases-rules.view' => 'Master Program Management → Target Phases, Probe Sets, Combinations & Rules View',

        // Master Mands Reinforcer (Master + Client Default)
        'mands-reinforcer.view' => 'Mands Reinforcer (Master + Client Default) → View',
        'mands-reinforcer.create' => 'Mands Reinforcer (Master + Client Default) → Create',
        'mands-reinforcer.update' => 'Mands Reinforcer (Master + Client Default) → Update',
        'mands-reinforcer.delete' => 'Mands Reinforcer (Master + Client Default) → Delete',

        // ABC Data (Master + Client Specific)
        'abc-data.view' => 'ABC Data (Master + Client Specific) → ABC Data View',
        'abc-data.update' => 'ABC Data (Master + Client Specific) → Create, Update & Delete',

        // Staff Member Management
        'user-configuration.access' => 'Staff Member Management → Menu Access',
        'user-configuration.groups.view' => 'Staff Member Management → Staff Groups View',
        'user-configuration.groups.save' => 'Staff Member Management → Staff Groups & Permissions Update',
        'user-configuration.users.view' => 'Staff Member Management → Staff Member List View',
        'user-configuration.users.create' => 'Staff Member Management → Staff Member Create',
        'user-configuration.users.update' => 'Staff Member Management → Staff Member Details, Direct Permissions & Password Update',
        'user-configuration.users.delete' => 'Staff Member Management → Staff Member Delete',
        'user-configuration.users.activate' => 'Staff Member Management → Staff Member Activate/Deactivate',
        'user-configuration.users.logs' => 'Staff Member Management → User Login Logs View',

        // MIS Configuration
        'app-configuration.access' => 'MIS Configuration → Menu Access',
        'app-configuration.general-settings.view' => 'MIS Configuration → General Settings View',
        'app-configuration.general-settings.save' => 'MIS Configuration → General Settings Update',
        'app-configuration.report-settings.view' => 'MIS Configuration → Report Settings View',
        'app-configuration.report-settings.save' => 'MIS Configuration → Report Settings Update',
        'app-configuration.module-settings.view' => 'MIS Configuration → Module Settings View',
        'app-configuration.module-settings.save' => 'MIS Configuration → Module Settings Update',

    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'app-configuration.*',
            'user-configuration.*',
            'client-configuration.*',
            'clients.*',
            'client-program.*',
            'master-program.*',
            'mands-reinforcer.*',
            'abc-data.*',
            'sessions.*',
            'data-sheet.*',
            'daily-data.*',
            'weekly-data.*',
            'client-profile.*',
        ],
        'management' => [],
        'supervisor' => [],
        'instructor' => [],
        'externalinstructor' => [],
        'admin' => [],
        'parent' => [],
        'user' => []
    ];
}
