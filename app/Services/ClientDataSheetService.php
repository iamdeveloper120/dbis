<?php

namespace App\Services;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientDataSheet\ClientDataSheetModel;
use App\Models\MasterProgram\TargetPhaseModel;
use App\Models\MasterProgram\TargetProbeSetModel;
use App\Models\ClientProgram\ClientDomainModel;
use App\Models\Mands\MandsSessionDataModel;
use App\Models\Mands\ClientMandsReinforcerModel;
use App\Models\Mands\ClientMandsReinforcerMediaModel;
use App\Models\ClientProblemBehavior\DailySessionsPbRecordsModel;
use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientSessions\StimulusStepSessionsDataModel;
use App\Models\ClientProgram\ClientStimulusStepModel;
use App\Models\ClientProgram\ClientStimulusChainModel;
use App\Entities\ClientConfiguration\Client;

class ClientDataSheetService
{
    protected $clientModel;
    protected $clientDataSheetModel;
    protected $targetPhaseModel;
    protected $targetProbeSetModel;
    protected $clientDomainModel;
    protected $mandsSessionDataModel;
    protected $clientMandsReinforcerModel;
    protected $clientMandsReinforcerMediaModel;
    protected $pbRecordsModel;
    protected $collectionModel;
    protected $stepSessionModel;
    protected $stepModel;
    protected $stimulusChainModel;

