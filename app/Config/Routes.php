<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function () {
    return view('404-cover');
});

$routes->get('/', 'Dashboard::index');
$routes->post('/dashboard/data', 'Dashboard::data');
$routes->get('/access-denied', 'Dashboard::access_denied');

// Auth Routes (no permissions required for login/logout)
$routes->group('/', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    $routes->get('login', 'LoginController::loginView');
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', 'LoginController::logoutAction');
});

/************************************************************************************************************************************************** */
// Application Configuration Routes 
$routes->group('/app-configuration', ['namespace' => 'App\Controllers\AppConfiguration'], static function ($routes) {
    $routes->get('permission-sync', 'PermissionSyncController::index');
    $routes->get('general-settings', 'GeneralSettingsController::general', ['filter' => 'permission:app-configuration.general-settings.view']);
    $routes->post('general-settings/get-time-zones', 'GeneralSettingsController::getTimezones', ['filter' => 'permission:app-configuration.general-settings.view']);
    $routes->post('general-settings/save', 'GeneralSettingsController::saveGeneral', ['filter' => 'permission:app-configuration.general-settings.save']);
    $routes->post('permissions/sync', 'PermissionSyncController::sync');
    $routes->get('report-settings', 'ReportConfigurationController::index', ['filter' => 'permission:app-configuration.report-settings.view']);
    $routes->post('report-settings/save', 'ReportConfigurationController::save', ['filter' => 'permission:app-configuration.report-settings.save']);
    $routes->get('report-settings/logo', 'ReportConfigurationController::logo', ['filter' => 'permission:app-configuration.report-settings.view']);
    $routes->get('module-settings', 'ModuleSettingsController::index', ['filter' => 'permission:app-configuration.module-settings.view']);
    $routes->post('module-settings/save', 'ModuleSettingsController::save', ['filter' => 'permission:app-configuration.module-settings.save']);
});

/************************************************************************************************************************************************** */

// User Groups Routes
$routes->group('/user-configuration', ['namespace' => 'App\Controllers\UserConfiguration'], static function ($routes) {
    $routes->get('groups', 'GroupController::index', ['filter' => 'permission:user-configuration.groups.view']);
    $routes->get('groups/(:segment)', 'GroupController::show/$1', ['filter' => 'permission:user-configuration.groups.view']);
    $routes->post('groups/(:segment)', 'GroupController::save/$1', ['filter' => 'permission:user-configuration.groups.save']);
    $routes->get('groups/(:segment)/permissions', 'GroupController::permissions/$1', ['filter' => 'permission:user-configuration.groups.view']);
    $routes->post('groups/(:segment)/permissions', 'GroupController::savePermissions/$1', ['filter' => 'permission:user-configuration.groups.save']);
    $routes->post('groups/saveSinglePermission', 'GroupController::saveSinglePermission', ['filter' => 'permission:user-configuration.groups.save']);
});

/************************************************************************************************************************************************** */

// Users Management Routes
$routes->group('/user-configuration/users', ['namespace' => 'App\Controllers\UserConfiguration'], static function ($routes) {
    $routes->get('active-list', 'UserController::active_user_list', ['filter' => 'permission:user-configuration.users.view']);
    $routes->get('inactive-list', 'UserController::inactive_user_list', ['filter' => 'permission:user-configuration.users.view']);

    $routes->get('new', 'UserController::create', ['filter' => 'permission:user-configuration.users.create']);
    $routes->post('save', 'UserController::save', ['filter' => 'permission:user-configuration.users.create']);

    $routes->post('(:num)/save', 'UserController::save/$1', ['filter' => 'permission:user-configuration.users.update']);
    $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:user-configuration.users.update']);
    $routes->get('edit/(:num)/security', 'UserController::security/$1', ['filter' => 'permission:user-configuration.users.update']);
    $routes->post('edit/(:num)/changePassword', 'UserController::changePassword/$1', ['filter' => 'permission:user-configuration.users.update']);
    $routes->get('edit/(:num)/permissions', 'UserController::permissions/$1', ['filter' => 'permission:user-configuration.users.update']);
    $routes->post('edit/(:num)/permissions', 'UserController::savePermissions/$1', ['filter' => 'permission:user-configuration.users.update']);

    $routes->post('delete', 'UserController::delete', ['filter' => 'permission:user-configuration.users.delete']);

    $routes->post('activation', 'UserController::activation', ['filter' => 'permission:user-configuration.users.activate']);

    $routes->get('logged-in-logs', 'UserController::LoggedInLogs', ['filter' => 'permission:user-configuration.users.logs']);
});

/************************************************************************************************************************************************** */

// Client Configuration Related Routes
$routes->group('/clients', ['namespace' => 'App\Controllers\ClientConfiguration'], static function ($routes) {
    $routes->get('/', 'ClientController::index', ['filter' => 'permission:clients.view']);
    $routes->get('single/(:segment)', 'ClientController::detail_view/$1', ['filter' => 'permission:clients.view']);
    $routes->post('new', 'ClientController::save', ['filter' => 'permission:clients.create']);
    $routes->post('get-selected', 'ClientController::get_selected', ['filter' => 'permission:clients.view']);
    $routes->post('update', 'ClientController::update_client', ['filter' => 'permission:clients.update']);
    $routes->post('save-info', 'ClientController::save_info', ['filter' => 'permission:clients.update']);
    $routes->post('save-guardians', 'ClientController::save_guardians', ['filter' => 'permission:clients.update']);
    $routes->post('save-household', 'ClientController::save_household', ['filter' => 'permission:clients.update']);
    $routes->post('save-medical', 'ClientController::save_medical', ['filter' => 'permission:clients.update']);
    $routes->post('save-medications', 'ClientController::save_medications', ['filter' => 'permission:clients.update']);
    $routes->post('save-education', 'ClientController::save_education', ['filter' => 'permission:clients.update']);
    $routes->post('save-effective-teaching-procedures', 'ClientController::save_effective_teaching_procedures', ['filter' => 'permission:clients.update']);


    $routes->post('activate', 'ClientController::change_status', ['filter' => 'permission:clients.activate']);
    $routes->post('deactivate', 'ClientController::change_status', ['filter' => 'permission:clients.deactivate']);
    $routes->post('delete', 'ClientController::delete', ['filter' => 'permission:clients.delete']);
});

/************************************************************************************************************************************************** */

