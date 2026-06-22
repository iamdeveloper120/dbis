<?php

namespace App\Validation\MasterProgram;

use App\Models\MasterProgram\MasterTargetModel;

class MasterTargetRule
{
    public static function is_master_target_name_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new MasterTargetModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'name' => $data['name'],
            'goal_id' => $data['goal_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
}
