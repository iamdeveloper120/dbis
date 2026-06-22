<?php

namespace App\Validation\ClientProgram;

use App\Models\ClientProgram\ClientTargetModel;

class ClientTargetRule
{
    public static function is_client_target_name_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new ClientTargetModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'name' => $data['name'],
            'goal_id' => $data['goal_id'],
            'client_id' => $data['client_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
}
