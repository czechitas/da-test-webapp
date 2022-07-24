<?php

declare(strict_types=1);

return [
    'title'        => 'Termíny',
    'title_detail' => 'Termín :term',

    'delete_modal' => 'Opravdu smazat termín :date?',

    'breadcrumbs'  => [
        'create' => 'Nový termín',
        'index'  => 'Termíny',
        'edit'   => 'Úprava termínu',
    ],

    'form'         => [
        'category'        => 'Kategorie',
        's_symbol'        => 'Specifický symbol',
        'term_range'      => 'Termín konání',
        'opening'         => 'Datum otevření přihlášek',
        'opening_desc'    => 'Datum od kdy lze vytvářet přihlášky. Pokud je prázdné, lze ihned',
        'opening_not_yet' => 'Přihlašovat žáky bude možné až od',
        'price'           => 'Cena za kurz',
        'note_public'     => 'Poznámka',
        'note_private'    => 'Soukromá poznámka',

        'optional'        => 'Pole je nepovinné',
        'created_at'      => 'Vytvořen',
        'submit_create'   => 'Vytvořit termín',
        'submit_update'   => 'Uložit termín',

    ],

    'success'      => [
        'flash_create' => 'Termín :date byl úspěšně vytvořen',
        'flash_update' => 'Termín :date byl úspěšně upraven',
        'flash_delete' => 'Termín :date byl smazán',
    ],

    // Table
    'table'        => [
        'range'         => 'Datum konání',
        'category'      => 'Kategorie',
        'students'      => 'Přihlášeno',
        'price'         => 'Cena za kurz',
        'action'        => 'Akce',

        'students_info' => 'Počet přihlášených žáků',
    ],
];
