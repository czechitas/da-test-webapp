<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    // 'failed'       => 'Tyto přihlašovací údaje neodpovídají žadnému záznamu.',
    'is_blocked'   => 'Uživatel je zablokován! Pro odblokování kontaktuje správce',
    'throttle'     => 'Příliš mnoho pokusů o přihlášení. Zkuste to prosím znovu za :seconds vteřin.',

    'login'        => [
        'header_notice' => 'Pokud jste zde poprvé, prosíme, zaregistrujte se.',
        'title'         => 'Přihlášení',
        'email'         => 'Email',
        'password'      => 'Heslo',
        'login'         => 'Přihlášit',
        'forget_pass'   => 'Zapomněli jste své heslo?',

        'first_visit'   => 'Jste zde podruhé?',
        'register'      => 'Zaregistrujte se',
    ],

    'registration' => [
        'title'            => 'Registrace',
        'header'           => 'Registrace',
        'sub_header'       => 'Registrace je určena <strong>pouze pro rodiče</strong>, kteří chtějí přihlásit své dítě. Pedagogové dostanou účet přidělený při závazné objednávce.',

        'name'             => 'Jméno a příjmení',
        'email'            => 'Email',
        'password'         => 'Heslo',
        'password_confirm' => 'Kontrola hesla',

        'register'         => 'Zaregistrovat',

        'validation'       => [
            'unique_email'   => 'Účet s tímto emailem již existuje',
            'confirmed_pass' => 'Hesla se neshodují',
        ],
    ],

    'forget'       => [
        'title'      => 'Zapomenuté heslo',
        'email'      => 'Email',
        'reset_link' => 'Resetovat heslo',

        'footer'     => 'Zadejte prosím email, kterým se přihlašujete do systému. Poté Vám bude na email zaslán odkaz s platností 60 minut, se kterým je možné si nastavit nové heslo.',

    ],

    'reset'        => [
        'title'   => 'Obnova zapomenutého hesla',
        'button'  => 'Změnit heslo',

        'subject' => 'Zapomenuté heslo',
    ],

    'confirm'      => [
        'title'   => 'Ověření hesla',
        'heading' => 'Než budete pokračovat, prosím ověřte heslo',
        'button'  => 'Ověřit heslo',

        'bad_password' => 'Zadané heslo není správné',
    ],

    'profile'      => [
        'title'         => 'Profil',
        'header'        => 'Změna údajů',
        'sub_header'    => 'Emailová adresa nelze měnit. V případě nutnosti nás kontaktujte.',
        'fill_password' => 'Heslo vyplňte pouze v případě, že jej chcete změnit.',

        'success'       => 'Údaje byly úspěšně uloženy',

        'button'        => 'Změnit',
    ],

];
