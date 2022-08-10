@component('mail::message')
# Password Reset

You received this email because you requested to reset your password. Copy the six character code below.

<p style="text-align: center; font-size: x-large; font-weight: bold; color: black;">{{ $otp[0] }} - {{ $otp[1] }} - {{ $otp[2] }} - {{ $otp[3] }} - {{ $otp[4] }} - {{ $otp[5] }}</p>
<br/>

Thanks,<br>
{{ config('app.name') . ' Team' }}
@endcomponent
