@component('mail::message')
# Rappel

Bonjour {nom responsable} vous un un rappel :

@component('mail::button', ['url' => $task['url']])

Visiter Notre Plateforme
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent