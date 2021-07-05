@component('mail::message')
# Introduction

Hi this is {{ $user->name }} from {{ config('app.name') }}! 

I am sending you an invite for you to register in our website. Please click the button below to register.


@component('mail::button', ['url' => "http://localhost:8000/api/register?user=$email", 'color' => 'success'])
Register
@endcomponent

Thanks,<br>
{{ $user->name }}
@endcomponent
