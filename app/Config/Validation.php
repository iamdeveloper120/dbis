<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

use App\Validation\UserConfiguration\UserRules;

use App\Validation\MasterProgram\MasterGoalRule;
use App\Validation\MasterProgram\MasterTargetRule;

use App\Validation\ClientProgram\ClientDomainRule;
use App\Validation\ClientProgram\ClientGoalRule;
use App\Validation\ClientProgram\ClientTargetRule;

use App\Validation\CustomRules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        UserRules::class,
        MasterGoalRule::class,
        MasterTargetRule::class,
        ClientDomainRule::class,
        ClientGoalRule::class,
        ClientTargetRule::class,
        CustomRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
        'custom_list' => '_errors_list',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

}
