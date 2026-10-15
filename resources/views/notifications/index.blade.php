<x-prototype-layout title="Notifications">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">General</div>
            <div class="pt">Notifications</div>
        </div>
        @if ($notifications->where('is_read', false)->count() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn sm"><i class="ti ti-checks" style="font-size:13px"></i> Mark all as read</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @if ($notifications->count() === 0)
        <div class="card" style="text-align:center;color:#64748b;padding:32px">
            You have no notifications yet.
        </div>
    @else
        @foreach ($notifications as $n)
            <div class="card" style="margin-bottom:10px;padding:14px 16px;{{ $n->is_read ? '' : 'border-left:4px solid #185fa5;background:#f8fbff' }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                    <div>
                        <div style="font-size:13.5px;font-weight:{{ $n->is_read ? '500' : '700' }};color:#0f2d5e">{{ $n->title }}</div>
                        <div style="font-size:12.5px;color:#475569;margin-top:3px">{{ $n->body }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:6px">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                    @unless ($n->is_read)
                        <form method="POST" action="{{ route('notifications.read', $n) }}">
                            @csrf
                            <button type="submit" class="btn sm" style="white-space:nowrap">Mark read</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach

        @if ($notifications->hasPages())
            <div style="display:flex;gap:10px;margin-top:12px;font-size:12.5px">
                @if (! $notifications->onFirstPage())
                    <a href="{{ $notifications->previousPageUrl() }}" class="btn sm">&larr; Newer</a>
                @endif
                @if ($notifications->hasMorePages())
                    <a href="{{ $notifications->nextPageUrl() }}" class="btn sm">Older &rarr;</a>
                @endif
            </div>
        @endif
    @endif

</x-prototype-layout>
