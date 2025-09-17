<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-pills p-1"></i>{{ localize('global.medication_administration_records') }}
        ({{ localize('global.mar') }})
    </h5>
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('medication-administration-records.print', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
            class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> {{ localize('global.print_mars') }}
        </a>
        @can('create', App\Models\MedicationAdministrationRecord::class)
            <a href="{{ route('medication-administration-records.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                class="btn btn-success">
                <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
            </a>
        @endcan
    </div>

    @if($medicationAdministrationRecords->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ localize('global.mar_id') }}</th>
                        <th>{{ localize('global.medicine') }}</th>
                        <th>{{ localize('global.nurse') }}</th>
                        <th>{{ localize('global.order_date') }}</th>
                        <th>{{ localize('global.signature_date') }}</th>
                        <th>{{ localize('global.administration_times') }}</th>
                        <th>{{ localize('global.mar_created_by') }}</th>
                        <th>{{ localize('global.mar_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicationAdministrationRecords as $mar)
                        <tr>
                            <td>{{ $mar->id }}</td>
                            <td>
                                <strong>{{ $mar->medicine->name ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $mar->nurse->full_name ?? 'N/A' }}</td>
                            <td>
                                @if($mar->order_date)
                                    <span class="badge bg-info">{{ $mar->order_date->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($mar->date_signature)
                                    <span class="badge bg-success">{{ $mar->date_signature->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($mar->administrationTimes->count() > 0)
                                    <span class="badge badge-info">
                                        {{ $mar->administrationTimes->count() }}
                                        {{ localize('global.times_count') }}
                                    </span>
                                    <br>
                                    <small>
                                        @foreach($mar->administrationTimes as $time)
                                            {{ $time->formatted_time }}@if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">{{ localize('global.no_times_recorded') }}</span>
                                @endif
                            </td>
                            <td>{{ $mar->createdBy->name ?? 'System' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view', $mar)
                                        <a href="{{ route('medication-administration-records.show', $mar) }}"
                                            class="btn btn-sm btn-info"
                                            title="{{ localize('global.mar_view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $mar)
                                        <a href="{{ route('medication-administration-records.edit', $mar) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ localize('global.mar_edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $mar)
                                        <form action="{{ route('medication-administration-records.destroy', $mar) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('{{ localize('global.mar_confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                title="{{ localize('global.mar_delete') }}">
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
                <i class="bx bx-pills bx-lg text-muted"></i>
            </div>
            <h5 class="text-muted">{{ localize('global.no_mars_found') }}</h5>
            <p class="text-muted">{{ localize('global.add_first_mar') }}</p>
            @can('create', App\Models\MedicationAdministrationRecord::class)
                <a href="{{ route('medication-administration-records.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                    class="btn btn-primary">
                    <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                </a>
            @endcan
        </div>
    @endif
</div>
