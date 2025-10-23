@component('mail::message')
# Email Verification

Your verification code is:

@component('mail::panel')
        **{{ $code }}**
@endcomponent

Please enter this code to verify your email address. This code will expire in 10 minutes.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