    protected ?array $phasesCache = null;
    protected ?array $probeSetsCache = null;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->clientDomainModel = new ClientDomainModel();
        $this->targetPhaseModel = new TargetPhaseModel();
        $this->targetProbeSetModel = new TargetProbeSetModel();
        $this->clientDataSheetModel = new ClientDataSheetModel();
        $this->mandsSessionDataModel = new MandsSessionDataModel();
        $this->clientMandsReinforcerModel = new ClientMandsReinforcerModel();
        $this->clientMandsReinforcerMediaModel = new ClientMandsReinforcerMediaModel();
        $this->pbRecordsModel = new DailySessionsPbRecordsModel();
        $this->collectionModel = new DailySessionDataCollectionModel();
        $this->stepSessionModel = new StimulusStepSessionsDataModel();
        $this->stepModel = new ClientStimulusStepModel();
        $this->stimulusChainModel = new ClientStimulusChainModel();
    }

    protected function normalizeFilter(?string $val): ?string
    {
        return ($val === '') ? null : $val;
    }

    public function getClient(int $client_id): ?Client
    {
        return $this->clientModel->getClientById($client_id);
    }

    public function getFilterBaseData(int $client_id): array
    {
        return [
            'client'     => $this->getClient($client_id),
            'domains'    => $this->clientDataSheetModel->getDomains($client_id),
            'probeSets'  => $this->getCachedProbeSets(),
        ];
    }

    public function fetchGoalsForDomain($client_id, $domain_id)
    {
        return $this->clientDataSheetModel->getGoalsByDomain($client_id, $domain_id);
    }

    public function getInitialProgramData($client_id)
    {
        return array_merge($this->getFilterBaseData($client_id), [
            'clientProgramData' => $this->clientDataSheetModel->getDataSheetInformation($client_id, null),
            'phases' => $this->getCachedPhases(),
        ]);
    }

    public function getFilteredProgramData(int $client_id, ?string $probeSet, $domain_id = null, $goal_id = null): array
    {
        $domain_id = $this->normalizeFilter($domain_id);
        $goal_id   = $this->normalizeFilter($goal_id);

        $clientProbeSetIds = ($probeSet !== '')
            ? $this->clientDataSheetModel->getClientProbeSets($client_id, $probeSet)
            : null;

        $clientProgramData = [];
        if ($clientProbeSetIds !== []) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds, $domain_id, $goal_id);
        }

        return [
            'client' => $this->getClient($client_id),
            'phases' => $this->getCachedPhases(),
            'clientProgramData' => $clientProgramData,
        ];
    }

    public function getTransitionList($collection_id)
    {
        $sessionData = $this->collectionModel->getSingle($collection_id);
        $collectedData = json_decode($sessionData->collected_data, true);
        return ['transitions' => $collectedData['transitions'] ?? []];
    }

    public function getStimulusTargetStepMatrix($target_id)
    {
        $steps = $this->stepModel->where('target_id', $target_id)->orderBy('step_number')->findAll();
        $collections = $this->collectionModel
            ->where('target_id', $target_id)
            ->where('is_processed', 1)
            ->orderBy('session_date', 'ASC')
            ->findAll();

        $sessionDates = [];
        foreach ($collections as $col) {
            $date = $col->session_date;
            if (!isset($sessionDates[$date])) $sessionDates[$date] = [];
            $sessionDates[$date][] = $col->id;
        }

        $collectionIds = array_merge(...array_values($sessionDates));
        $stepInputs = [];
        if (!empty($collectionIds)) {
            $stepInputs = $this->stepSessionModel
                ->where('target_id', $target_id)
                ->whereIn('collection_id', $collectionIds)
                ->findAll();
        }

        $matrix = [];
        foreach ($stepInputs as $input) {
            $stepId = $input['step_id'];
            $date = $input['session_date'];
            $val = $input['input_result'] ?? '';

            $matrix[$stepId][$date][] = $val;
        }

        $chain = $this->stimulusChainModel->where('target_id', $target_id)->first();
        $chainLabel = null;
        if ($chain) {
            $labelText = match ($chain->method) {
                'backward' => 'Backward Chain',
                'forward' => 'Forward Chain',
                'total_task' => 'Total Task Chain',
                default => ucfirst($chain->method) . ' Chain'
            };

            $badgeClass = match ($chain->method) {
                'backward', 'forward', 'total_task' => 'info',
                default => 'secondary'
            };

            $chainLabel = "<span class='badge bg-$badgeClass'>$labelText</span>";
        }

        return [
            'steps' => $steps,
            'matrix' => $matrix,
            'sessionDates' => array_keys($sessionDates),
            'chainLabel' => $chainLabel,
            'chain' => $chain,
        ];
    }

    public function getMandsSummary($client_id)
    {
        return [
            'client' => $this->getClient($client_id),
            'mandsSummaryData' => $this->mandsSessionDataModel->getSummaryData($client_id),
        ];
    }

    public function getMandsDaily($client_id, $session_date)
    {
        return ['mandsData' => $this->mandsSessionDataModel->getDailyData($client_id, $session_date)];
    }

    public function getCurrentMandList($client_id)
    {
        $reinforcers = $this->clientMandsReinforcerModel->listByClient((int) $client_id);

        $reinforcerIds = [];
        foreach ($reinforcers as $reinforcer) {
            $reinforcerIds[] = (int) ($reinforcer->id ?? 0);
        }

        $mediaRows = $this->clientMandsReinforcerMediaModel->listByClientReinforcerIds($reinforcerIds);
        $mediaByReinforcer = [];
        foreach ($mediaRows as $media) {
            $parentId = (int) ($media->client_reinforcer_id ?? 0);
            if ($parentId <= 0) {
                continue;
            }

            if (!isset($mediaByReinforcer[$parentId])) {
                $mediaByReinforcer[$parentId] = [
                    'images' => [],
                    'videos' => [],
                ];
            }

            $row = [
                'id' => (int) ($media->id ?? 0),
                'media_type' => (string) ($media->media_type ?? ''),
                'media_path' => (string) ($media->media_path ?? ''),
            ];

            if (($row['media_type'] ?? '') === 'video') {
                $mediaByReinforcer[$parentId]['videos'][] = $row;
            } else {
                $mediaByReinforcer[$parentId]['images'][] = $row;
            }
        }

        $list = [];
        foreach ($reinforcers as $reinforcer) {
            $id = (int) ($reinforcer->id ?? 0);
            $mediaSet = $mediaByReinforcer[$id] ?? ['images' => [], 'videos' => []];

            $list[] = [
                'id' => $id,
                'client_id' => (int) ($reinforcer->client_id ?? 0),
                'reinforcer_name' => (string) ($reinforcer->reinforcer_name ?? ''),
                'introduced_at' => (string) ($reinforcer->introduced_at ?? ''),
                'introduced_at_display' => !empty($reinforcer->introduced_at) ? app_date((string) $reinforcer->introduced_at) : '',
                'vocal_sign' => (string) ($reinforcer->vocal_sign ?? ''),
                'description' => (string) ($reinforcer->description ?? ''),
                'images' => $mediaSet['images'],
                'videos' => $mediaSet['videos'],
                'image_count' => count($mediaSet['images']),
                'video_count' => count($mediaSet['videos']),
            ];
        }

        return [
            'client' => $this->getClient($client_id),
            'currentMandListData' => $list,
        ];
    }

    public function getPbData($client_id)
    {
        return [
            'client' => $this->getClient($client_id),
            'pbDailyData' => $this->pbRecordsModel->getCompleteRecordSet($client_id),
        ];
    }

    // ----------------------
    // 🔍 Filtering Methods
    // ----------------------
    public function filterSkillsRetained($client_id, $domain_id, $goal_id, $probe_set_id)
    {
        return $this->clientDataSheetModel->getSkillsRetained($client_id, $domain_id, $goal_id, $probe_set_id);
    }

    public function filterDOITargets($client_id, $domain_id, $goal_id, $probe_set_id)
    {
        return $this->clientDataSheetModel->getDOITargets($client_id, $domain_id, $goal_id, $probe_set_id);
    }

    public function filterProgramChange($client_id, $domain_id, $goal_id, $probe_set_id)
    {
        return $this->clientDataSheetModel->getProgramChangeData($client_id, $domain_id, $goal_id, $probe_set_id);
    }

    // ----------------------
    // 🔄 Cached Helpers
    // ----------------------
    protected function getCachedPhases(): array
    {
        if ($this->phasesCache === null) {
            $this->phasesCache = $this->clientDataSheetModel->getTargetPhasesArray();
        }
        return $this->phasesCache;
    }

    protected function getCachedProbeSets(): array
    {
        if ($this->probeSetsCache === null) {
            $this->probeSetsCache = $this->targetProbeSetModel->findAll();
        }
        return $this->probeSetsCache;
    }
}
