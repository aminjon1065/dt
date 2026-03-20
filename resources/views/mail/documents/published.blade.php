<x-mail::message>
# {{ $title }}

@if($documentDate)
Date: {{ $documentDate }}
@endif

@if($fileType)
Type: {{ strtoupper($fileType) }}
@endif

{{ $summary ?: 'A document has been published on the portal.' }}

<x-mail::button :url="$url">
View document
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
