<?php

namespace App\Providers\Wirechat;

use App\Models\User;
use Illuminate\Support\Collection;
use Wirechat\Wirechat\Panel;
use Wirechat\Wirechat\PanelProvider;

class ChatsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('chats')
            ->path('chats')
            ->middleware(['web', 'auth'])
            ->heading('Chats')
            ->chatsSearch()
            ->createChatAction()
            ->createGroupAction()
            ->attachments()
            ->mediaMimes(['png', 'jpg', 'jpeg', 'gif', 'mov', 'mp4', 'webm', 'ogg', 'mp3', 'wav', 'm4a'])
            ->mediaMaxUploadSize(8192)
            ->searchableAttributes(['name', 'last_name', 'email'])
            ->searchUsersUsing(function (?string $needle): Collection {
                $needle = trim((string) $needle);

                if ($needle === '') {
                    return collect();
                }

                $authId = auth()->id();

                return User::query()
                    ->when($authId, fn ($query) => $query->whereKeyNot($authId))
                    ->where(function ($query) use ($needle) {
                        $query->where('name', 'like', "%{$needle}%")
                            ->orWhere('last_name', 'like', "%{$needle}%")
                            ->orWhere('email', 'like', "%{$needle}%")
                            ->orWhereRaw("CONCAT(name, ' ', COALESCE(last_name, '')) LIKE ?", ["%{$needle}%"]);
                    })
                    ->orderBy('name')
                    ->orderBy('last_name')
                    ->limit(50)
                    ->get();
            })
            ->redirectToHomeAction(url: url('/'))
            ->messagesQueue('messages')
            ->eventsQueue('default')
            ->default();
    }
}
