@component('mail::message')
# Introduction

Hi {{ $user->name }}, 

To complete the registration process please login with the following pin {{ $user->pin }}.

You can click the button below to login.

@component('mail::button', ['url' => 'http://localhost:8000/api/login/verfication', 'color' => 'success'])
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
