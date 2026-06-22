<?php

namespace App\Validation\ClientProgram;

use App\Models\ClientProgram\ClientGoalModel;

class ClientGoalRule
{
    public static function is_client_goal_code_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new ClientGoalModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'goal_code' => $data['goal_code'],
            'domain_id' => $data['domain_id'],
            'client_id' => $data['client_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }

    public static function is_client_goal_name_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new ClientGoalModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'name' => $data['name'],
            'domain_id' => $data['domain_id'],
            'client_id' => $data['client_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
}
