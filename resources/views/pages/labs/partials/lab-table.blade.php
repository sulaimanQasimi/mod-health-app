<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.lab_type')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.patient_id')}}</th>
            <th>{{localize('global.doctor')}}</th>
            <th>{{localize('global.section')}}</th>
            <th>{{localize('global.date')}}</th>
            <th>{{localize('global.status')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($labs as $lab)
            <tr>
                <td>{{ $loop->iteration + ($labs->currentPage() - 1) * $labs->perPage() }}</td>
                <td>{{ $lab->labType->name }}</td>
                <td>{{ $lab->patient->name }}</td>
                <td>{{ $lab->patient->id_card ?? '-' }}</td>
                <td>{{ $lab->doctor->name ?? '-' }}</td>
                <td>
                    @if($lab->labTypeSection && $lab->labTypeSection->relatedSection)
                        <span class="badge bg-info">{{ $lab->labTypeSection->relatedSection->name }}</span>
                    @else
                        <span class="badge bg-light text-dark">{{ localize('global.no_section') }}</span>
                    @endif
                </td>
                <td>{{ \Hekmatinasser\Verta\Verta::instance($lab->created_at)->format('Y/n/j') }}</td>
                <td>
                    @if($lab->status)
                        <span class="badge bg-success">{{ localize('global.completed') }}</span>
                    @else
                        <span class="badge bg-warning">{{ localize('global.pending') }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('lab_tests.edit', $lab) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-expand"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        {{ localize('global.no_lab_tests_found') }}
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="col-md-12 mt-4 mb-4">
    {{$labs->links('pagination::bootstrap-4')}}
</div>
