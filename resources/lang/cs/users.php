<?php

declare(strict_types=1);

return [
    'title'         => 'Uživatelé',

    'delete_modal'  => 'Opravdu smazat uživatele :name?',
    'unblock_modal' => 'Opravdu odblokovat uživatele :name?',

    'breadcrumbs'   => [
        'create' => 'Nový uživatel',
        'index'  => 'Uživatelé',
        'edit'   => 'Úprava uživatele',
        'delete' => 'Smazání uživatele',
    ],

    'actions'       => [
        'delete'  => 'Smazat uživatele',
        'unblock' => 'Odblokovat',
    ],

    'role'          => [
        'master' => 'Hl. Správce',
        'admin'  => 'Správce',
        'parent' => 'Rodič',
    ],

    'form'          => [
        'name'                  => 'Jméno',
        'email'                 => 'Email - také přihlašovací jméno',
        'role'                  => 'Role',
        'password_change'       => 'Heslo vyplňujte pouze, pokud se má změnit',
        'password'              => 'Heslo',
        'password_confirmation' => 'Heslo pro kontrolu',
        'password_filled'       => 'Heslo doplněno automaticky z objednávky, nastaveno na <strong>:pass</strong>',

        'created_at'            => 'Vytvořen',

        'submit_create'         => 'Vytvořit',
        'submit_update'         => 'Uložit',

    ],

    'delete'        => [
        'delete_heading' => 'Mazání uživatele',
        'delete'         => 'Smazáním dojde k úplnému odstranění uživatele ze systému. Se stejným emailem půjde vytvořit nový účet',
        'delete_btn'     => 'Smazat uživatele',
        'cannot'         => 'Tohoto uživatele nelze smazat, jsou na něj navázány tyto akce',
        'block_heading'  => 'Blokace uživatele',
        'block'          => 'Zablokováním se uživatel nemůže přihlásit, ale ani registrovat znovu se stejným emailem. Blokaci lze později zrušit',
        'block_btn'      => 'Zablokovat uživatele',

        'constraints'    => [
            'payments' => 'Vložil platbu k žákům',
            'students' => 'Vytvořil přihlášku',
        ],
    ],

    'success'       => [
        'flash_create'  => 'Uživatel :name byl úspěšně vytvořen',
        'flash_update'  => 'Uživatel :name byl úspěšně upraven',
        'flash_delete'  => 'Uživatel :name byl smazán',
        'flash_block'   => 'Uživatel :name byl úspěšně zablokován',
        'flash_unblock' => 'Uživatel :name byl úspěšně odblokován',
        'flash_delete'  => 'Objednávka byla smazána',
    ],

    'validation'    => ['email_unique' => 'Uživatel s tímto emailem již existuje'],

    // Table
    'table'         => [
        'name'   => 'Jméno',
        'email'  => 'Email',
        'role'   => 'Role',
        'action' => 'Akce',
    ],
];
