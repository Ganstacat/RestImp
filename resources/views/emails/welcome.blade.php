Hello, {{$user->name}}!
 
{{route('verify', $user->verification_token)}}

@component('mail::message')
# Hello, {{$user->name}}!

Thank you for creating an account. Please verify your email by using this button:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
