<?php

namespace App\Livewire\Wirechat\New;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Wirechat\Wirechat\Livewire\New\Group as BaseGroup;

class Group extends BaseGroup
{
    public function updatedSearch(): void
    {
        if (blank($this->search)) {
            $this->users = [];

            return;
        }

        $this->users = $this->panel()->searchUsers($this->search)->resolve();
    }

    public function toggleMemberByUserId($userId): void
    {
        $this->toggleMember($userId, User::class);
    }

    public function toggleMember($id, string $class)
    {
        $model = $this->resolveChatUser($id, $class);

        if (! $model) {
            return;
        }

        if ($this->selectedMembers->contains(fn ($member) => $member->id == $model->id && $member->getMorphClass() == $model->getMorphClass())) {
            $this->selectedMembers = $this->selectedMembers->reject(
                fn ($member) => $member->id == $model->id && $member->getMorphClass() == $model->getMorphClass()
            );

            return;
        }

        if (count($this->selectedMembers) >= $this->panel()->getMaxGroupMembers()) {
            $this->dispatch('show-member-limit-error');

            return;
        }

        $this->selectedMembers->push($model);
    }

    protected function resolveChatUser($id, string $class): ?Model
    {
        $modelClass = Relation::getMorphedModel($class) ?? $class;

        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        return $modelClass::query()->find($id);
    }
}
