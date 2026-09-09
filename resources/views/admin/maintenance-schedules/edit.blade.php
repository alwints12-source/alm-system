<x-prototype-layout title="Edit maintenance schedule">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Edit maintenance schedule</div>
        </div>
    </div>

    <div class="card" style="max-width:600px">
        <form method="POST" action="{{ route('admin.maintenance-schedules.update', $schedule) }}">
            @csrf
            @method('PATCH')
            @include('admin.maintenance-schedules._form')

            <div class="fa">
                <button type="submit" class="btn pri sm">Save changes</button>
                <a href="{{ route('admin.maintenance-schedules.index') }}" class="btn sm">Cancel</a>
            </div>
        </form>
    </div>

</x-prototype-layout>
