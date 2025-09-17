<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-note p-1"></i>{{ localize('global.nurse_notes') }}
    </h5>
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('nurse-notes.print', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
            class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> {{ localize('global.print_notes') }}
        </a>
        @can('create', App\Models\NurseNote::class)
            <a href="{{ route('nurse-notes.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                class="btn btn-success">
                <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
            </a>
        @endcan
    </div>

    @if($nurseNotes->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ localize('global.date') }}</th>
                        <th>{{ localize('global.nurse') }}</th>
                        <th>{{ localize('global.am_time') }}</th>
                        <th>{{ localize('global.pm_time') }}</th>
                        <th>{{ localize('global.note') }}</th>
                        <th>{{ localize('global.created_by') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nurseNotes as $note)
                        <tr>
                            <td>{{ $note->id }}</td>
                            <td>
                                @if($note->date)
                                    <span class="badge bg-info">{{ $note->date->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($note->nurse)
                                    <span class="badge bg-primary">{{ $note->nurse->full_name }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($note->time_am)
                                    <span
                                        class="badge bg-primary">{{ $note->time_am->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($note->time_pm)
                                    <span
                                        class="badge bg-primary">{{ $note->time_pm->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($note->note)
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                        title="{{ $note->note }}">
                                        {{ Str::limit($note->note, 50) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($note->createdBy)
                                    <span class="badge bg-secondary">{{ $note->createdBy->name }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view', $note)
                                        <a href="{{ route('nurse-notes.show', $note) }}"
                                            class="btn btn-sm btn-info"
                                            title="{{ localize('global.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $note)
                                        <a href="{{ route('nurse-notes.edit', $note) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ localize('global.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $note)
                                        <form action="{{ route('nurse-notes.destroy', $note) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                title="{{ localize('global.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <div class="mb-3">
                <i class="bx bx-note bx-lg text-muted"></i>
            </div>
            <h5 class="text-muted">{{ localize('global.no_nurse_notes_found') }}</h5>
            <p class="text-muted">{{ localize('global.add_first_nurse_note') }}</p>
            @can('create', App\Models\NurseNote::class)
                <a href="{{ route('nurse-notes.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                    class="btn btn-primary">
                    <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
                </a>
            @endcan
        </div>
    @endif
</div>