// Client Configuration Related Routes 
$routes->group('/client-profile', ['namespace' => 'App\Controllers\ClientProfile'], static function ($routes) {
    $routes->get('list', 'ClientProfileController::index', ['filter' => 'permission:client-profile.access']);
    $routes->get('dashboard/(:segment)', 'ClientProfileController::dashboard/$1', ['filter' => 'permission:client-profile.dashboard.view']);
    $routes->get('background/(:segment)', 'ClientProfileController::background/$1', ['filter' => 'permission:client-profile.client.client-detail.view']);
    $routes->get('key-information/(:segment)', 'ClientProfileController::keyInformation/$1', ['filter' => 'permission:client-profile.client.key-information.view']);


    $routes->get('currentPrograms/(:segment)', 'ClientProfileController::currentPrograms/$1', ['filter' => 'permission:client-profile.programs-skills.program-history.view']);

    $routes->get('activeProgram/(:segment)', 'ClientProfileController::activeProgram/$1', ['filter' => 'permission:client-profile.programs-skills.active-targets.view']);


    $routes->get('sessions/(:segment)', 'ClientProfileController::sessions/$1', ['filter' => 'permission:client-profile.summary-data.session-overview.view']);

    $routes->get('dailyData/(:segment)', 'ClientProfileController::dailyData/$1', ['filter' => 'permission:client-profile.summary-data.daily-data.view']);
    $routes->get('weeklyData/(:segment)', 'ClientProfileController::weeklyData/$1', ['filter' => 'permission:client-profile.summary-data.weekly-data.view']);

    // Datasheet (with prefix)
    $routes->group('dataSheet', static function ($routes) {
        $routes->get('programData/(:segment)', 'ClientProfileController::programData/$1', ['filter' => 'permission:client-profile.programs-skills.probe-data.view']);
        $routes->get('mandsData/(:segment)', 'ClientProfileController::mandsData/$1', ['filter' => 'permission:client-profile.mands.data.view']);
        $routes->get('currentMandList/(:segment)', 'ClientProfileController::currentMandList/$1', ['filter' => 'permission:client-profile.mands.dictionary.view']);
        $routes->post('currentMandList/list/(:segment)', 'ClientProfileController::currentMandListList/$1', ['filter' => 'permission:client-profile.mands.dictionary.view']);
        $routes->post('currentMandList/create/(:segment)', 'ClientProfileController::currentMandListCreate/$1', ['filter' => 'permission:client-profile.mands.dictionary.create']);
        $routes->post('currentMandList/update/(:segment)', 'ClientProfileController::currentMandListUpdate/$1', ['filter' => 'permission:client-profile.mands.dictionary.update']);
        $routes->post('currentMandList/delete/(:segment)', 'ClientProfileController::currentMandListDelete/$1', ['filter' => 'permission:client-profile.mands.dictionary.delete']);
        $routes->post('currentMandList/media/upload/(:segment)', 'ClientProfileController::currentMandListMediaUpload/$1', ['filter' => 'permission:client-profile.mands.dictionary.media.manage']);
        $routes->post('currentMandList/media/delete/(:segment)', 'ClientProfileController::currentMandListMediaDelete/$1', ['filter' => 'permission:client-profile.mands.dictionary.media.manage']);
        $routes->post('currentMandList/media/replace/(:segment)', 'ClientProfileController::currentMandListMediaReplace/$1', ['filter' => 'permission:client-profile.mands.dictionary.media.manage']);
        $routes->post('currentMandList/media/settings/(:segment)', 'ClientProfileController::currentMandListMediaSettings/$1', ['filter' => 'permission:client-profile.mands.dictionary.view']);
        $routes->get('currentMandList/media/view/(:segment)/(:num)', 'ClientProfileController::currentMandListMediaView/$1/$2', ['filter' => 'permission:client-profile.mands.dictionary.view']);
        $routes->get('defaultReinforcerData/(:segment)', 'ClientProfileController::defaultReinforcerData/$1', ['filter' => 'permission:client-profile.mands.active-targets.view']);
        $routes->get('pbData/(:segment)', 'ClientProfileController::pbData/$1', ['filter' => 'permission:client-profile.problem-behaviour.reduction-data.view']);
        $routes->get('defaultAbcData/(:segment)', 'ClientProfileController::defaultAbcData/$1', ['filter' => 'permission:client-profile.problem-behaviour.abc-template.view']);
        $routes->get('pcData/(:segment)', 'ClientProfileController::pcData/$1', ['filter' => 'permission:client-profile.programs-skills.program-adjustments.view']);
        $routes->get('skillsData/(:segment)', 'ClientProfileController::skillsData/$1', ['filter' => 'permission:client-profile.programs-skills.mastered-skills.view']);
        $routes->get('doiData/(:segment)', 'ClientProfileController::doiData/$1', ['filter' => 'permission:client-profile.programs-skills.developing-independence.view']);
    });

    // Graphs (with prefix)
    $routes->group('graphs', static function ($routes) {
        $routes->get('daily/(:segment)', 'ClientProfileController::graphsDaily/$1', ['filter' => 'permission:client-profile.graphs.daily.view']);
        $routes->get('stimulus-response-chain/(:segment)', 'ClientProfileController::graphsStimulusResponseChain/$1', ['filter' => 'permission:client-profile.graphs.stimulus-response-chain.view']);
        $routes->get('cumulative/(:segment)', 'ClientProfileController::graphsCumulative/$1', ['filter' => 'permission:client-profile.graphs.cumulative.view']);
        $routes->get('cumulative/phaseline/(:segment)', 'ClientProfileController::graphsCumulativePhaseline/$1', ['filter' => 'permission:client-profile.graphs.cumulative.view']);
        $routes->get('cumulative/domains-and-goals/(:segment)', 'ClientProfileController::graphsCumulativeByDomainAndGoal/$1', ['filter' => 'permission:client-profile.graphs.cumulative.view']);
        $routes->get('rate/(:segment)', 'ClientProfileController::graphsRate/$1', ['filter' => 'permission:client-profile.graphs.rate.view']);
        $routes->get('rate/phaseline/(:segment)', 'ClientProfileController::graphsRatePhaseline/$1', ['filter' => 'permission:client-profile.graphs.rate.view']);
        $routes->get('rate/target-months/(:segment)', 'ClientProfileController::graphsRateTargetMonths/$1', ['filter' => 'permission:client-profile.graphs.rate.view']);
        $routes->get('mands/(:segment)', 'ClientProfileController::graphsMands/$1', ['filter' => 'permission:client-profile.graphs.mands.view']);
        $routes->get('behaviour-reduction/(:segment)', 'ClientProfileController::graphsProblemBehaviour/$1', ['filter' => 'permission:client-profile.problem-behaviour.graphs.view']);
    });

    $routes->get('reports/daily/(:segment)', 'ClientProfileController::dailyReport/$1', ['filter' => 'permission:client-profile.reports.daily.view']);
    $routes->get('reports/progress/(:segment)', 'ClientProfileController::progressReport/$1', ['filter' => 'permission:client-profile.reports.progress.view']);
    $routes->post('reports/progress/data/(:segment)', 'ClientProfileController::progressReportData/$1', ['filter' => 'permission:client-profile.reports.progress.view']);
    $routes->post('reports/progress/versions/(:segment)', 'ClientProfileController::progressReportVersions/$1', ['filter' => 'permission:client-profile.reports.progress.view']);
    $routes->get('reports/progress/version/(:segment)/(:num)/pdf', 'ClientProfileController::progressReportPdf/$1/$2', ['filter' => 'permission:client-profile.reports.progress.view']);
});

