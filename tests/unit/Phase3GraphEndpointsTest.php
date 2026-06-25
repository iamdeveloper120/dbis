<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ClientGraphs\DailyDataGraphsModel;
use App\Models\ClientGraphs\MandsGraphsModel;
use App\Models\ClientGraphs\CumulativeGraphsModel;
use App\Models\ClientGraphs\RateGraphsModel;
use App\Models\ClientGraphs\StimulusResponseChainGraphsModel;
use App\Models\ClientGraphs\PhaseLineModel;
use App\Models\ClientGraphs\TargetMonthModel;
use App\Entities\ClientGraphs\PhaseLine;
use App\Entities\ClientGraphs\TargetMonth;
use App\Models\ClientConfiguration\ClientModel;

/**
 * Phase 3: Graph endpoint migration — functional tests.
 *
 * Uses client_id = 1 which is confirmed to have data in the DB.
 */
class Phase3GraphEndpointsTest extends CIUnitTestCase
{
    private int $clientId = 1;

    // -----------------------------------------------------------------------
    // 1. Daily data graph
    // -----------------------------------------------------------------------

    public function testDailyDataModelMethodExists(): void
    {
        $model = new DailyDataGraphsModel();
        $this->assertTrue(method_exists($model, 'get_client_session_data_for_graphs'),
            'DailyDataGraphsModel must have get_client_session_data_for_graphs()');
    }

    public function testDailyDataReturnsArrayWithNoDateFilter(): void
    {
        $model  = new DailyDataGraphsModel();
        $result = $model->get_client_session_data_for_graphs($this->clientId, null, null);
        $this->assertIsArray($result, 'get_client_session_data_for_graphs() must return array');
    }

    public function testDailyDataReturnsArrayWithDateFilter(): void
    {
        $model  = new DailyDataGraphsModel();
        $result = $model->get_client_session_data_for_graphs($this->clientId, '2020-01-01', date('Y-m-d'));
        $this->assertIsArray($result, 'get_client_session_data_for_graphs() with date range must return array');
    }

    // -----------------------------------------------------------------------
    // 2. Mands data graph
    // -----------------------------------------------------------------------

    public function testMandsModelMethodExists(): void
    {
        $model = new MandsGraphsModel();
        $this->assertTrue(method_exists($model, 'getMandsSummaryDataForGraphs'),
            'MandsGraphsModel must have getMandsSummaryDataForGraphs()');
    }

    public function testMandsDataReturnsArray(): void
    {
        $model  = new MandsGraphsModel();
        $result = $model->getMandsSummaryDataForGraphs($this->clientId, null, null);
        $this->assertIsArray($result, 'getMandsSummaryDataForGraphs() must return array');
    }

    // -----------------------------------------------------------------------
    // 3. Cumulative graph data
    // -----------------------------------------------------------------------

    public function testCumulativeDataMethodExists(): void
    {
        $model = new CumulativeGraphsModel();
        $this->assertTrue(method_exists($model, 'get_cumulative_data'),
            'CumulativeGraphsModel must have get_cumulative_data()');
    }

    public function testCumulativeDataReturnsArray(): void
    {
        $model  = new CumulativeGraphsModel();
        $result = $model->get_cumulative_data($this->clientId, null, null);
        $this->assertIsArray($result, 'get_cumulative_data() must return array');
    }

    public function testCumulativeDomainsReturnsArray(): void
    {
        $model  = new CumulativeGraphsModel();
        $result = $model->getDomains($this->clientId);
        $this->assertIsArray($result, 'getDomains() must return array');
    }

    public function testCumulativeDomainGoalsReturnsArray(): void
    {
        $model   = new CumulativeGraphsModel();
        $domains = $model->getDomains($this->clientId);
        if (empty($domains)) {
            $this->markTestSkipped('No domains found for client ' . $this->clientId);
        }
        $first    = (array) $domains[0];
        $domainId = $first['id'] ?? null;
        $this->assertNotNull($domainId, 'Domain must have an id');
        $result = $model->getGoalsByDomain($this->clientId, $domainId);
        $this->assertIsArray($result, 'getGoalsByDomain() must return array');
    }

    public function testCumulativeDomainAndGoalDataMethodExists(): void
    {
        $model = new CumulativeGraphsModel();
        $this->assertTrue(method_exists($model, 'get_cumulative_data_by_domain_and_goal'),
            'CumulativeGraphsModel must have get_cumulative_data_by_domain_and_goal()');
    }

    // -----------------------------------------------------------------------
    // 4. Rate graph data
    // -----------------------------------------------------------------------

    public function testRateModelMethodExists(): void
    {
        $model = new RateGraphsModel();
        $this->assertTrue(method_exists($model, 'get_target_rate_data'),
            'RateGraphsModel must have get_target_rate_data()');
    }

    public function testRateDataSkillsReturnsArray(): void
    {
        $model  = new RateGraphsModel();
        $result = $model->get_target_rate_data($this->clientId, 'Skills');
        $this->assertIsArray($result, 'get_target_rate_data(Skills) must return array');
    }

    public function testRateDataDoiReturnsArray(): void
    {
        $model  = new RateGraphsModel();
        $result = $model->get_target_rate_data($this->clientId, 'DOI');
        $this->assertIsArray($result, 'get_target_rate_data(DOI) must return array');
    }

