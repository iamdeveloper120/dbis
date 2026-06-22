<?php

namespace App\Validation\UserConfiguration;

use CodeIgniter\Shield\Models\UserIdentityModel;

class UserRules
{
    /**
     * Checks a user has a unique email within all of the email_password identities.
     */

    public function unique_email(string $value, string $fields, array $data): bool
    {
        return model(UserIdentityModel::class)
            ->where('user_id !=', $fields)
            ->where('type', 'email_password')
            ->where('secret', $value)
            ->countAllResults() === 0;
    }
}
