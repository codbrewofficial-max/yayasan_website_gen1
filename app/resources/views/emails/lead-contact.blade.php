@component('mail::layout')
    @slot('header')
        Pesan Kontak Website
    @endslot

    **Pesan baru masuk dari website.**

    | | |
    |---|---|
    | Nama | {{ $lead->name }} |
    | Email | {{ $lead->email ?: '-' }} |
    | Telepon/WA | {{ $lead->phone ?: '-' }} |
    | Topik | {{ $lead->subject ?: '-' }} |
    | Jalur | {{ $lead->lead_type }} |

    **Pesan:**
    {{ $lead->message }}
@endcomponent