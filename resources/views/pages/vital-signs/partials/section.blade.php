<div class="col-md-12 mt-2">
    <div class="row mb-3">
        <div class="col-md-4">
            @can('create', App\Models\VitalSign::class)
                <a href="{{ route('vital-signs.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                    class="btn btn-primary">
                    <i class="bx bx-plus"></i> {{ localize('global.manage_vital_signs') }}
                </a>
            @endcan
        </div>
        <div class="col-md-4 text-center">
            @if($morphModel->vitalSigns->count() > 0)
                <a href="{{ route('vital-signs.print', [$morphableType, $morphableId]) }}"
                    class="btn btn-info" target="_blank">
                    <i class="fas fa-print"></i> {{ localize('global.print_vital_signs_chart') }}
                </a>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('vital-signs.index', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                class="btn btn-outline-primary">
                <i class="bx bx-list-ul"></i> {{ localize('global.view_all_vital_signs') }}
            </a>
        </div>
    </div>

    @if($morphModel->vitalSigns->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ localize('global.id') }}</th>
                        <th>{{ localize('global.vital_sign_type') }}</th>
                        <th>{{ localize('global.created_at') }}</th>
                        <th>{{ localize('global.schedules') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($morphModel->vitalSigns->take(5) as $vitalSign)
                        <tr>
                            <td>{{ $vitalSign->id }}</td>
                            <td>
                                <span class="badge bg-info">{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</span>
                            </td>
                            <td>{{ verta($vitalSign->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $vitalSign->schedules->count() }}
                                    {{ localize('global.schedules') }}</span>
                            </td>
                            <td>
                                @can('update', $vitalSign)
                                    <a href="{{ route('vital-signs.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                                        class="btn btn-warning btn-sm" title="{{ localize('global.edit') }}">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($morphModel->vitalSigns->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('vital-signs.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                        class="btn btn-outline-primary">
                        {{ localize('global.manage_vital_signs') }}
                        ({{ $morphModel->vitalSigns->count() }})
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-4">
            <div class="mb-3">
                <i class="bx bx-heart bx-lg text-muted"></i>
            </div>
            <h5 class="text-muted">{{ localize('global.no_vital_signs_found') }}</h5>
            <p class="text-muted">{{ localize('global.add_first_vital_sign') }}</p>
            @can('create', App\Models\VitalSign::class)
                <a href="{{ route('vital-signs.create', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
                    class="btn btn-primary">
                    <i class="bx bx-plus"></i> {{ localize('global.add_vital_sign') }}
                </a>
            @endcan
        </div>
    @endif
</div>
