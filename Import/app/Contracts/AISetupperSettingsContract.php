<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Import;



interface AISetupperSettingsContract 
{
    public function setupSettings(Import $import);
}
