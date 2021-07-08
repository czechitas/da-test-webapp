<?php

declare(strict_types=1);

return [
    'name'         => 'Přihlášky Czechitas',
    'short_name'   => 'Czechitas',

    'price_czk'    => 'Kč',

    'yes'          => 'Ano',
    'no'           => 'Ne',

    'delete'       => ['confirm' => 'Opravdu smazat?'],

    'modal'        => [
        'yes'     => 'Ano',
        'no'      => 'NE',
        'no_dont' => 'NE, nechci',
        'close'   => 'Zavřít',
        'cancel'  => 'Zrušit',
    ],

    'change_flag'  => [
        'flag'          => 'Vlaječka',
        'change'        => 'Změnit',
        'title'         => 'Vyberte novou vlaječku pro označení',
        'cancel'        => 'Neměnit',

        'success_title' => 'Vlaječka nastavena',
        'success_text'  => 'Změna vlaječky byla úspěšně uložena',
    ],

    'timestamps'   => [
        'created_at' => 'Vytvořeno',
        'updated_at' => 'Naposledy upraveno',
        'deleted_at' => 'Smazáno',
    ],

    'menu'         => [
        'home'           => 'Domů',

        // Parents
        'parents'        => 'Pro rodiče',
        'parents_tuts'   => 'Návody a formuláře',
        'students'       => 'Přihlášky',
        'application'    => 'Vytvořit přihlášku',

        // Teachers
        'teachers'       => 'Pro učitelé',
        'terms'          => 'Termíny',
        'teachers_tuts'  => 'Návody a formuláře',
        'teachers_order' => 'Objednávka pro MŠ/ZŠ',

        'contact'        => 'Kontakt',

        'login'          => 'Přihlášit',
        'logged_in'      => 'Přihlášen',
        'profile'        => 'Profil',
        'logout'         => 'Odhlásit',

        'show_menu'      => 'Zobrazit menu',
    ],

    'admin'        => [
        'menu' => [
            'users'          => 'Uživatelé',
            'orders'         => 'Objednávky',
            'categories'     => 'Kategorie',
            'terms'          => 'Termíny',
            'students'       => 'Přihlášky',
            'news'           => 'Aktuality',
            'exports'        => 'Exporty',
        ],
    ],

    'breadcrumbs'  => ['home' => 'Domů'],

    'validation'   => [
        'error' => [
            'heading' => 'Špatně zadané pole|Špatně zadaná pole',
            'text'    => 'Některé pole obsahuje špatně zadanou hodnotu|Více polí obsahuje špatně zadanou hodnotu',
        ],
    ],

    'selectpicker' => [
        'title'       => 'Vyberte jednu položku ze seznamu',
        'title_short' => 'Vyberte',
    ],

    'actions'      => [
        'create'  => 'Přidat',
        'store'   => 'Uložit',
        'show'    => 'Zobrazit',
        'edit'    => 'Upravit',
        'update'  => 'Upravit',
        'destroy' => 'Smazat',
        'cancel'  => 'Zrušit',
    ],

    'footer'       => [
        'about'    => '<p>Tento systém slouží pouze k testovacím a výukovým účelům v akademiích Czechitas!</p>',

        'news'     => 'Aktuality',
        'links'    => 'Odkazy',
        'contact'  => 'Kontakty',

        'parents'  => 'Pro rodiče - návody, informace a formuláře',
        'teachers' => 'Pro pedagogy - návody a praktické informace',
        'order'    => 'Závazná objednávka kurzů a ŠvP pro MŠ/ZŠ',

        'created'  => 'Vytvořil',
    ],

    'error'        => [
        '404' => [
            'title'      => 'Soubor chybí',
            'title_text' => 'Ups, tady nic není',
            'text'       => 'Hledaná stránka chybí, zkus to jinde',
            'button'     => 'Zpět domů',
        ],
        '403' => [
            'title'      => 'Přístup zakázán',
            'title_text' => 'TOP Secret area',
            'text'       => 'Přístup zamítnut, zkus to jinde',
            'button'     => 'Zpět domů',
        ],
        '405' => [
            'title'      => 'Chybný přístup',
            'title_text' => 'Takto to nepůjde',
            'text'       => 'Tato metoda není povolena. Zkus svou akci provést znovu, nebo nám napiš',
            'button'     => 'Zpět domů',
        ],
        '419' => [
            'title'      => 'Neaktivita',
            'title_text' => 'Odhlášení z důvody neaktivity',
            'text'       => 'Dlouho jsi byl neaktivní a tvé sezení bylo stornováno. Proveď svou akci znovu',
            'button'     => 'Zpět domů',
        ],
    ],

    'homepage'     => [
        'header'          => 'Vyberte období akce',
        'breadcrumb'      => 'Období akcí',
        'more_info'       => 'Více informací',
        'alert'           => 'Pro vytvoření přihlášky vyberte období akce',
        'new_application' => 'Vytvořit přihlášku',
        'news'            => 'Co je u nás nového?',
    ],

    'week_days'    => [
        '0' => 'Neděle',
        '1' => 'Pondělí',
        '2' => 'Úterý',
        '3' => 'Středa',
        '4' => 'Čtvrtek',
        '5' => 'Pátek',
        '6' => 'Sobota',
    ],
];
