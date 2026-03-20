<x-mail::message>
# {{ $title }}

{{ $summary ?: 'A new public update has been published on the portal.' }}

<x-mail::button :url="$url">
Read update
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
