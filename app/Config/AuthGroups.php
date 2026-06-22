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
        // KPI
        'kpi.access' => 'KPI\'s → Menu Access',
        'kpi.rate-data.view' => 'KPI → Rate Data View',
        'kpi.client-target.view' => 'KPI → Client\'s Target View',
        'kpi.supervisor-target.view' => 'KPI → Supervisor\'s Target View',

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

        // Graphs Menu Access
        'graphs.access' => 'Graphs → Menu Access',

        // Daily Data Graphs
        'graphs.daily-data.view' => 'Graphs → Daily Data Graphs View',

        // Cumulative Graphs
        'graphs.cumulative.view' => 'Graphs → Cumulative Graphs View',
        'graphs.cumulative.phase-line.view' => 'Graphs → Cumulative Graphs Phase Line View',
        'graphs.cumulative.phase-line.create' => 'Graphs → Cumulative Graphs Phase Line Create',
        'graphs.cumulative.phase-line.update' => 'Graphs → Cumulative Graphs Phase Line Update',
        'graphs.cumulative.phase-line.delete' => 'Graphs → Cumulative Graphs Phase Line Delete',

        // Rate Graphs
        'graphs.rate.view' => 'Graphs → Rate Graphs View',
        'graphs.rate.phase-line.view' => 'Graphs → Rate Graphs Phase Line View',
        'graphs.rate.phase-line.create' => 'Graphs → Rate Graphs Phase Line Create',
        'graphs.rate.phase-line.update' => 'Graphs → Rate Graphs Phase Line Update',
        'graphs.rate.phase-line.delete' => 'Graphs → Rate Graphs Phase Line Delete',
        'graphs.rate.target-months.view' => 'Graphs → Rate Graphs Target Months View',
        'graphs.rate.target-months.create' => 'Graphs → Rate Graphs Target Months Create',
        'graphs.rate.target-months.update' => 'Graphs → Rate Graphs Target Months Update',
        'graphs.rate.target-months.delete' => 'Graphs → Rate Graphs Target Months Delete',

        // Mand Graphs
        'graphs.mands.view' => 'Graphs → Mands Graphs View',
        
        // Stimulus Response Chain Graphs
        'graphs.stimulus-response-chain.view' => 'Graphs → Stimulus Response Chain Graphs View',

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
        'client-profile.reports.progress.view' => 'Client Profile → Reports → Progress Report View',

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

        // Reports
        'reporting.access' => 'Reports → Menu Access',
        'reporting.daily.view' => 'Reports → Daily Report View',
        'reporting.daily.generate' => 'Reports → Daily Report Generate Draft',
        'reporting.daily.save-draft' => 'Reports → Daily Report Save Draft',
        'reporting.daily.pull-section' => 'Reports → Daily Report Pull Section',
        'reporting.daily.finalize' => 'Reports → Daily Report Finalize',
        'reporting.daily.regenerate' => 'Reports → Daily Report Regenerate',
        'reporting.daily.send' => 'Reports → Daily Report Send',
        'reporting.daily.delete-version' => 'Reports → Daily Report Delete Version',
        'reporting.daily.delete-all' => 'Reports → Daily Report Delete All Versions',
        'reporting.progress.view' => 'Reports → Progress Report View',
        'reporting.progress.generate' => 'Reports → Progress Report Generate Draft',
        'reporting.progress.save-draft' => 'Reports → Progress Report Save Draft',
        'reporting.progress.pull-section' => 'Reports → Progress Report Pull Section',
        'reporting.progress.finalize' => 'Reports → Progress Report Finalize',
        'reporting.progress.regenerate' => 'Reports → Progress Report Regenerate',
        'reporting.progress.view-pdf' => 'Reports → Progress Report View PDF',
        'reporting.progress.delete-version' => 'Reports → Progress Report Delete Version',
        'reporting.progress.delete-all' => 'Reports → Progress Report Delete All Versions',
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
            'graphs.*',
            'kpi.*',
            'client-profile.*',
            'reporting.*',
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
