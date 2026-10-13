<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $roleToAssign = null;

    protected ?int $companyIdToAssign = null;

    protected array $managedCompanyIdsToAssign = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->getRoleNames()->first() ?? 'user';

        $data['managed_company_ids'] = $this->record
            ->managedCompanies()
            ->pluck('companies.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->roleToAssign === null) {
            return;
        }

        $this->record->syncRoles([$this->roleToAssign]);

        if ($this->roleToAssign === 'employee') {
            $this->record->company_id = $this->companyIdToAssign;
            $this->record->save();

            $this->record->managedCompanies()->detach();

            return;
        }

        $this->record->company_id = null;
        $this->record->save();

        if ($this->roleToAssign === 'company_admin') {
            $this->record->managedCompanies()->sync(
                $this->managedCompanyIdsToAssign
            );

            return;
        }

        $this->record->managedCompanies()->detach();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
