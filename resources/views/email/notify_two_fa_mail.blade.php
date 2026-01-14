@component('mail::message')
    # {{ $title }}

@component('mail::panel')
  Your verification code is **{{ $otp }}**

  It will expire in **{{ $expiry }} minutes**.
@endcomponent

Thanks,<br>
    {{ config('app.name') }}
@endcomponent
