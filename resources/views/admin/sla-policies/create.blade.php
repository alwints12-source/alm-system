<x-prototype-layout title="New SLA policy">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">New SLA policy</div>
        </div>
    </div>

    <div class="card" style="max-width:560px">
        <form method="POST" action="{{ route('admin.sla-policies.store') }}">
            @csrf
            @include('admin.sla-policies._form')

            <div class="fa">
                <button type="submit" class="btn pri sm">Create policy</button>
                <a href="{{ route('admin.sla-policies.index') }}" class="btn sm">Cancel</a>
            </div>
        </form>
    </div>

</x-prototype-layout>
