<?php

namespace App\Filament\Plugins;

use Filament\Panel;
use daacreators\CreatorsTicketing\TicketingPlugin;

use daacreators\CreatorsTicketing\Filament\Resources\Forms\FormResource;
use daacreators\CreatorsTicketing\Filament\Resources\Departments\DepartmentResource;
use daacreators\CreatorsTicketing\Filament\Resources\TicketStatuses\TicketStatusResource;
use daacreators\CreatorsTicketing\Filament\Resources\AutomationRules\AutomationRuleResource;
use daacreators\CreatorsTicketing\Filament\Resources\SpamFilters\SpamFilterResource;
use daacreators\CreatorsTicketing\Filament\Resources\SpamLogs\SpamLogResource;

class CustomTicketingPlugin extends TicketingPlugin
{
    public function register(Panel $panel): void
    {
        $panel->resources([
            FormResource::class,
            DepartmentResource::class,
            TicketStatusResource::class,
            AutomationRuleResource::class,
            SpamFilterResource::class,
            SpamLogResource::class,
        ]);
    }
}