    // -----------------------------------------------------------------------
    // 5. Phase line CRUD (Cumulative)
    // -----------------------------------------------------------------------

    public function testPhaseLineListReturnsArray(): void
    {
        $model  = new PhaseLineModel();
        $result = $model->list($this->clientId, 'Cumulative');
        $this->assertIsArray($result, 'PhaseLineModel::list() must return array');
    }

    public function testPhaseLineCreateAndDelete(): void
    {
        $model  = new PhaseLineModel();
        $entity = new PhaseLine();
        $entity->fill([
            'p_date'    => date('Y-m-d'),
            'client_id' => $this->clientId,
            'graph_type'=> 'Cumulative',
            'p_key'     => '__TEST__',
            'created_by'=> 1,
        ]);
        $this->assertTrue($model->save($entity), 'PhaseLineModel::save() must return true');
        $insertId = $model->getInsertID();
        $this->assertGreaterThan(0, $insertId, 'Insert ID must be > 0 after save');

        $row = $model->single($insertId);
        $this->assertNotNull($row, 'PhaseLineModel::single() must return the saved row');
        $this->assertEquals('__TEST__', $row->p_key, 'p_key must match what was saved');

        $deleted = $model->delete($insertId);
        $this->assertTrue((bool)$deleted, 'PhaseLineModel::delete() must succeed');
        $this->assertNull($model->find($insertId), 'Row must not exist after delete');
    }

    public function testPhaseLineListRateReturnsArray(): void
    {
        $model  = new PhaseLineModel();
        $result = $model->list($this->clientId, 'Target_Rate');
        $this->assertIsArray($result, 'PhaseLineModel::list(Target_Rate) must return array');
    }

    // -----------------------------------------------------------------------
    // 6. Target months CRUD
    // -----------------------------------------------------------------------

    public function testTargetMonthListReturnsArray(): void
    {
        $model  = new TargetMonthModel();
        $result = $model->list($this->clientId);
        $this->assertIsArray($result, 'TargetMonthModel::list() must return array');
    }

    public function testTargetMonthCreateAndDelete(): void
    {
        $model  = new TargetMonthModel();
        $entity = new TargetMonth();
        $entity->fill([
            't_date'    => '2099-01-01',
            'client_id' => $this->clientId,
            'graph_type'=> 'Skills',
            'created_by'=> 1,
        ]);
        $this->assertTrue($model->save($entity), 'TargetMonthModel::save() must return true');
        $insertId = $model->getInsertID();
        $this->assertGreaterThan(0, $insertId, 'Insert ID must be > 0 after save');

        $row = $model->single($insertId);
        $this->assertNotNull($row, 'TargetMonthModel::single() must return the saved row');
        $this->assertEquals('Skills', $row->graph_type, 'graph_type must match');

        $deleted = $model->delete($insertId);
        $this->assertTrue((bool)$deleted, 'TargetMonthModel::delete() must succeed');
        $this->assertNull($model->find($insertId), 'Row must not exist after delete');
    }

    // -----------------------------------------------------------------------
    // 7. Stimulus Response Chain
    // -----------------------------------------------------------------------

    public function testSrcDomainsReturnsArray(): void
    {
        $model  = new StimulusResponseChainGraphsModel();
        $result = $model->getClientDomains($this->clientId);
        $this->assertIsArray($result, 'getClientDomains() must return array');
    }

    public function testSrcDomainGoalsReturnsArray(): void
    {
        $model   = new StimulusResponseChainGraphsModel();
        $domains = $model->getClientDomains($this->clientId);
        if (empty($domains)) {
            $this->markTestSkipped('No domains found for SRC client ' . $this->clientId);
        }
        $first    = (array) $domains[0];
        $domainId = $first['domain_id'] ?? $first['id'] ?? array_values($first)[0] ?? null;
        $result   = $model->getClientDomainGoals($this->clientId, (int)$domainId);
        $this->assertIsArray($result, 'getClientDomainGoals() must return array');
    }

    public function testSrcGetGraphsDataMethodExists(): void
    {
        $model = new StimulusResponseChainGraphsModel();
        $this->assertTrue(method_exists($model, 'getGraphsData'),
            'StimulusResponseChainGraphsModel must have getGraphsData()');
    }

    // -----------------------------------------------------------------------
    // 8. Client model helpers used in controller
    // -----------------------------------------------------------------------

    public function testClientModelGetById(): void
    {
        $model  = new ClientModel();
        $client = $model->getClientById($this->clientId);
        $this->assertNotNull($client, 'getClientById() must return a client entity for id=' . $this->clientId);
    }

    public function testClientModelActiveProgram(): void
    {
        $model  = new ClientModel();
        $result = $model->clientActiveProgram($this->clientId);
        // May be null if no active program — just ensure it doesn't throw
        $this->assertTrue(true, 'clientActiveProgram() must not throw');
    }

    // -----------------------------------------------------------------------
    // 9. encodeValue / decodeValue round-trip
    // -----------------------------------------------------------------------

    public function testEncodeDecodeValue(): void
    {
        $encoded = encodeValue($this->clientId);
        $this->assertNotEmpty($encoded, 'encodeValue() must not be empty');
        $decoded = decodeValue($encoded);
        $this->assertEquals($this->clientId, (int)$decoded, 'decodeValue(encodeValue(id)) must equal original id');
    }
}
