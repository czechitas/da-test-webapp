<?php

declare(strict_types=1);

return [
    'title'                => 'Přihlášky',
    'title_new'            => 'Nová přihláška',
    'title_detail'         => 'Žák :student',
    'title_edit'           => ':student - úprava',
    'title_logout'         => ':student - odhlášení',
    'title_payment_import' => 'Import plateb',
    'title_send_emails'    => ':student - odeslané emaily',

    'create_button'        => 'Vytvořit novou přihlášku',

    'breadcrumbs'          => [
        'create'      => 'Nová přihláška',
        'index'       => 'Přihlášky',
        'edit'        => 'Úprava přihlášky',
        'logout'      => 'Odhlášení',
        'send_emails' => 'Odeslané emaily',
    ],

    'created_mail_subject' => 'Nová přihláška',
    'updated_mail_subject' => 'Úpravy přihlášky',

    'payments'             => [
        'transfer'          => 'Bankovní převod',
        'postal_order'      => 'Složenka',
        'fksp'              => 'FKSP',
        'cash'              => 'Hotově',
        'none'              => 'Nevybráno',
        'return'            => 'Vratka',
        'income'            => 'Příjem',
        'subject'           => 'Vložená platba',

        'transfer_desc'     => 'Informace přijdou emailem',
        'postal_order_desc' => 'Informace přijdou emailem',
        'fksp_desc'         => 'Informace přijdou emailem',
        'cash_desc'         => 'V kanceláři na základě osobní domluvy', // Form etc
        'cash_note'         => 'Možné pouze v kanceláři na základě osobní domluvy.', // Email

        'free'              => 'Je zdarma',

        'history'           => [
            'show'       => 'Zobrazit historii plateb',
            'show_short' => 'Historie plateb',

            'heading'    => 'Historie plateb',
            'date'       => 'Datum',
            'type'       => 'Operace',
            'price'      => 'Hodnota',
            'note'       => 'Poznámka',
            'added'      => 'Přidal :name',
            'created_at' => 'Připsáno zpětně dne :date',
        ],

        'insert'            => [
            'new'       => 'Správa platby',
            'heading'   => 'Přidat novou platbu/vratku',
            'direction' => 'Typ platby',
            'type'      => 'Způsob platby',
            'price'     => 'Hodnota',
            'date'      => 'Datum platby',
            'date_note' => 'K datum se doplní aktuální čas',
            'received'  => 'Vložit datum přijetí',
            'note'      => 'Poznámka',
            'submit'    => 'Vložit platbu',
        ],
    ],

    'certificates'         => [
        // Name and PDF title
        'login_file'       => 'Potvrzení o přihlášení',
        'payment_file'     => 'Potvrzení o zaplacení',

        'login_download'   => 'Stáhnout potvrzení o přihlášení',
        'payment_download' => 'Stáhnout potvrzení o zaplacení',
    ],

    'form'                 => [
        'category'              => 'Kategorie',
        'term'                  => 'Termín',
        'term_select'           => 'Vyberte termín',
        'parent_name'           => 'Jméno a příjmení zákonného zástupce',
        'forename'              => 'Křestní jméno žáka',
        'surname'               => 'Příjmení žáka',
        'birthday'              => 'Datum narození žáka',
        'birthday_note'         => 'Žák musí dovršit :years roky nejpozději v den začátku kurzu',
        'email'                 => 'Email zák. zástupce',
        'payment'               => 'Způsoby úhrady kurzu',
        'restrictions'          => 'Zdravotní omezení',
        'restrictions_yes'      => 'Ano, žák má zdravotní omezení',
        'restrictions_desc'     => 'Vyplňte jaké má žák zdravotní omezení',
        'note'                  => 'Poznámka',
        'terms_conditions'      => 'Souhlasím s všeobecnými podmínkami a zpracováním osobních údajů.',

        'note_public'           => 'Poznámka',
        'note_private'          => 'Soukromá poznámka',

        'optional'              => 'Pole je nepovinné',
        'created_at'            => 'Vytvořen',
        'parent_detail'         => 'Zobrazit rodiče',
        'submit'                => 'Vytvořit přihlášku',
        'submit_update'         => 'Upravit přihlášku',
        'submit_logout'         => 'Odhlásit žáka',
        'submit_general_update' => 'Upravit',

        /////////////
        // Details //
        /////////////

        'payment_instructions'  => 'Pokyny k platbě',
        'student_details'       => 'Detaily žáka',
        'payment_over'          => 'Máte přeplatek <strong>:pay_over</strong>',
        'payment_exact'         => 'Kurz je uhrazen v plné výši. Není potřeba žádné další kroky',
        'payment_cash_desc'     => 'Prosíme o realizaci platby v kanceláři na základě osobní domluvy',
        'payment_fksp_desc'     => 'Vystavíme vám fakturu pro vašeho zaměstnavatele',
        'acc_number'            => 'Číslo účtu',
        'acc_number_val'        => '199488012/1234 - Kočičí banka',
        'acc_address'           => 'Adresa majitele účtu',
        'acc_address_val'       => 'Czechitas<br>Dlouhá 123, 123 45 Horní Dolní',
        'price'                 => 'Zbývá uhradit',
        'v_symbol'              => 'Variabilní symbol',
        'k_symbol'              => 'Konstantní symbol',
        'k_symbol_val'          => '308',
        's_symbol'              => 'Specifický symbol',
        'message'               => 'Zpráva pro příjemce',
        'qr_payment'            => 'QR Platba',
        'certificate'           => 'Potvrzení',

        ////////////
        // Logout //
        ////////////
        'logged_out'            => 'Důvod odhlášení',
        'illness'               => 'Nemoc',
        'other'                 => 'Jiný důvod',
        'alternate'             => 'Náhradník',
        'alternate_yes'         => 'Mám náhradníka a vyplním jeho jméno',

        'validation'            => [
            'birthday_min_age' => 'Žák musí dovršit :years roky nejpozději v den začátku kurzu',
        ],
    ],

    'modal'                => ['send_notification' => 'Odeslat rodiči upozornění na změnu'],

    'admin_update'         => ['mail_subject' => 'Změna stavu přihlášky'],

    'filled_note_or_fksp'  => [
        'insert_mail_subject' => 'Nová přihláška s poznámkou či FKSP',
        'update_mail_subject' => 'Upravená přihláška s poznámkou či FKSP',
    ],

    'logout'               => [
        'button'          => 'Odhlášení',
        'heading'         => 'Správa odhlášení',
        'subject'         => 'Odhlášení',

        'status'          => 'Nyní je student',
        'isnot'           => 'řádně přihlášený',
        'illness'         => 'odhlášen z důvodu nemoci',
        'other'           => 'odhlášen z jiného důvodu',
        'alternate'       => 'Má náhradníka',
        'last_updated_at' => 'Poslední změna odhlášení proběhla :logged_out_date',
        'reason'          => 'Důvod',

        'will_not_be'     => 'Řádně přihlášený',
        'will_illness'    => 'Odhlášen z důvodu nemoci',
        'will_other'      => 'Odhlášen z jiných důvodů',
        'flash_isnot'     => 'Žák :name je řádně přihlášen',
        'flash_illness'   => 'Žák :name je odhlášen z důvodu nemoci',
        'flash_other'     => 'Žák :name je odhlášen z jiných důvodů',
    ],

    'cancel'               => [
        'button'      => 'Zrušení',
        'heading'     => 'Správa zrušení',
        'status'      => 'Nyní přihláška',
        'is'          => 'je zrušená',
        'isnot'       => 'není zrušená',
        'canceled'    => 'Je zrušená a důvod',
        'flash_is'    => 'Přihláška žáka :name je zrušená',
        'flash_isnot' => 'Žák :name je řádně přihlášený',
    ],

    'send_emails'          => [
        'button'          => 'Historie emailů',

        'created_at'      => 'Odesláno',
        'to'              => 'Příjemce',
        'subject'         => 'Předmět',
        'action'          => 'Akce',
        'show_content'    => 'Zobrazit obsah emailu',

        'mail_save_delay' => 'Email se zde v seznamu může zobrazit až s 10 minutovým zpožděním od odeslání!',
    ],

    'warnings'             => [
        'canceled'                  => 'Přihláška je zrušena z důvodu: :reason',
        'logged_out'                => 'Žák je odhlášen',
        'logged_out_alternate'      => 'Žák je odhlášen a náhradníkem je :alternate',
        'logged_out_date'           => 'Žák byl odhlášen <strong>:since</strong>',
        'logged_out_date_alternate' => 'Žák byl odhlášen <strong>:since</strong> a náhradníkem je :alternate',
        'logged_out_illness'        => 'Z důvodu nemoci',
        'logged_out_other'          => 'Z jiných důvodů',
        'logged_out_reason'         => 'Z důvodu :reason',
    ],

    'success'              => [
        'flash_create'         => 'Žák :name byl úspěšně vytvořen',
        'flash_update'         => 'Žák :name byl úspěšně upraven',
        'flash_logout'         => 'Žák :name byl úspěšně odhlášen',
        'flash_insert_payment' => 'Platba pro žáka :name byla úspěšně vložena',
    ],

    // Table
    'table'                => [
        'name'              => 'Jméno',
        'category'          => 'Kategorie',
        'term'              => 'Datum konání',
        'payment'           => 'Způsob platby',
        'price'             => 'Zbývá uhradit',
        'action'            => 'Akce',
        'created'           => 'Přihlášen',
        'contact'           => 'Kontaktní Informace',
        'restrictions'      => 'Zdravotní omezení',
        'other'             => 'Další info',

        'restrictions_info' => 'Více informací',

        'info'              => 'Detail',
        'edit'              => 'Upravit',
        'logout'            => 'Odhlášení účasti',
        'canceled'          => 'Přihláška zrušena',
        'canceled_desc'     => 'Přihláška je zrušena z důvodu: :reason',
    ],
];
