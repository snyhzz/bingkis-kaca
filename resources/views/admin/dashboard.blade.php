@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i> Dashboard</h1>
    <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-people fs-1 text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-images fs-1 text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_strips']) }}</h3>
                    <small class="text-muted">Photo Strips</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #17a2b8;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-camera fs-1 text-info"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_photos']) }}</h3>
                    <small class="text-muted">Total Photos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #ffc107;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-palette fs-1 text-warning"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_frames']) }}</h3>
                    <small class="text-muted">Total Frames</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <!-- Recent Photo Strips -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-images me-2"></i> Recent Photo Strips</h5>
                <a href="{{ route('admin.photo-strips.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Photos</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStrips as $strip)
                            <tr>
                                <td><strong>#{{ $strip->id }}</strong></td>
                                <td>
                                    @if($strip->user)
                                        <a href="{{ route('admin.users.show', $strip->user) }}">
                                            {{ $strip->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Guest</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $strip->photo_count }} photos</span></td>
                                <td>{{ $strip->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.photo-strips.show', $strip) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No photo strips yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i> Recent Users</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentUsers as $user)
                    <a href="{{ route('admin.users.show', $user) }}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-center text-muted">
                        No users yet
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}
</style>
@endpush
