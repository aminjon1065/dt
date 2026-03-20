<x-mail::message>
# {{ $title }}

Reference: {{ $referenceNumber }}

Status: {{ strtoupper($status) }}

{{ $summary ?: 'A procurement notice has been published on the portal.' }}

<x-mail::button :url="$url">
View notice
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
