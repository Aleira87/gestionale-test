<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $roleToAssign = null;

    protected ?int $companyIdToAssign = null;

    protected array $managedCompanyIdsToAssign = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? null;
        $this->companyIdToAssign = isset($data['company_id'])
            ? (int) $data['company_id']
            : null;

        $this->managedCompanyIdsToAssign = array_map(
            'intval',
            $data['managed_company_ids'] ?? []
        );

        unset(
            $data['role'],
            $data['company_id'],
            $data['managed_company_ids']
        );

        // L'azienda dell'employee viene assegnata esplicitamente dopo la creazione.
        unset($data['company_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->roleToAssign === null) {
            return;
        }

        $this->record->assignRole($this->roleToAssign);

        if ($this->roleToAssign === 'employee') {
            $this->record->company()->associate($this->companyIdToAssign);
            $this->record->save();
        }

        if ($this->roleToAssign === 'company_admin') {
            $this->record->managedCompanies()->sync(
                $this->managedCompanyIdsToAssign
            );
        }
    }
}
