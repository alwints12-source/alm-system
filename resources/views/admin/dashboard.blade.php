<x-prototype-layout title="Administrative Admin">
    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">Dashboard overview</div>
        </div>
    </div>
    @include('partials.pending-assignments-banner')
    <div class="card">
        Welcome, {{ auth()->user()->name }}. This is the Administrative Admin dashboard.
        Feature goes here.
    </div>
</x-prototype-layout>
