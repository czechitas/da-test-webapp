<?php

declare(strict_types=1);

return [
    'title'          => 'Kategorie',

    'delete_modal'   => 'Opravdu smazat kategorii :name?',

    'empty_category' => 'V této kategorii momentálně nejsou žádné otevřené termíny',

    'breadcrumbs'    => [
        'create' => 'Nová kategorie',
        'index'  => 'Kategorie',
        'edit'   => 'Úprava kategorie',
    ],

    'form'           => [
        'name'          => 'Název',
        'parent'        => 'Nadřazená kategorie',
        'cover_img'     => 'Obrázek',
        'new_cover_img' => 'Vyměnit obrázek',
        'content'       => 'Popis',

        'created_at'    => 'Vytvořen',
        'no_parent'     => 'Hlavní kategorie',

        'submit_create' => 'Vytvořit',
        'submit_update' => 'Uložit',

    ],

    'success'        => [
        'flash_create'  => 'Kategorie :name byla úspěšně vytvořena',
        'flash_update'  => 'Kategorie :name byla úspěšně upravena',
        'flash_reorder' => 'Kategorie :name byla úspěšně posunuta',
        'flash_delete'  => 'Kategorie :name byla smazána',
    ],

    'error'          => ['flash_reorder' => 'Tímto směrem nelze kategorii přesunout'],

    // Table
    'table'          => [
        'position'   => 'Pořadí',
        'name'       => 'Název',
        'term_count' => 'Termínů / smazaných / otevřených',
        'action'     => 'Akce',

        // 'move_up'    => 'Posunout nahoru',
        'move_down'  => 'Posunout dolů',
    ],

];
