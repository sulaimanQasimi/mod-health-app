<?php

namespace App\Livewire\Wirechat\New;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Wirechat\Wirechat\Livewire\New\Chat as BaseChat;
use Wirechat\Wirechat\Livewire\Widgets\Wirechat as WidgetsWirechat;

class Chat extends BaseChat
{
    public function updatedSearch(): void
    {
        if (blank($this->search)) {
            $this->users = [];

            return;
        }

        $this->users = $this->panel()->searchUsers($this->search)->resolve();
    }

    public function startChatWithUser($userId): void
    {
        $this->createConversation($userId, User::class);
    }

    public function createConversation($id, string $class)
    {
        $model = $this->resolveChatUser($id, $class);

        if (! $model) {
            return;
        }

        $createdConversation = auth()->user()->createConversationWith($model);

        if (! $createdConversation) {
            return;
        }

        $this->closeWirechatModal();

        $this->handleComponentTermination(
            redirectRoute: $this->panel()->chatRoute($createdConversation->id),
            events: [
                WidgetsWirechat::class => ['open-chat', ['conversation' => $createdConversation->id]],
            ],
        );
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
