@component('mail::message')
# Password Reset

You received this email because you requested to reset your password. Click the 'RESET PASSWORD' button to replace your forgotten password with a new one.

@component('mail::button', ['url' => $link])
RESET PASSWORD
@endcomponent

Thanks,<br>
{{ config('app.name') . ' Team' }}
@endcomponent
