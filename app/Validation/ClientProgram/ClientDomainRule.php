<?php

namespace App\Validation\ClientProgram;

use App\Models\ClientProgram\ClientDomainModel;

class ClientDomainRule
{
    public static function is_client_domain_code_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new ClientDomainModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'domain_code' => $data['domain_code'], 
            'client_id' => $data['client_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
    public static function is_client_domain_name_unique(string $str, string $fields, array $data, string &$error = null): bool
    {
        [$table, $field, $id] = explode(',', $fields);

        $model = new ClientDomainModel(); // Replace YourModel with your actual model name
        $existingRecord = $model->where([
            'name' => $data['name'], 
            'client_id' => $data['client_id'],
        ])->first();

        if ($existingRecord && $existingRecord->{$field} != $id) {
            return false;
        }

        return true;
    }
}
