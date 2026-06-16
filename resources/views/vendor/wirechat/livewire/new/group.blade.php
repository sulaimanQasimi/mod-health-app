
<div x-data dusk="new_group_modal">

    <div
        class="relative w-full h-[500px] border overflow-hidden flex flex-col items-center justify-center border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] shadow-xl bg-gradient-to-br from-[var(--wc-light-primary)] to-gray-100 dark:from-[var(--wc-dark-primary)] dark:to-gray-800 dark:text-white sm:max-w-lg sm:rounded-2xl transition-all duration-300">

        {{-- Group Details --}}
        <section x-show="$wire.showAddMembers==false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-x-10 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-x-10 scale-95"
            class="w-full"
        >

            <form wire:submit="validateDetails()" class="flex flex-col gap-2 h-full p-8">

                <header>
                    <div class="flex gap-8 w-full items-center">

                        @if ($photo)
                            <div class="relative w-28 h-28 shadow-lg bg-white dark:bg-gray-950 border-4 border-[var(--wc-accent)] rounded-full flex items-center justify-center hover:scale-105 transition-transform duration-200">
                                <x-wirechat::avatar :src="$photo->temporaryUrl()" class="w-28 h-28 rounded-full object-cover border-none"/>
                                <button
                                    type="button"
                                    class="absolute bottom-2 right-2 bg-gradient-to-r from-red-400 to-red-800 text-white flex items-center justify-center w-8 h-8 rounded-full shadow-md border border-white dark:border-gray-950 hover:scale-110 transition-transform duration-200"
                                    wire:click="deletePhoto"
                                    title="Remove photo"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <label class="cursor-pointer hover:scale-105 transition-transform duration-200">
                                <span class="block bg-gray-200 dark:bg-gray-700 rounded-full w-28 h-28 shadow-inner flex items-center justify-center border-4 border-[var(--wc-accent)]">
                                    <x-wirechat::avatar wire:loading.class="animate-pulse" wire:target="photo" :group="true" class="w-24 h-24" />
                                </span>
                                <input wire:model="photo" dusk="add_photo_field" type="file" hidden accept=".jpg,.jpeg,.png,.webp">
                                <span class="block text-xs mt-1 text-center text-gray-400 dark:text-gray-500">+ Upload</span>
                            </label>
                        @endif

                        <div class="flex-1 ml-4">

                            <label for="name" class="block text-lg font-semibold text-gray-800 dark:text-white mb-1">
                                @lang('wirechat::new.group.inputs.name.label')
                            </label>
                            <input id='name' type="text" wire:model='name' autofocus placeholder="{{__('wirechat::new.group.inputs.name.placeholder') }}"
                                class="wc-input w-full border-0 px-0 bg-white dark:bg-gray-800 dark:text-white text-gray-900 outline-hidden rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--wc-accent)] bg-opacity-70 shadow-sm placeholder-gray-400 font-medium transition-all"
                            />
                            <span class="text-red-500 text-sm font-medium mt-2 block">
                                @error('name')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                    </div>

                    <span class="text-red-500 text-sm font-medium mt-2 block">
                        @error('photo')
                            {{ $message }}
                        @enderror
                    </span>
                </header>

                <main class="mt-6">
                    <div class="flex flex-col gap-y-2">

                        <label for="description" class="text-base font-medium text-gray-700 dark:text-gray-200 mb-1">@lang('wirechat::new.group.inputs.description.label')</label>

                        <textarea id='description' type="text" wire:model='description' placeholder="{{__('wirechat::new.group.inputs.description.placeholder')}}" rows="3"
                            class="wc-textarea w-full resize-none rounded-lg border-2 border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] bg-white/60 dark:bg-gray-800/80 dark:text-white text-gray-900 outline-none focus:border-[var(--wc-accent)] focus:ring-2 focus:ring-[var(--wc-accent)] transition shadow-md placeholder-gray-400 p-3 font-normal"
                        ></textarea>

                        <span class="text-red-500 text-sm font-medium mt-1 block">
                            @error('description')
                                {{ $message }}
                            @enderror
                        </span>

                    </div>
                </main>

                <footer class="flex gap-4 justify-end mt-auto pt-6">
                    <x-wirechat::actions.close-modal>
                        <button type="button"
                            dusk="cancel_create_new_group_button"
                            class="font-semibold cursor-pointer bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700 px-5 py-3 rounded-lg shadow transition-all duration-200"
                        >
                            @lang('wirechat::new.group.actions.cancel.label')
                        </button>
                    </x-wirechat::actions.close-modal>

                    <button type="submit"
                        :disabled="!($wire.name?.trim()?.length)"
                        dusk="next_button"
                        :class="{ 'cursor-not-allowed bg-gray-100 dark:bg-gray-900 text-gray-500 opacity-60': !($wire.name?.trim()?.length) }"
                        class="font-semibold cursor-pointer bg-[var(--wc-accent)] text-white hover:bg-[var(--wc-accent-dark)] px-5 py-3 rounded-lg shadow-lg transition-all duration-200 disabled:opacity-60 disabled:pointer-events-none"
                    >
                        @lang('wirechat::new.group.actions.next.label')
                    </button>
                </footer>
            </form>
        </section>

        {{-- Add members --}}
        <section dusk="add_members_section" x-cloak x-show="$wire.showAddMembers==true"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-10 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-10 scale-95"
            class="px-8 relative h-full w-full overflow-x-hidden"
        >

            <header class="sticky top-0 bg-gradient-to-tr from-[var(--wc-light-primary)] to-gray-100 dark:from-[var(--wc-dark-primary)] dark:to-gray-900 rounded-t-2xl z-20 py-4 shadow-lg mb-3">
                <div class="flex items-center gap-4 pb-2">

                    <button @click="$wire.showAddMembers=false"
                        class="p-2 mr-2 text-gray-600 hover:bg-[var(--wc-accent)] hover:text-white dark:hover:bg-[var(--wc-dark-secondary)] dark:hover:text-white rounded-full transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[var(--wc-accent)]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </button>

                    <h3 class="text-md mx-auto font-extrabold tracking-tight text-gray-800 dark:text-gray-100 select-none">
                        <span class="text-[var(--wc-accent)]">@lang('wirechat::new.group.labels.add_members')</span>
                        <span class="ml-2 font-semibold text-gray-600 dark:text-gray-300">{{count($selectedMembers)}} / {{$maxGroupMembers}}</span>
                    </h3>

                    <button
                        wire:click="create"
                        wire:loading.attr="disabled"
                        wire:target='create'
                        class="ml-2 px-5 py-2 bg-[var(--wc-accent)] text-white font-semibold rounded-lg shadow-lg hover:bg-[var(--wc-accent-dark)] disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-[var(--wc-accent)] transition"
                    >
                        @lang('wirechat::new.group.actions.create.label')
                    </button>
                </div>

                {{-- Member limit error --}}
                <div
                  x-data="{ showError:false }"
                  x-on:show-member-limit-error.window="
                  showError=true;
                  setTimeout(()=>{ showError=false; },1800);
                  "
                 class="text-red-600 text-sm text-center py-2 font-semibold transition-all"
                >
                   <span x-transition.opacity x-show="showError" class="inline-block px-3 py-1 rounded bg-red-100 border border-red-300">
                        @lang('wirechat::new.group.messages.members_limit_error',['count'=>$maxGroupMembers])
                   </span>
                </div>
                {{-- Search input --}}
                <section class="flex items-center mt-2 mb-2 px-2 py-1 bg-gray-50 dark:bg-gray-900 rounded-xl border border-[var(--wc-light-secondary)] dark:border-[var(--wc-dark-secondary)] shadow-sm">
                    <span class="pr-2 text-gray-400 dark:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 16l4 4M21 10a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <input type="search" id="users-search-field" wire:model.live.debounce='search' autocomplete="off"
                        placeholder="{{__('wirechat::new.group.inputs.search.placeholder')}}"
                        class="wc-input w-full border-0 bg-transparent dark:bg-transparent outline-none focus:ring-0 text-gray-800 dark:text-white rounded-lg px-0 font-medium placeholder-gray-400"
                    >
                </section>

                <section class="overflow-x-auto my-2 py-1 hide-scrollbar">
                    <ul
                        style="
                         -ms-overflow-style: none;
                         scrollbar-width: none;
                        "
                        class="flex w-full gap-3"
                    >
                        @if ($selectedMembers)
                            @foreach ($selectedMembers as $key => $member)
                                <li class="flex items-center min-w-fit gap-2 px-3 py-1 rounded-2xl bg-gradient-to-br from-[var(--wc-accent)]/90 to-indigo-400/80 shadow text-white font-semibold text-sm mr-1 hover:scale-105 transition"
                                    wire:key="selected-member-{{ $member->id }}">
                                    <x-wirechat::avatar :src="$member->wirechat_avatar_url ?? null" class="w-6 h-6 rounded-full mr-1 border border-white" />
                                    <span>{{ $member->wirechat_name }}</span>
                                    <button type="button"
                                        wire:click.stop="toggleMemberByUserId({{ (int) $member->id }})"
                                        class="flex items-center p-0 ml-2 bg-transparent rounded-full hover:bg-red-500/70 hover:text-white w-5 h-5 justify-center transition"
                                        aria-label="Remove"
                                        title="Remove"
                                    >
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                        </svg>
                                        <span class="sr-only">Remove badge</span>
                                    </button>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </section>
            </header>

            {{-- Search list --}}
            <div class="relative w-full flex-1 max-h-[calc(100%-210px)] overflow-y-auto custom-scrollbar">
                <section class="my-4">
                    @if (count($users)!=0)
                        <ul class="flex flex-col gap-1">
                            @foreach ($users as $key => $user)
                                <li class="group flex gap-3 items-center p-3 rounded-xl bg-white/70 dark:bg-gray-900/80 hover:bg-[var(--wc-accent)]/10 dark:hover:bg-[var(--wc-accent)]/20 shadow-sm cursor-pointer transition-all"
                                    wire:key="user-list-{{$user['id']}}-{{$user['type']}}"
                                >
                                    <label
                                        wire:click.stop="toggleMemberByUserId({{ (int) $user['id'] }})"
                                        class="flex items-center gap-3 w-full cursor-pointer"
                                    >
                                        <x-wirechat::avatar  src="{{ $user['wirechat_avatar_url'] }}" class="w-10 h-10 rounded-full border-2 border-[var(--wc-accent)] shadow-sm group-hover:scale-105 transition-transform"/>
                                        <span class="text-gray-900 dark:text-white font-semibold truncate group-hover:underline text-base flex-1 transition-all">
                                            {{ $user['wirechat_name'] }}
                                        </span>
                                        <div class="ml-auto flex items-center">
                                            @if ($selectedMembers->contains(fn($member) => $member->id == $user['id'] && $member->getMorphClass() == $user['type']))
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    fill="currentColor"
                                                    class="bi bi-plus-square-fill w-7 h-7 text-[var(--wc-accent)]"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm6.5 4.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3a.5.5 0 0 1 1 0" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    fill="currentColor" class="bi bi-plus-square-dotted w-7 h-7 text-gray-500 group-hover:text-[var(--wc-accent)] transition"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M2.5 0q-.25 0-.487.048l.194.98A1.5 1.5 0 0 1 2.5 1h.458V0zm2.292 0h-.917v1h.917zm1.833 0h-.917v1h.917zm1.833 0h-.916v1h.916zm1.834 0h-.917v1h.917zm1.833 0h-.917v1h.917zM13.5 0h-.458v1h.458q.151 0 .293.029l.194-.981A2.5 2.5 0 0 0 13.5 0m2.079 1.11a2.5 2.5 0 0 0-.69-.689l-.556.831q.248.167.415.415l.83-.556zM1.11.421a2.5 2.5 0 0 0-.689.69l.831.556c.11-.164.251-.305.415-.415zM16 2.5q0-.25-.048-.487l-.98.194q.027.141.028.293v.458h1zM.048 2.013A2.5 2.5 0 0 0 0 2.5v.458h1V2.5q0-.151.029-.293zM0 3.875v.917h1v-.917zm16 .917v-.917h-1v.917zM0 5.708v.917h1v-.917zm16 .917v-.917h-1v.917zM0 7.542v.916h1v-.916zm15 .916h1v-.916h-1zM0 9.375v.917h1v-.917zm16 .917v-.917h-1v.917zm-16 .916v.917h1v-.917zm16 .917v-.917h-1v.917zm-16 .917v.458q0 .25.048.487l.98-.194A1.5 1.5 0 0 1 1 13.5v-.458zm16 .458v-.458h-1v.458q0 .151-.029.293l.981.194Q16 13.75 16 13.5M.421 14.89c.183.272.417.506.69.689l.556-.831a1.5 1.5 0 0 1-.415-.415zm14.469.689c.272-.183.506-.417.689-.69l-.831-.556c-.11.164-.251.305-.415.415l.556.83zm-12.877.373Q2.25 16 2.5 16h.458v-1H2.5q-.151 0-.293-.029zM13.5 16q.25 0 .487-.048l-.194-.98A1.5 1.5 0 0 1 13.5 15h-.458v1zm-9.625 0h.917v-1h-.917zm1.833 0h.917v-1h-.917zm1.834-1v1h.916v-1zm1.833 1h.917v-1h-.917zm1.833 0h.917v-1h-.917zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        @if (!empty($search))
                            <span class="block text-center my-6 text-gray-400 font-semibold">@lang('wirechat::new.group.messages.empty_search_result')</span>
                        @endif
                    @endif
                </section>
            </div>
        </section>

    </div>
</div>
