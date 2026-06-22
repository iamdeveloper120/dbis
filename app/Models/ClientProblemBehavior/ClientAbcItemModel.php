<?php

namespace App\Models\ClientProblemBehavior;

use CodeIgniter\Model;

class ClientAbcItemModel extends Model
{
    protected $table = 'client_abc_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'client_id',
        'category',
        'value',
        'order',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getClientValuesByCategory(int $clientId, string $category): array
    {
        $rows = $this->select('value')
            ->where('client_id', $clientId)
            ->where('category', $category)
            ->orderBy('`order`', 'ASC', false)
            ->orderBy('id', 'ASC')
            ->findAll();

        return array_values(array_filter(array_map(static function ($row) {
            return trim((string) ($row['value'] ?? ''));
        }, $rows), static fn($v) => $v !== ''));
    }

    public function getResolvedValues(int $clientId, string $category): array
    {
        $clientValues = $this->getClientValuesByCategory($clientId, $category);
        if (!empty($clientValues)) {
            return $clientValues;
        }

        $masterModel = new MasterAbcItemModel();
        $masterRows = $masterModel->select('value')
            ->where('category', $category)
            ->orderBy('value', 'ASC')
            ->findAll();

        return array_values(array_filter(array_map(static function ($row) {
            return trim((string) ($row['value'] ?? ''));
        }, $masterRows), static fn($v) => $v !== ''));
    }

    public function replaceClientCategoryValues(int $clientId, string $category, array $values, int $userId): void
    {
        $this->where('client_id', $clientId)->where('category', $category)->delete();

        if (empty($values)) {
            return;
        }

        $rows = [];
        foreach (array_values($values) as $index => $value) {
            $rows[] = [
                'client_id' => $clientId,
                'category' => $category,
                'value' => $value,
                'order' => $index + 1,
                'created_by' => $userId,
                'updated_by' => null,
            ];
        }

        $this->insertBatch($rows);
    }
}
