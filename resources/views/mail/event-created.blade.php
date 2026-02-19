<x-mail::message>
# Pemberitahuan Kegiatan Baru Dibuat

Nama Kegiatan: {{ $eventProject->event_name }}<br>
Tgl Kegiatan: {{ $eventProject->event_date->format('d/m/Y') }}<br>
Pembuat: {{ $creator->name }}<br>

@if($eventProject->description != null)
Deskripsi: {{ $eventProject->description }}
@endif

<x-mail::button :url="route('event-projects.show', $eventProject->id)">
View Detail
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
