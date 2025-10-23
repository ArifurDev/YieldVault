@component('mail::message')
# {{ $title }}

@component('mail::panel')
{{ $message }}
@endcomponent

@endcomponent
