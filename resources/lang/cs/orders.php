<?php

declare(strict_types=1);

return [
    'title'       => 'Objednávky',

    'breadcrumbs' => [
        'create' => 'Nová objednávka',
        'index'  => 'Objednávky',
        'edit'   => 'Úprava objednávky',
    ],

    'subject'     => [
        'created' => 'Nová objednávka',
        'signed'  => 'Zpracována objednávka',
    ],

    'type'        => [
        'camp'          => 'Příměstský tábor',
        'school_nature' => 'Škola v přírodě',

        'short'         => [
            'camp'          => 'Tábor',
            'school_nature' => 'ŠvP',
        ],
    ],

    'form'        => [
        'camp'               => 'Příměstský tábor',
        'school_nature'      => 'Škola v přírodě',

        'submit'             => 'Uložit objednávku',
        'submit_term'        => 'Uložit a označit za vyřízenou',

        'common_heading'     => 'Objednávka akce',
        'client'             => 'Odběratel',
        'address'            => 'Úplná adresa',
        'ico'                => 'IČO',
        'substitute'         => 'Zastoupena - ředitel(ka) školy',
        'ares_searching'     => 'Hledání v ARESu',

        'contact_heading'    => 'Kontaktní osoba',
        'contact_name'       => 'Jméno a příjmení',
        'contact_tel'        => 'Telefon',
        'contact_mail'       => 'Email',

        'dates_heading'      => 'Požadovaný termín',
        'since'              => 'Začátek',
        'till'               => 'Konec',
        'start_date_1'       => 'Upřednostňovaný termín 1',
        'start_date_2'       => 'Upřednostňovaný termín 2 - nepovinné',
        'start_date_3'       => 'Upřednostňovaný termín 3 - nepovinné',

        'service_heading'    => 'Objednávaná služba',

        'adults'             => 'Počet pedagogického doprovodu',
        // Camp
        'students'           => 'Počet dětí',
        'age'                => 've věku',
        'date_part'          => 'Kurz',

        // School nature
        'start_time'         => 'Nástup',
        'start_food'         => 'Strava začíná',
        'end_time'           => 'Ukončení',
        'end_food'           => 'Strava končí',

        // Enums
        'breakfast'          => 'Snídaní',
        'lunch'              => 'Obědem',
        'dinner'             => 'Večeří',
        'forenoon'           => 'Dopolední',
        'afternoon'          => 'Odpolední',
        'day_morning'        => 'Celodenní - dop. + odp.',
        'day_evening'        => 'Celodenní - odp. + večer',
        'half_day'           => 'Půldenní - odpoledne',

        // Admin part
        'update_lower'       => 'Hodnotu lze měnit níže',
        'admin_part_heading' => 'Pro vyplnění administrátorem',
        'final_date_heading' => 'Finální termín',
        'final_date_from'    => 'Finální termín od',
        'final_date_to'      => 'Finální termín do',

        'price_heading'      => 'Cena',
        'price_kid'          => 'Cena za dítě',
        'price_adult'        => 'Cena za dospělého',
        'price_total'        => 'Celková cena',
        'signed'             => 'Zpracována',
        'signed_detail'      => 'Objednávka byla zpracována :date, již nelze upravovat.',
    ],

    'success'     => [
        'flash'        => 'Objednávka byla úspěšně uložena',
        'header'       => 'Děkujeme za objednávku',
        'text'         => 'Objednávka byla úspěšně uložena a bude zpracována. O postupu vás budeme informovat. Zkontrolujte si také složku SPAM',

        'ares'         => 'Data z ARESu úspěšně načtena',

        'flash_delete' => 'Objednávka byla smazána',
    ],

    'error'       => [
        'ares'             => 'Data z ARESu se nepodařilo načíst, vyplňte je prosím ručně',
        'ares_missing_ico' => 'IČO nenalezeno, zkontrolujte jej prosím',
    ],

    'table'       => [
        'action'  => 'Akce',
        'client'  => 'Odběratel',
        'contact' => 'Kontaktní osoba',
        'signed'  => 'Zpracována',
        'type'    => 'Služba',
    ],

    'show'        => [
        'header'            => 'Detail objednávky',
        'admin_part_header' => 'Pole pro vyplnění - Správce',
    ],

    'validation'  => ['required_with' => 'Pole je povinné při zadání jednoho z datumů'],

];
