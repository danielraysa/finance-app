<x-mail::message>
# Pemberitahuan Kegiatan Baru Dibuat

Nama Kegiatan: {{ $eventProject->event_name }}
Tgl: {{ $eventProject->event_date->format('d/m/Y') }}
@if($eventProject->description != null)
Deskripsi: {{ $eventProject->description }}
@endif

<x-mail::button :url="route('event-projects.show', $eventProject->id)">
View Detail
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
