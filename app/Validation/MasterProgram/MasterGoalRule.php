<?php

namespace App\Validation\MasterProgram;

use App\Models\MasterProgram\MasterGoalModel;

class MasterGoalRule
{
    public static function is_master_goal_code_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new MasterGoalModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'goal_code' => $data['goal_code'],
            'domain_id' => $data['domain_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }

    public static function is_master_goal_name_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new MasterGoalModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'name' => $data['name'],
            'domain_id' => $data['domain_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
}
