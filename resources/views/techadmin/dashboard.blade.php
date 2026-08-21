<x-prototype-layout title="Technical Admin">
    <div class="ph">
        <div class="ph-l">
            <div class="bc">Technical Admin</div>
            <div class="pt">Dashboard overview</div>
        </div>
    </div>
    @include('partials.pending-assignments-banner')
    <div class="card">
        Welcome, {{ auth()->user()->name }}. This is the Technical Admin dashboard.
        Feature goes here.
    </div>
</x-prototype-layout>
