@component('mail::message')
# Gentile {{ $data['nome'] }} {{ $data['cognome'] }},

Grazie per l’interesse al progetto. Abbiamo preso in carico il suo messaggio, la contatteremo il più presto possibile.

Direzione del progetto.

=========

Qui di seguito i dettagli della sua richiesta:

Nome: {{ $data['nome'] }}

Cognome: {{ $data['cognome'] }}

Azienda: {{ $data['azienda'] }}

Telefono: {{ $data['telefono'] }}

Email: {{ $data['email'] }}

Note: {{ $data['note'] }}

@component('mail::button', ['url' => url('/catalog-areas/' . $data['catalogArea-id'])])
LINK ALL’AREA
@endcomponent

@endcomponent