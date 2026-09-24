<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Clinic' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php
    $user = auth()->user();
    $menu = match ($user->role) {
        'patient' => [['dashboard', 'Dashboard'], ['patient.profile', 'My Profile'], ['patient.book', 'Book Appointment'], ['appointments', 'My Appointments'], ['records', 'My Medical Records'], ['patient.documents', 'My Documents'], ['notifications', 'Notifications']],
        'nurse' => [['dashboard', 'Dashboard'], ['appointments', "Today's Appointments"], ['patients', 'Patients'], ['nurse.visits', 'Visits & Vital Signs'], ['records', 'Medical Records'], ['notifications', 'Notifications']],
        'doctor' => [['dashboard', 'Dashboard'], ['doctor.availability', 'My Availability'], ['appointments', 'Appointments'], ['doctor.consultations', 'Consultations'], ['patients', 'Patient Records'], ['records', 'Medical Records'], ['notifications', 'Notifications']],
        default => [['dashboard', 'Clinic Overview'], ['appointments', 'Appointments'], ['patients', 'Patients'], ['headnurse.staff', 'Nurses', ['type' => 'nurses']], ['headnurse.staff', 'Doctors', ['type' => 'doctors']], ['records', 'Medical Records'], ['headnurse.audit', 'Audit Log'], ['headnurse.reports', 'Reports'], ['notifications', 'Notifications']],
    };
    $unread = $user->clinicNotifications()->whereNull('read_at')->count();
@endphp

<div class="flex min-h-screen flex-col md:flex-row">
    <aside class="w-full shrink-0 border-b border-slate-200 bg-white md:w-64 md:border-b-0 md:border-r print:hidden">
        <div class="px-5 py-5">
            <p class="text-lg font-semibold text-teal-800">University Clinic</p>
            <p class="text-xs text-slate-500">{{ $user->name }} · {{ str_replace('_', ' ', $user->role) }}</p>
        </div>
        <nav class="flex flex-wrap gap-1 px-3 pb-3 md:block md:space-y-1">
            @foreach ($menu as $m)
                @php
                    $params = $m[2] ?? [];
                    $active = url()->current() === route($m[0], $params);
                @endphp
                <a href="{{ route($m[0], $params) }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ $active ? 'bg-teal-50 font-semibold text-teal-800' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $m[1] }}
                    @if ($m[0] === 'notifications' && $unread)
                        <span class="rounded-full bg-teal-700 px-2 text-xs text-white">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="px-5 pb-5">
            @csrf
            <button class="text-sm text-slate-500 hover:text-slate-800">Log out</button>
        </form>
    </aside>

    <main class="flex-1 p-4 md:p-8">
        {{ $slot }}
    </main>
</div>
</body>
</html>