$routes->group('shared-datasheet', ['namespace' => 'App\Controllers\Shared'], function ($routes) {
    $routes->post('filterProgramData', 'SharedDataSheetController::filterProgramData');
    $routes->post('getGoalsByDomain', 'SharedDataSheetController::getGoalsByDomain');
    $routes->post('transitionList', 'SharedDataSheetController::transitionList');
    $routes->post('stimulusSteps', 'SharedDataSheetController::stimulusTargetStepsDetail');
    $routes->post('mandsDailyData', 'SharedDataSheetController::mandsDailyData');
    $routes->post('filterSkillsRetained', 'SharedDataSheetController::filterSkillsRetained');
    $routes->post('filterDOITargets', 'SharedDataSheetController::filterDOITargets');
    $routes->post('filterProgramChange', 'SharedDataSheetController::filterProgramChange');
});



/************************************************************************************************************************************************** */
// Client Permission Routes
$routes->group('/clients/permissions', ['namespace' => 'App\Controllers\ClientConfiguration'], static function ($routes) {
    $routes->get('/', 'ClientPermissionController::index', ['filter' => 'permission:clients.permissions.view']);
    $routes->post('list', 'ClientPermissionController::list', ['filter' => 'permission:clients.permissions.view']);
    $routes->post('save', 'ClientPermissionController::save', ['filter' => 'permission:clients.permissions.save']);
    $routes->post('save-supervisor', 'ClientPermissionController::update_default_supervisor', ['filter' => 'permission:clients.permissions.save']);
});

/************************************************************************************************************************************************** */

// MIS Client Program Routes
$routes->group('/client-program', ['namespace' => 'App\Controllers\ClientProgram'], static function ($routes) {
    $routes->get('', 'ClientDomainController::client_list', ['filter' => 'permission:client-program.view']);
    $routes->get('(:segment)/domains', 'ClientDomainController::index/$1', ['filter' => 'permission:client-program.view']);
    $routes->post('domains/list', 'ClientDomainController::list', ['filter' => 'permission:client-program.view']);
    $routes->post('domains/single', 'ClientDomainController::single', ['filter' => 'permission:client-program.view']);

    $routes->post('domains/create', 'ClientDomainController::create', ['filter' => 'permission:client-program.domain.create']);
    $routes->post('domains/update', 'ClientDomainController::update', ['filter' => 'permission:client-program.domain.update']);
    $routes->post('domains/delete', 'ClientDomainController::delete', ['filter' => 'permission:client-program.domain.delete']);

    $routes->get('(:segment)/goals', 'ClientGoalController::index/$1', ['filter' => 'permission:client-program.view']);
    $routes->post('goals/list', 'ClientGoalController::list', ['filter' => 'permission:client-program.view']);
    $routes->post('goals/single', 'ClientGoalController::single', ['filter' => 'permission:client-program.view']);

    $routes->post('goals/create', 'ClientGoalController::create', ['filter' => 'permission:client-program.goal.create']);
    $routes->post('goals/update', 'ClientGoalController::update', ['filter' => 'permission:client-program.goal.update']);
    $routes->post('goals/delete', 'ClientGoalController::delete', ['filter' => 'permission:client-program.goal.delete']);

    $routes->get('(:segment)/targets', 'ClientTargetController::index/$1', ['filter' => 'permission:client-program.view']);
    $routes->post('targets/list', 'ClientTargetController::list', ['filter' => 'permission:client-program.view']);
    $routes->post('targets/single', 'ClientTargetController::single', ['filter' => 'permission:client-program.view']);

    $routes->post('targets/create', 'ClientTargetController::create', ['filter' => 'permission:client-program.target.create']);
    $routes->post('targets/update', 'ClientTargetController::update', ['filter' => 'permission:client-program.target.update']);
    $routes->post('targets/delete', 'ClientTargetController::delete', ['filter' => 'permission:client-program.target.delete']);
    $routes->post('targets/on-hold', 'ClientTargetController::onHold');

    // Client Program Tree View

    $routes->get('treeView', 'ClientProgramController::treeView', ['filter' => 'permission:client-program.view']);
    $routes->get('client-list', 'ClientProgramController::getClientList', ['filter' => 'permission:client-program.view']);

    $routes->post('program-list', 'ClientProgramController::getClientProgramInfo', ['filter' => 'permission:client-program.view']);
    $routes->post('get-client-selected-goal-probe-set', 'ClientProgramController::getClientSelectedGoalProbeSet', ['filter' => 'permission:client-program.view']);

    // Goal Probe Set Configuration
    $routes->post('goal/check-goal-probe-sets', 'ClientGoalRulesController::check_goal_probe_sets', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/create-probe-set', 'ClientGoalRulesController::create_probe_set_configuration', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/get-probe-set-phase-combinations', 'ClientGoalRulesController::get_probe_set_phase_combinations', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/get-probe-set-detail-and-rules', 'ClientGoalRulesController::get_probe_set_detail_and_rules', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/save-probe-set-and-rules', 'ClientGoalRulesController::save_probe_set_and_rules', ['filter' => 'permission:client-program.manage-probe-set']);

    $routes->post('goal/load-client-existing-probe-sets-list', 'ClientGoalRulesController::load_client_existing_probe_sets_list', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/load-client-existing-probe-sets-edit-form', 'ClientGoalRulesController::load_client_existing_probe_sets_edit_form', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/update-client-existing-probe-set', 'ClientGoalRulesController::update_client_existing_probe_set', ['filter' => 'permission:client-program.manage-probe-set']);

    $routes->post('goal/activate-client-probe-set', 'ClientGoalRulesController::activateClientProbeSet', ['filter' => 'permission:client-program.manage-probe-set']);
    $routes->post('goal/delete-client-probe-set', 'ClientGoalRulesController::deleteClientProbeSet', ['filter' => 'permission:client-program.manage-probe-set']);

    $routes->post('goal/load-client-active-probe-set-rules', 'ClientGoalRulesController::clientActiveProbeSetRules', ['filter' => 'permission:client-program.view']);

    // Target Stimulus Steps Configuration
    $routes->post('target/load-stimulus-steps-editor', 'ClientProgramController::loadStimulusStepsEditor');
    $routes->post('target/get-client-selected-stimulus-target-updated-detail', 'ClientProgramController::getClientSelectedStimulusTargetUpdatedDetail');
    $routes->post('target/add-stimulus-step', 'ClientProgramController::addStimulusStep');
    $routes->post('target/update-stimulus-step', 'ClientProgramController::updateStimulusStep');
    $routes->post('target/delete-stimulus-step', 'ClientProgramController::deleteStimulusStep');
    $routes->post('target/reorder-stimulus-steps', 'ClientProgramController::reorderStimulusSteps');

    // Target Stimulus Chain Configuration
    $routes->post('target/load-stimulus-chain-editor', 'ClientProgramController::loadStimulusChainEditor');
    $routes->post('target/save-stimulus-chain', 'ClientProgramController::saveStimulusChain');
});

