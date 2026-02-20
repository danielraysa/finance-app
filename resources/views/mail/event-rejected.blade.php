<x-mail::message>
# Kegiatan Ditolak

Nama Kegiatan: {{ $eventProject->event_name }}<br>
Tgl Kegiatan: {{ $eventProject->event_date->format('d/m/Y') }}<br>

@if($eventProject->description != null)
Deskripsi: {{ $eventProject->description }}<br>
@endif

Alasan Penolakan: {{ $eventProject->rejection_reason }}

<x-mail::button :url="route('event-projects.show', $eventProject->id)">
View Detail
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
