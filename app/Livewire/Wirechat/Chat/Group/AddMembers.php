<?php

namespace App\Livewire\Wirechat\Chat\Group;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Wirechat\Wirechat\Livewire\Chat\Group\AddMembers as BaseAddMembers;

class AddMembers extends BaseAddMembers
{
    public function updatedSearch(): void
    {
        if (blank($this->search)) {
            $this->users = null;

            return;
        }

        $this->users = collect($this->panel()->searchUsers($this->search)->collection)
            ->map(function ($resource) {
                $model = $resource->resource;

                return [
                    'id' => $model->id,
                    'type' => $model->getMorphClass(),
                    'wirechat_name' => $model->wirechat_name,
                    'wirechat_avatar_url' => $model->wirechat_avatar_url,
                    'belongsToConversation' => $model->belongsToConversation($this->conversation),
                ];
            });
    }

    public function toggleMember($id, string $class)
    {
        $model = $this->resolveChatUser($id, $class);

        if (! $model) {
            return;
        }

        abort_if($model->belongsToConversation($this->conversation), 403, $model->wirechat_name.' Is already a member');

        if ($this->selectedMembers->contains(fn ($member) => $member->id == $model->id && $member->getMorphClass() == $model->getMorphClass())) {
            $this->selectedMembers = $this->selectedMembers->reject(
                fn ($member) => $member->id == $model->id && $member->getMorphClass() == $model->getMorphClass()
            );
        } else {
            if ($this->newTotalCount >= $this->panel()->getMaxGroupMembers()) {
                $this->dispatch('show-member-limit-error');

                return;
            }

            $participant = $this->conversation->participant($model, withoutGlobalScopes: true);

            abort_if($participant?->hasExited(), 403, 'Cannot add '.$model->wirechat_name.' because they left the group');

            if ($participant?->isRemovedByAdmin()) {
                $authParticipant = $this->conversation->participant(auth()->user());

                abort_unless($authParticipant?->isAdmin(), 403, 'Cannot add '.$model->wirechat_name.' because they were removed from the group by an Admin.');
            }

            $this->selectedMembers->push($model);
        }

        $this->newTotalCount = count($this->selectedMembers) + $this->exitingMembersCount;
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