/************************************************************************************************************************************************** */

// MIS Client Program Wizard Routes
$routes->group('/client-program/wizard', ['namespace' => 'App\Controllers\ClientProgram'], static function ($routes) {
    $routes->get('', 'CPWizardController::index', ['filter' => 'permission:client-program.wizard-access']);
    $routes->get('client-list', 'CPWizardController::getClientList', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('program-list', 'CPWizardController::getMasterProgramWithClientInfo', ['filter' => 'permission:client-program.wizard-access']);


    $routes->post('domains/list', 'CPWizardController::domain_list', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('domains/create', 'CPWizardController::assign_domain_to_client', ['filter' => 'permission:client-program.wizard-access']);

    $routes->post('goals/list', 'CPWizardController::goal_list', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('goals/create', 'CPWizardController::assign_goal_to_client', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('goals/search', 'CPWizardController::goal_list_by_filter', ['filter' => 'permission:client-program.wizard-access']);

    $routes->post('targets/list', 'CPWizardController::target_list', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('targets/create', 'CPWizardController::assign_target_to_client', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('targets/goals', 'CPWizardController::get_client_domain_goals', ['filter' => 'permission:client-program.wizard-access']);
    $routes->post('targets/search', 'CPWizardController::target_list_by_filter', ['filter' => 'permission:client-program.wizard-access']);
});

/************************************************************************************************************************************************** */

// MIS Master Program Routes
$routes->group('/master-program', ['namespace' => 'App\Controllers\MasterProgram'], static function ($routes) {
    $routes->get('domains', 'MasterDomainController::index', ['filter' => 'permission:master-program.view']);
    $routes->get('domains/list', 'MasterDomainController::list', ['filter' => 'permission:master-program.view']);
    $routes->post('domains/single', 'MasterDomainController::single', ['filter' => 'permission:master-program.view']);
    $routes->post('domains/create', 'MasterDomainController::create', ['filter' => 'permission:master-program.create']);
    $routes->post('domains/update', 'MasterDomainController::update', ['filter' => 'permission:master-program.update']);
    $routes->post('domains/delete', 'MasterDomainController::delete', ['filter' => 'permission:master-program.delete']);

    $routes->get('goals', 'MasterGoalController::index', ['filter' => 'permission:master-program.view']);
    $routes->post('goals/list', 'MasterGoalController::list', ['filter' => 'permission:master-program.view']);
    $routes->post('goals/single', 'MasterGoalController::single', ['filter' => 'permission:master-program.view']);
    $routes->post('goals/create', 'MasterGoalController::create', ['filter' => 'permission:master-program.create']);
    $routes->post('goals/update', 'MasterGoalController::update', ['filter' => 'permission:master-program.update']);
    $routes->post('goals/delete', 'MasterGoalController::delete', ['filter' => 'permission:master-program.delete']);

    $routes->get('targets', 'MasterTargetController::index', ['filter' => 'permission:master-program.view']);
    $routes->post('targets/list', 'MasterTargetController::list', ['filter' => 'permission:master-program.view']);
    $routes->post('targets/single', 'MasterTargetController::single', ['filter' => 'permission:master-program.view']);
    $routes->post('targets/create', 'MasterTargetController::create', ['filter' => 'permission:master-program.create']);
    $routes->post('targets/update', 'MasterTargetController::update', ['filter' => 'permission:master-program.update']);
    $routes->post('targets/delete', 'MasterTargetController::delete', ['filter' => 'permission:master-program.delete']);

    $routes->get('phases-rules-setup', 'TargetPhasesRulesSetupController::index', ['filter' => 'permission:master-program.phases-rules.view']);
    $routes->get('probe-set-details/(:num)', 'TargetPhasesRulesSetupController::getProbeSetDetails/$1', ['filter' => 'permission:master-program.phases-rules.view']);

    $routes->get('', 'MasterProgramController::index', ['filter' => 'permission:master-program.view']);
    $routes->get('tree-view/list', 'MasterProgramController::list', ['filter' => 'permission:master-program.view']);
});

/************************************************************************************************************************************************** */


$routes->group('/mands/reinforcer', ['namespace' => 'App\Controllers\Mands'], static function ($routes) {
    $routes->get('/', 'MandsReinforcerController::index', ['filter' => 'permission:mands-reinforcer.view']);
    $routes->get('list', 'MandsReinforcerController::list', ['filter' => 'permission:mands-reinforcer.view']);
    $routes->post('single', 'MandsReinforcerController::single', ['filter' => 'permission:mands-reinforcer.view']);
    $routes->post('client-defaults/list', 'MandsReinforcerController::clientDefaultsList', ['filter' => 'permission:mands-reinforcer.view']);
    $routes->post('client-defaults/save', 'MandsReinforcerController::clientDefaultsSave', ['filter' => 'permission:mands-reinforcer.update']);
    $routes->post('create', 'MandsReinforcerController::create', ['filter' => 'permission:mands-reinforcer.create']);
    $routes->post('update', 'MandsReinforcerController::update', ['filter' => 'permission:mands-reinforcer.update']);
    $routes->post('delete', 'MandsReinforcerController::delete', ['filter' => 'permission:mands-reinforcer.delete']);
    //$routes->get('search/(:segment)', 'MandsReinforcerController::search/$1');
    $routes->get('search', 'MandsReinforcerController::search');
});

/************************************************************************************************************************************************** */

$routes->group('/abc-data', ['namespace' => 'App\Controllers\Abc'], static function ($routes) {
    $routes->get('/', 'AbcDataController::index', ['filter' => 'permission:abc-data.view']);

    $routes->get('master/list', 'AbcDataController::masterList', ['filter' => 'permission:abc-data.view']);

    $routes->get('client/list', 'AbcDataController::clientList', ['filter' => 'permission:abc-data.view']);
    $routes->post('client/save', 'AbcDataController::clientSave', ['filter' => 'permission:abc-data.update']);
});

/************************************************************************************************************************************************** */

// Clients Completed Session Routes
$routes->group('/sessions/daily', ['namespace' => 'App\Controllers\ClientSessions'], static function ($routes) {
    $routes->get('/', 'DailySessionsController::index', ['filter' => 'permission:sessions.daily.view']);
    $routes->post('list', 'DailySessionsController::list', ['filter' => 'permission:sessions.daily.view']);
    $routes->post('single', 'DailySessionsController::single', ['filter' => 'permission:sessions.daily.view']);
    $routes->post('create', 'DailySessionsController::create', ['filter' => 'permission:sessions.daily.create']);
    $routes->post('update', 'DailySessionsController::update', ['filter' => 'permission:sessions.daily.update']);
    $routes->post('delete', 'DailySessionsController::deleteSession', ['filter' => 'permission:sessions.daily.delete']);

    $routes->post('end-session-manually', 'DailySessionsController::endSessionManually', ['filter' => 'permission:sessions.daily.update']);
});

/************************************************************************************************************************************************** */

$routes->group('/sessions/live', ['namespace' => 'App\Controllers\ClientSessions'], static function ($routes) {
    $routes->get('/', 'LiveSessionsController::index', ['filter' => 'permission:sessions.live.run']);
    // Prefixed routes for liveSession with client ID
    $routes->get('client/(:segment)', 'LiveSessionsController::liveSession/$1', ['filter' => 'permission:sessions.live.run']);
    $routes->post('client/(:segment)', 'LiveSessionsController::liveSession/$1', ['filter' => 'permission:sessions.live.run']);

    $routes->post('start', 'LiveSessionsController::startSession', ['filter' => 'permission:sessions.live.run']);
    $routes->post('end', 'LiveSessionsController::endSession', ['filter' => 'permission:sessions.live.run']);

    $routes->post('get-probe-sets', 'LiveSessionsController::get_probe_sets', ['filter' => 'permission:sessions.live.run']);

    $routes->post('get-target-list', 'LiveSessionsController::get_target_list', ['filter' => 'permission:sessions.live.run']);
    $routes->post('viewTargetHistory', 'LiveSessionsController::viewTargetHistory', ['filter' => 'permission:sessions.live.run']);
    $routes->post('target/save', 'LiveSessionsController::save_session_target', ['filter' => 'permission:sessions.live.run']);
    $routes->post('target/save_percentage_probe_yes_no', 'LiveSessionsController::save_percentage_session_target', ['filter' => 'permission:sessions.live.run']);

    $routes->post('teaching/updateDuration', 'LiveSessionsController::updateDuration', ['filter' => 'permission:sessions.live.run']);
    $routes->post('teaching/updatePBDuration', 'LiveSessionsController::updatePBDuration', ['filter' => 'permission:sessions.live.run']);
    $routes->post('getPbRecordList', 'LiveSessionsController::getPbRecordList', ['filter' => 'permission:sessions.live.run']);
    $routes->post('getPbRecordForm', 'LiveSessionsController::getPbRecordForm', ['filter' => 'permission:sessions.live.run']);
    $routes->post('saveProblemBehaviorRecord', 'LiveSessionsController::saveProblemBehaviorRecord', ['filter' => 'permission:sessions.live.run']);

    $routes->post('mands', 'LiveSessionsController::get_mands_form', ['filter' => 'permission:sessions.live.run']);
    $routes->post('mands/save', 'LiveSessionsController::save_mands_form', ['filter' => 'permission:sessions.live.run']);
    $routes->post('mands/list', 'LiveSessionsController::get_mands_session_list', ['filter' => 'permission:sessions.live.run']);
    $routes->post('mands/updateMandsDuration', 'LiveSessionsController::updateMandsDuration', ['filter' => 'permission:sessions.live.run']);

    $routes->post('target/save_stimulus_baseline_attempt', 'LiveSessionsController::saveStimulusBaselineAttempt', ['filter' => 'permission:sessions.live.run']);
    $routes->post('target/save_stimulus_total_task_attempt', 'LiveSessionsController::saveStimulusTotalTaskAttempt', ['filter' => 'permission:sessions.live.run']);
    $routes->post('target/save_stimulus_forward_attempt', 'LiveSessionsController::saveStimulusForwardAttempt', ['filter' => 'permission:sessions.live.run']);
    $routes->post('target/save_stimulus_backward_attempt', 'LiveSessionsController::saveStimulusBackwardAttempt', ['filter' => 'permission:sessions.live.run']);
});

/************************************************************************************************************************************************** */

// Session Review Routes    
$routes->group('/sessions/review', ['namespace' => 'App\Controllers\ClientSessions'], function ($routes) {
    $routes->get('(:num)', 'SessionReviewController::index/$1', ['filter' => 'permission:sessions.review']);
    $routes->get('(:num)/mands', 'SessionReviewController::mandsReview/$1', ['filter' => 'permission:sessions.review']);
    $routes->get('(:num)/problemBehavior', 'SessionReviewController::problemBehaviorReview/$1', ['filter' => 'permission:sessions.review']);
    $routes->get('(:num)/processConfirmation', 'SessionReviewController::processConfirmation/$1', ['filter' => 'permission:sessions.review']);
    $routes->post('getProcessedLog', 'SessionReviewController::getProcessingDetails', ['filter' => 'permission:sessions.review']);

    $routes->get('(:num)/sessionDuration', 'SessionReviewController::sessionDuration/$1', ['filter' => 'permission:sessions.review']);
    $routes->post('teachingDurationList', 'SessionReviewController::teachingDurationList', ['filter' => 'permission:sessions.review']);
    $routes->post('mandsDurationList', 'SessionReviewController::mandsDurationList', ['filter' => 'permission:sessions.review']);
    $routes->post('pbDurationList', 'SessionReviewController::pbDurationList', ['filter' => 'permission:sessions.review']);

    $routes->get('manual-target-entry/(:num)', 'SessionReviewController::getTargetScreenForManuallyEntry/$1', ['filter' => 'permission:sessions.review']);
    $routes->post('get-target-list', 'SessionReviewController::get_target_list', ['filter' => 'permission:sessions.review']);
    $routes->post('target/save', 'SessionReviewController::save_session_target', ['filter' => 'permission:sessions.review']);
    $routes->post('target/save_percentage_probe_yes_no', 'SessionReviewController::save_percentage_session_target', ['filter' => 'permission:sessions.review']);
    $routes->post('viewTargetConflictDetail', 'SessionReviewController::viewTargetConflictDetail', ['filter' => 'permission:sessions.review']);

    $routes->post('single', 'SessionReviewController::single', ['filter' => 'permission:sessions.review']);
    $routes->post('update', 'SessionReviewController::updateData', ['filter' => 'permission:sessions.review']);
    $routes->post('update_p_yes_no', 'SessionReviewController::updatePercentageYesNoData', ['filter' => 'permission:sessions.review']);

    $routes->post('delete', 'SessionReviewController::deleteTarget', ['filter' => 'permission:sessions.review']);

    $routes->post('getPBRecord', 'SessionReviewController::getPBRecord', ['filter' => 'permission:sessions.review']);
    $routes->post('createPBRecord', 'SessionReviewController::createPBRecord', ['filter' => 'permission:sessions.review']);
    $routes->post('updatePBRecord', 'SessionReviewController::updatePBRecord', ['filter' => 'permission:sessions.review']);
    $routes->post('deletePBRecord', 'SessionReviewController::deletePBRecord', ['filter' => 'permission:sessions.review']);

    $routes->get('manually-mands-entry/(:num)', 'SessionReviewController::get_mands_form_manually/$1', ['filter' => 'permission:sessions.review']);
    $routes->post('mands/save', 'SessionReviewController::save_mands_form_manually', ['filter' => 'permission:sessions.review']);
    $routes->post('getMandsRecord', 'SessionReviewController::getMandsRecord', ['filter' => 'permission:sessions.review']);
    $routes->post('updateMandsRecord', 'SessionReviewController::updateMandsRecord', ['filter' => 'permission:sessions.review']);
    $routes->post('deleteMandsRecord', 'SessionReviewController::deleteMandsRecord', ['filter' => 'permission:sessions.review']);

    // CRUD Operations for Teaching & Mands Duration
    $routes->post('createDuration', 'SessionReviewController::createDuration', ['filter' => 'permission:sessions.review']);
    $routes->post('getDuration', 'SessionReviewController::getDuration', ['filter' => 'permission:sessions.review']);
    $routes->post('updateDuration', 'SessionReviewController::updateDuration', ['filter' => 'permission:sessions.review']);
    $routes->post('deleteDuration', 'SessionReviewController::deleteDuration', ['filter' => 'permission:sessions.review']);


    $routes->post('target/save_stimulus_baseline_attempt', 'SessionReviewController::saveStimulusBaselineAttempt', ['filter' => 'permission:sessions.review']);
    $routes->post('target/save_stimulus_total_task_attempt', 'SessionReviewController::saveStimulusTotalTaskAttempt', ['filter' => 'permission:sessions.review']);
    $routes->post('target/save_stimulus_forward_attempt', 'SessionReviewController::saveStimulusForwardAttempt', ['filter' => 'permission:sessions.review']);
    $routes->post('target/save_stimulus_backward_attempt', 'SessionReviewController::saveStimulusBackwardAttempt', ['filter' => 'permission:sessions.review']);
});



/************************************************************************************************************************************************** */

// Session Process Routes    
$routes->group('/sessions/process', ['namespace' => 'App\Controllers\ClientSessions'], function ($routes) {
    //$routes->post('single', 'SessionProcessingController::processSingle', ['filter' => 'permission:sessions.review']);
    $routes->post('all', 'SessionProcessingController::processAll', ['filter' => 'permission:sessions.review']);
    $routes->post('conflict', 'SessionProcessingController::processConflict', ['filter' => 'permission:sessions.review']);
});

/************************************************************************************************************************************************** */

// Program Change Routes    
$routes->group('/sessions/programChange', ['namespace' => 'App\Controllers\ClientProgram'], static function ($routes) {
    $routes->post('getForm', 'ProgramChangeController::getForm', ['filter' => 'permission:sessions.program-change']);
    $routes->post('saveProgramChange', 'ProgramChangeController::saveProgramChange', ['filter' => 'permission:sessions.program-change']);
});

/************************************************************************************************************************************************** */

// Data sheet Routes 
$routes->group('/dataSheet', ['namespace' => 'App\Controllers\ClientDataSheet'], static function ($routes) {
    $routes->get('/', 'DataSheetController::index', ['filter' => 'permission:data-sheet.view']);
    $routes->get('programData/(:segment)', 'DataSheetController::programData/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('filterProgramData', 'DataSheetController::filterProgramData', ['filter' => 'permission:data-sheet.view']);
    $routes->post('programData/percentage-probe-yes-no/transition-list', 'DataSheetController::transitionListYesNoPercentageProbe', ['filter' => 'permission:data-sheet.view']);
    $routes->post('programData/stimulust-program/steps-data-sheet', 'DataSheetController::stimulusTargetStepsDetail', ['filter' => 'permission:data-sheet.view']);

    $routes->get('mandsDataSheet/(:segment)', 'DataSheetController::mandsDataSheet/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('mandsDailyData', 'DataSheetController::mandsDailyData', ['filter' => 'permission:data-sheet.view']);
    $routes->get('pbDataSheet/(:segment)', 'DataSheetController::pbDataSheet/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('getGoalsByDomain', 'DataSheetController::getGoalsByDomain', ['filter' => 'permission:data-sheet.view']);

    $routes->get('getDOITargets/(:segment)', 'DataSheetController::getDOITargets/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('filterDOI', 'DataSheetController::filterDOI', ['filter' => 'permission:data-sheet.view']);

    $routes->get('getSkillsRetained/(:segment)', 'DataSheetController::getSkillsRetained/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('filterSkillsRetained', 'DataSheetController::filterSkillsRetained', ['filter' => 'permission:data-sheet.view']);

    $routes->post('getClientGoalsForFilter', 'DataSheetController::getClientGoalsForFilter', ['filter' => 'permission:data-sheet.view']);

    $routes->get('getProgramChange/(:segment)', 'DataSheetController::getProgramChange/$1', ['filter' => 'permission:data-sheet.view']);
    $routes->post('filterProgramChange', 'DataSheetController::filterProgramChange', ['filter' => 'permission:data-sheet.view']);
});

/************************************************************************************************************************************************** */

// Computed Daily Data Routes
$routes->group('/dailyData', ['namespace' => 'App\Controllers\ClientDailyData'], static function ($routes) {
    $routes->get('computedData', 'ComputedDailyDataController::index', ['filter' => 'permission:daily-data.computed-data.view']);
    $routes->post('computedData/list', 'ComputedDailyDataController::list', ['filter' => 'permission:daily-data.computed-data.view']);
});

// daily session data Manually defined  
$routes->group('/sessions/manual', ['namespace' => 'App\Controllers\ClientDailyData'], static function ($routes) {
    $routes->post('new', 'ManualDailyDataController::create_manual_session', ['filter' => 'permission:daily-data.manual.create']);
    $routes->post('get-selected', 'ManualDailyDataController::get_selected_manual_session', ['filter' => 'permission:daily-data.manual.update']);
    $routes->post('update', 'ManualDailyDataController::update_manual_session', ['filter' => 'permission:daily-data.manual.update']);
    $routes->post('create_no_session', 'ManualDailyDataController::create_manual_no_session', ['filter' => 'permission:daily-data.manual.create-no-session']);
    $routes->post('delete', 'ManualDailyDataController::delete_manual_session', ['filter' => 'permission:daily-data.manual.delete']);
});
$routes->group('/sessions/weekly', ['namespace' => 'App\Controllers\ClientDailyData'], static function ($routes) {

    $routes->get('', 'ManualWeeklyDataController::index', ['filter' => 'permission:weekly-data.manual.view']);
    $routes->post('list', 'ManualWeeklyDataController::list', ['filter' => 'permission:weekly-data.manual.view']);

    $routes->post('new', 'ManualWeeklyDataController::create', ['filter' => 'permission:weekly-data.manual.create']);
    $routes->post('get-selected', 'ManualWeeklyDataController::get_selected', ['filter' => 'permission:weekly-data.manual.update']);
    $routes->post('update', 'ManualWeeklyDataController::update', ['filter' => 'permission:weekly-data.manual.update']);

    $routes->post('create_no_session', 'ManualWeeklyDataController::create_no_session', ['filter' => 'permission:weekly-data.manual.create-no-session']);
    $routes->post('delete', 'ManualWeeklyDataController::delete', ['filter' => 'permission:weekly-data.manual.delete']);
});

/************************************************************************************************************************************************** */

// Graphs Routes
$routes->group('/graphs/mands', ['namespace' => 'App\Controllers\ClientGraphs'], static function ($routes) {
    $routes->get('/', 'MandsGraphsController::index', ['filter' => 'permission:graphs.mands.view']);
    $routes->post('/', 'MandsGraphsController::graphs_data', ['filter' => 'permission:graphs.mands.view']);
});

$routes->group('/graphs/stimulus-response-chain', ['namespace' => 'App\Controllers\ClientGraphs'], static function ($routes) {
    $routes->get('/', 'StimulusResponseChainGraphsController::index', ['filter' => 'permission:graphs.stimulus-response-chain.view']);
    $routes->post('/', 'StimulusResponseChainGraphsController::graphs_data', ['filter' => 'permission:graphs.stimulus-response-chain.view']);
    $routes->post('getClientDomains', 'StimulusResponseChainGraphsController::getClientDomains', ['filter' => 'permission:graphs.stimulus-response-chain.view']);
    $routes->post('getClientDomainGoals', 'StimulusResponseChainGraphsController::getClientDomainGoals', ['filter' => 'permission:graphs.stimulus-response-chain.view']);
    $routes->post('getClientGoalTargets', 'StimulusResponseChainGraphsController::getClientGoalTargets', ['filter' => 'permission:graphs.stimulus-response-chain.view']);
});

$routes->group('/graphs/dailyData', ['namespace' => 'App\Controllers\ClientGraphs'], static function ($routes) {
    $routes->get('/', 'DailyDataGraphsController::index', ['filter' => 'permission:graphs.daily-data.view']);
    $routes->post('/', 'DailyDataGraphsController::graphs_data', ['filter' => 'permission:graphs.daily-data.view']);
});

$routes->group('/graphs/cumulative', ['namespace' => 'App\Controllers\ClientGraphs'], static function ($routes) {
    $routes->get('/', 'CumulativeGraphsController::index', ['filter' => 'permission:graphs.cumulative.view']);
    $routes->post('/', 'CumulativeGraphsController::graphs_data', ['filter' => 'permission:graphs.cumulative.view']);

    $routes->get('phase-line', 'CumulativeGraphsController::index_phase_line', ['filter' => 'permission:graphs.cumulative.phase-line.view']);

    $routes->post('phase-line/list', 'PhaseLineController::list', ['filter' => 'permission:graphs.cumulative.phase-line.view']);
    $routes->post('phase-line/new', 'PhaseLineController::create', ['filter' => 'permission:graphs.cumulative.phase-line.create']);
    $routes->post('phase-line/get-selected', 'PhaseLineController::get_selected', ['filter' => 'permission:graphs.cumulative.phase-line.update']);
    $routes->post('phase-line/update', 'PhaseLineController::update', ['filter' => 'permission:graphs.cumulative.phase-line.update']);
    $routes->post('phase-line/delete', 'PhaseLineController::delete', ['filter' => 'permission:graphs.cumulative.phase-line.delete']);

    $routes->get('domains-and-goals', 'CumulativeGraphsController::cumulative_graph_by_domain_and_goal_index', ['filter' => 'permission:graphs.cumulative.view']);
    $routes->post('domains-and-goals', 'CumulativeGraphsController::cumulative_graph_by_domain_and_goal_data', ['filter' => 'permission:graphs.cumulative.view']);
    $routes->post('getClientDomains', 'CumulativeGraphsController::getClientDomains', ['filter' => 'permission:graphs.cumulative.view']);
    $routes->post('getClientDomainGoals', 'CumulativeGraphsController::getClientDomainGoals', ['filter' => 'permission:graphs.cumulative.view']);
});

$routes->group('/graphs/rate', ['namespace' => 'App\Controllers\ClientGraphs'], static function ($routes) {
    $routes->get('/', 'RateGraphsController::index', ['filter' => 'permission:graphs.rate.view']);
    $routes->post('/', 'RateGraphsController::graphs_data', ['filter' => 'permission:graphs.rate.view']);

    $routes->get('phase-line', 'RateGraphsController::index_phase_line', ['filter' => 'permission:graphs.rate.phase-line.view']);

    $routes->post('phase-line/list', 'PhaseLineController::list', ['filter' => 'permission:graphs.rate.phase-line.view']);
    $routes->post('phase-line/new', 'PhaseLineController::create', ['filter' => 'permission:graphs.rate.phase-line.create']);
    $routes->post('phase-line/get-selected', 'PhaseLineController::get_selected', ['filter' => 'permission:graphs.rate.phase-line.update']);
    $routes->post('phase-line/update', 'PhaseLineController::update', ['filter' => 'permission:graphs.rate.phase-line.update']);
    $routes->post('phase-line/delete', 'PhaseLineController::delete', ['filter' => 'permission:graphs.rate.phase-line.delete']);


    $routes->get('target-months', 'RateGraphsController::index_target_months', ['filter' => 'permission:graphs.rate.target-months.view']);

    $routes->post('target-months/list', 'TargetMonthController::list', ['filter' => 'permission:graphs.rate.target-months.view']);
    $routes->post('target-months/new', 'TargetMonthController::create', ['filter' => 'permission:graphs.rate.target-months.create']);
    $routes->post('target-months/get-selected', 'TargetMonthController::get_selected', ['filter' => 'permission:graphs.rate.target-months.update']);
    $routes->post('target-months/update', 'TargetMonthController::update', ['filter' => 'permission:graphs.rate.target-months.update']);
    $routes->post('target-months/delete', 'TargetMonthController::delete', ['filter' => 'permission:graphs.rate.target-months.delete']);
});

/************************************************************************************************************************************************** */

// KPI Routes
$routes->group('/kpi', ['namespace' => 'App\Controllers\KPI'], static function ($routes) {
    $routes->get('rate-data', 'KPIController::rate_data', ['filter' => 'permission:kpi.rate-data.view']);
    $routes->get('client-target', 'KPIController::client_target', ['filter' => 'permission:kpi.client-target.view']);
    $routes->post('client-target/data', 'KPIController::client_target_data', ['filter' => 'permission:kpi.client-target.view']);
    $routes->post('client-target-month-vise/data', 'KPIController::client_target_data_by_month', ['filter' => 'permission:kpi.client-target.view']);
    $routes->get('supervisor-target', 'KPIController::supervisor_target', ['filter' => 'permission:kpi.supervisor-target.view']);
});

 


//$routes->get('test-ai-agent', 'TestAiAgent::index');


/************************************************************************************************************************************************** */

// Reporting Routes
$routes->group('/reports/daily', ['namespace' => 'App\Controllers\Reports'], static function ($routes) {
    $routes->get('', 'DailyReportController::index', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('state-token', 'DailyReportController::stateToken', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('data', 'DailyReportController::data', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('check-generate', 'DailyReportController::checkGenerate', ['filter' => 'permission:reporting.daily.generate']);
    $routes->post('generate', 'DailyReportController::generate', ['filter' => 'permission:reporting.daily.generate']);
    $routes->post('versions', 'DailyReportController::versions', ['filter' => 'permission:reporting.daily.view']);
    $routes->get('version/(:num)/draft', 'DailyReportController::draft/$1', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('version/(:num)/save-draft', 'DailyReportController::saveDraft/$1', ['filter' => 'permission:reporting.daily.save-draft']);
    $routes->post('version/(:num)/pull-section', 'DailyReportController::pullSection/$1', ['filter' => 'permission:reporting.daily.pull-section']);
    $routes->post('version/(:num)/images', 'DailyReportController::images/$1', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('version/(:num)/images/upload', 'DailyReportController::uploadImages/$1', ['filter' => 'permission:reporting.daily.save-draft']);
    $routes->post('version/(:num)/images/(:num)/delete', 'DailyReportController::deleteImage/$1/$2', ['filter' => 'permission:reporting.daily.save-draft']);
    $routes->post('version/(:num)/images/(:num)/replace', 'DailyReportController::replaceImage/$1/$2', ['filter' => 'permission:reporting.daily.save-draft']);
    $routes->get('version/(:num)/images/(:num)/view', 'DailyReportController::viewImage/$1/$2', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('version/(:num)/finalize', 'DailyReportController::finalize/$1', ['filter' => 'permission:reporting.daily.finalize']);
    $routes->post('version/(:num)/regenerate', 'DailyReportController::regenerate/$1', ['filter' => 'permission:reporting.daily.regenerate']);
    $routes->post('version/(:num)/delete', 'DailyReportController::deleteVersion/$1', ['filter' => 'permission:reporting.daily.delete-version']);
    $routes->post('report/(:num)/delete-all', 'DailyReportController::deleteAll/$1', ['filter' => 'permission:reporting.daily.delete-all']);
    $routes->get('version/(:num)/pdf', 'DailyReportController::pdf/$1', ['filter' => 'permission:reporting.daily.view']);
    $routes->post('send', 'DailyReportController::send', ['filter' => 'permission:reporting.daily.send']);
});

$routes->group('/reports/progress', ['namespace' => 'App\Controllers\Reports'], static function ($routes) {
    $routes->get('', 'ProgressReportController::index', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('state-token', 'ProgressReportController::stateToken', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('data', 'ProgressReportController::data', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('check-generate', 'ProgressReportController::checkGenerate', ['filter' => 'permission:reporting.progress.generate']);
    $routes->post('generate', 'ProgressReportController::generate', ['filter' => 'permission:reporting.progress.generate']);
    $routes->post('versions', 'ProgressReportController::versions', ['filter' => 'permission:reporting.progress.view']);
    $routes->get('version/(:num)/draft', 'ProgressReportController::draft/$1', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('version/(:num)/save-draft', 'ProgressReportController::saveDraft/$1', ['filter' => 'permission:reporting.progress.save-draft']);
    $routes->post('version/(:num)/instructional-images', 'ProgressReportController::instructionalImages/$1', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('version/(:num)/instructional-images/upload', 'ProgressReportController::uploadInstructionalImages/$1', ['filter' => 'permission:reporting.progress.save-draft']);
    $routes->post('version/(:num)/instructional-images/(:num)/delete', 'ProgressReportController::deleteInstructionalImage/$1/$2', ['filter' => 'permission:reporting.progress.save-draft']);
    $routes->post('version/(:num)/instructional-images/(:num)/replace', 'ProgressReportController::replaceInstructionalImage/$1/$2', ['filter' => 'permission:reporting.progress.save-draft']);
    $routes->get('version/(:num)/instructional-images/(:num)/view', 'ProgressReportController::viewInstructionalImage/$1/$2', ['filter' => 'permission:reporting.progress.view']);
    $routes->post('version/(:num)/pull-section', 'ProgressReportController::pullSection/$1', ['filter' => 'permission:reporting.progress.pull-section']);
    $routes->post('version/(:num)/update-section-state', 'ProgressReportController::updateSectionState/$1', ['filter' => 'permission:reporting.progress.save-draft']);
    $routes->post('version/(:num)/finalize', 'ProgressReportController::finalize/$1', ['filter' => 'permission:reporting.progress.finalize']);
    $routes->post('version/(:num)/regenerate', 'ProgressReportController::regenerate/$1', ['filter' => 'permission:reporting.progress.regenerate']);
    $routes->post('version/(:num)/delete', 'ProgressReportController::deleteVersion/$1', ['filter' => 'permission:reporting.progress.delete-version']);
    $routes->post('report/(:num)/delete-all', 'ProgressReportController::deleteAll/$1', ['filter' => 'permission:reporting.progress.delete-all']);
    $routes->get('version/(:num)/pdf', 'ProgressReportController::pdf/$1', ['filter' => 'permission:reporting.progress.view-pdf']);
});
