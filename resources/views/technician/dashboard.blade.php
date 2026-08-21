<x-prototype-layout title="Technician">
    <div class="ph">
        <div class="ph-l">
            <div class="bc">Technician</div>
            <div class="pt">Dashboard overview</div>
        </div>
    </div 
    >@include('partials.pending-assignments-banner')
 
    <div class="card">
        Welcome, {{ auth()->user()->name }}. This is the Technician dashboard.
        Feature goes here.
    </div>
</x-prototype-layout>
