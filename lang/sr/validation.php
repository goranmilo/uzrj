<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Poruke validacije
    |--------------------------------------------------------------------------
    |
    | U Filament formama :attribute je labela polja (npr. „JMBG“), pa poruke
    | počinju sa „Polje …“ da bi se čitale prirodno uz bilo koju labelu.
    |
    */

    'accepted' => 'Polje :attribute mora biti prihvaćeno.',
    'accepted_if' => 'Polje :attribute mora biti prihvaćeno kada je :other :value.',
    'active_url' => 'Polje :attribute mora biti ispravan URL.',
    'after' => 'Polje :attribute mora biti datum posle :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum posle ili jednak :date.',
    'alpha' => 'Polje :attribute sme da sadrži samo slova.',
    'alpha_dash' => 'Polje :attribute sme da sadrži samo slova, brojeve, crtice i donje crte.',
    'alpha_num' => 'Polje :attribute sme da sadrži samo slova i brojeve.',
    'any_of' => 'Polje :attribute nije ispravno.',
    'array' => 'Polje :attribute mora biti niz.',
    'ascii' => 'Polje :attribute sme da sadrži samo jednobajtne alfanumeričke znakove i simbole.',
    'before' => 'Polje :attribute mora biti datum pre :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum pre ili jednak :date.',
    'between' => [
        'array' => 'Polje :attribute mora imati između :min i :max stavki.',
        'file' => 'Polje :attribute mora biti između :min i :max kilobajta.',
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
        'string' => 'Polje :attribute mora imati između :min i :max znakova.',
    ],
    'boolean' => 'Polje :attribute mora biti tačno ili netačno.',
    'can' => 'Polje :attribute sadrži nedozvoljenu vrednost.',
    'confirmed' => 'Potvrda polja :attribute se ne poklapa.',
    'contains' => 'Polju :attribute nedostaje obavezna vrednost.',
    'current_password' => 'Lozinka nije ispravna.',
    'date' => 'Polje :attribute mora biti ispravan datum.',
    'date_equals' => 'Polje :attribute mora biti datum jednak :date.',
    'date_format' => 'Polje :attribute mora biti u formatu :format.',
    'decimal' => 'Polje :attribute mora imati :decimal decimala.',
    'declined' => 'Polje :attribute mora biti odbijeno.',
    'declined_if' => 'Polje :attribute mora biti odbijeno kada je :other :value.',
    'different' => 'Polja :attribute i :other moraju biti različita.',
    'digits' => 'Polje :attribute mora imati :digits cifara.',
    'digits_between' => 'Polje :attribute mora imati između :min i :max cifara.',
    'dimensions' => 'Polje :attribute ima neispravne dimenzije slike.',
    'distinct' => 'Polje :attribute ima dupliranu vrednost.',
    'doesnt_contain' => 'Polje :attribute ne sme da sadrži nijedno od sledećeg: :values.',
    'doesnt_end_with' => 'Polje :attribute ne sme da se završava nijednim od sledećeg: :values.',
    'doesnt_start_with' => 'Polje :attribute ne sme da počinje nijednim od sledećeg: :values.',
    'email' => 'Polje :attribute mora biti ispravna e-mail adresa.',
    'encoding' => 'Polje :attribute mora biti kodirano kao :encoding.',
    'ends_with' => 'Polje :attribute mora da se završava jednim od sledećeg: :values.',
    'enum' => 'Izabrana vrednost polja :attribute nije ispravna.',
    'exists' => 'Izabrana vrednost polja :attribute nije ispravna.',
    'extensions' => 'Polje :attribute mora imati jednu od sledećih ekstenzija: :values.',
    'file' => 'Polje :attribute mora biti fajl.',
    'filled' => 'Polje :attribute mora imati vrednost.',
    'gt' => [
        'array' => 'Polje :attribute mora imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti veće od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće od :value.',
        'string' => 'Polje :attribute mora imati više od :value znakova.',
    ],
    'gte' => [
        'array' => 'Polje :attribute mora imati :value ili više stavki.',
        'file' => 'Polje :attribute mora biti veće ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće ili jednako :value.',
        'string' => 'Polje :attribute mora imati :value ili više znakova.',
    ],
    'hex_color' => 'Polje :attribute mora biti ispravna heksadecimalna boja.',
    'image' => 'Polje :attribute mora biti slika.',
    'in' => 'Izabrana vrednost polja :attribute nije ispravna.',
    'in_array' => 'Polje :attribute mora postojati u :other.',
    'in_array_keys' => 'Polje :attribute mora sadržati bar jedan od sledećih ključeva: :values.',
    'integer' => 'Polje :attribute mora biti ceo broj.',
    'ip' => 'Polje :attribute mora biti ispravna IP adresa.',
    'ipv4' => 'Polje :attribute mora biti ispravna IPv4 adresa.',
    'ipv6' => 'Polje :attribute mora biti ispravna IPv6 adresa.',
    'json' => 'Polje :attribute mora biti ispravan JSON.',
    'list' => 'Polje :attribute mora biti lista.',
    'lowercase' => 'Polje :attribute mora biti napisano malim slovima.',
    'lt' => [
        'array' => 'Polje :attribute mora imati manje od :value stavki.',
        'file' => 'Polje :attribute mora biti manje od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje od :value.',
        'string' => 'Polje :attribute mora imati manje od :value znakova.',
    ],
    'lte' => [
        'array' => 'Polje :attribute ne sme imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti manje ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje ili jednako :value.',
        'string' => 'Polje :attribute ne sme imati više od :value znakova.',
    ],
    'mac_address' => 'Polje :attribute mora biti ispravna MAC adresa.',
    'max' => [
        'array' => 'Polje :attribute ne sme imati više od :max stavki.',
        'file' => 'Polje :attribute ne sme biti veće od :max kilobajta.',
        'numeric' => 'Polje :attribute ne sme biti veće od :max.',
        'string' => 'Polje :attribute ne sme imati više od :max znakova.',
    ],
    'max_digits' => 'Polje :attribute ne sme imati više od :max cifara.',
    'mimes' => 'Polje :attribute mora biti fajl tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti fajl tipa: :values.',
    'min' => [
        'array' => 'Polje :attribute mora imati najmanje :min stavki.',
        'file' => 'Polje :attribute mora imati najmanje :min kilobajta.',
        'numeric' => 'Polje :attribute mora biti najmanje :min.',
        'string' => 'Polje :attribute mora imati najmanje :min znakova.',
    ],
    'min_digits' => 'Polje :attribute mora imati najmanje :min cifara.',
    'missing' => 'Polje :attribute ne sme biti prisutno.',
    'missing_if' => 'Polje :attribute ne sme biti prisutno kada je :other :value.',
    'missing_unless' => 'Polje :attribute ne sme biti prisutno osim ako je :other :value.',
    'missing_with' => 'Polje :attribute ne sme biti prisutno kada je prisutno :values.',
    'missing_with_all' => 'Polje :attribute ne sme biti prisutno kada su prisutna :values.',
    'multiple_of' => 'Polje :attribute mora biti umnožak broja :value.',
    'not_in' => 'Izabrana vrednost polja :attribute nije ispravna.',
    'not_regex' => 'Format polja :attribute nije ispravan.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'password' => [
        'letters' => 'Polje :attribute mora sadržati bar jedno slovo.',
        'mixed' => 'Polje :attribute mora sadržati bar jedno veliko i jedno malo slovo.',
        'numbers' => 'Polje :attribute mora sadržati bar jedan broj.',
        'symbols' => 'Polje :attribute mora sadržati bar jedan simbol.',
        'uncompromised' => 'Uneta vrednost polja :attribute se pojavila u curenju podataka. Izaberite drugu vrednost.',
    ],
    'present' => 'Polje :attribute mora biti prisutno.',
    'present_if' => 'Polje :attribute mora biti prisutno kada je :other :value.',
    'present_unless' => 'Polje :attribute mora biti prisutno osim ako je :other :value.',
    'present_with' => 'Polje :attribute mora biti prisutno kada je prisutno :values.',
    'present_with_all' => 'Polje :attribute mora biti prisutno kada su prisutna :values.',
    'prohibited' => 'Polje :attribute nije dozvoljeno.',
    'prohibited_if' => 'Polje :attribute nije dozvoljeno kada je :other :value.',
    'prohibited_if_accepted' => 'Polje :attribute nije dozvoljeno kada je :other prihvaćeno.',
    'prohibited_if_declined' => 'Polje :attribute nije dozvoljeno kada je :other odbijeno.',
    'prohibited_unless' => 'Polje :attribute nije dozvoljeno osim ako je :other u :values.',
    'prohibits' => 'Polje :attribute ne dozvoljava da :other bude prisutno.',
    'regex' => 'Format polja :attribute nije ispravan.',
    'required' => 'Polje :attribute je obavezno.',
    'required_array_keys' => 'Polje :attribute mora sadržati stavke za: :values.',
    'required_if' => 'Polje :attribute je obavezno kada je :other :value.',
    'required_if_accepted' => 'Polje :attribute je obavezno kada je :other prihvaćeno.',
    'required_if_declined' => 'Polje :attribute je obavezno kada je :other odbijeno.',
    'required_unless' => 'Polje :attribute je obavezno osim ako je :other u :values.',
    'required_with' => 'Polje :attribute je obavezno kada je prisutno :values.',
    'required_with_all' => 'Polje :attribute je obavezno kada su prisutna :values.',
    'required_without' => 'Polje :attribute je obavezno kada :values nije prisutno.',
    'required_without_all' => 'Polje :attribute je obavezno kada nijedno od :values nije prisutno.',
    'same' => 'Polje :attribute mora se poklapati sa :other.',
    'size' => [
        'array' => 'Polje :attribute mora sadržati :size stavki.',
        'file' => 'Polje :attribute mora imati :size kilobajta.',
        'numeric' => 'Polje :attribute mora biti :size.',
        'string' => 'Polje :attribute mora imati :size znakova.',
    ],
    'starts_with' => 'Polje :attribute mora da počinje jednim od sledećeg: :values.',
    'string' => 'Polje :attribute mora biti tekst.',
    'timezone' => 'Polje :attribute mora biti ispravna vremenska zona.',
    'unique' => 'Vrednost polja :attribute je već zauzeta.',
    'uploaded' => 'Otpremanje polja :attribute nije uspelo.',
    'uppercase' => 'Polje :attribute mora biti napisano velikim slovima.',
    'url' => 'Polje :attribute mora biti ispravan URL.',
    'ulid' => 'Polje :attribute mora biti ispravan ULID.',
    'uuid' => 'Polje :attribute mora biti ispravan UUID.',

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Nazivi atributa
    |--------------------------------------------------------------------------
    |
    | Koriste se van Filament formi (Fortify, uvoz), gde :attribute inače
    | dobija sirov naziv kolone.
    |
    */

    'attributes' => [
        'email' => 'e-mail',
        'password' => 'lozinka',
        'password_confirmation' => 'potvrda lozinke',
        'current_password' => 'trenutna lozinka',
        'name' => 'ime',
        'code' => 'kod',
        'recovery_code' => 'kod za oporavak',
    ],

];
