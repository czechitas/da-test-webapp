<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => 'Pole musí být přijato',
    'active_url'           => 'Pole neobsahuje platnou URL adresu',
    'after'                => 'Pole musí obsahovat datum po :date',
    'after_or_equal'       => 'Pole musí být datum nejméně :date',
    'alpha'                => 'Pole může obsahovat pouze písmena',
    'alpha_dash'           => 'Pole může obsahovat pouze písmena, číslice, pomlčky a podtržítka. České znaky nejsou podporovány',
    'alpha_num'            => 'Pole může obsahovat pouze písmena a číslice',
    'array'                => 'Pole musí být pole',
    'before'               => 'Pole musí být datum před :date',
    'before_or_equal'      => 'Pole musí být datum nejvýše :date',
    'between'              => [
        'numeric' => 'Pole musí obshovat hodnotu mezi :min a :size',
        'file'    => 'Soubor musí být větší než :min a menší než :max Kilobytů',
        'string'  => 'Text musí být delší než :min a kratší než :max znaků',
        'array'   => 'Pole musí obshovat obsahovat nejméně :min a nesmí obsahovat více než :max prvků',
    ],
    'boolean'              => 'Pole musí být true nebo false',
    'confirmed'            => 'Pole pro potvrzení nesouhlasí',
    'date'                 => 'Pole musí být platné datum, například 1.1.2000',
    'date_format'          => 'Pole není platný formát data podle :format',
    'different'            => ':attribute a :other se musí lišit',
    'digits'               => 'Pole musí být :digits číslic dlouhé',
    'digits_between'       => 'Pole musí být dlouhé nejméně :min a nejvíce :max číslic',
    'dimensions'           => 'Pole má neplatné rozměry',
    'distinct'             => 'Pole má duplicitní hodnotu',
    'email'                => 'Pole neobsahuje platnou emailovou adresu',
    'email_dns'            => 'Zadaná adresa neexistuje, zkontrolujte překlepy',
    'exists'               => 'Zvolená hodnota pro pole není platná',
    'file'                 => 'Pole musí obsahovat soubor',
    'filled'               => 'Pole musí být vyplněno',
    'gt'                   => [
        'numeric' => 'Pole musí být větší než :value',
        'file'    => 'Soubor musí být větší než :value kilobytů',
        'string'  => 'Text musí být delší než :value znaků',
        'array'   => 'Pole musí být obsahovat více než :value položek',
    ],
    'gte'                  => [
        'numeric' => 'Pole musí být větší nebo rovno :value',
        'file'    => 'Soubor musí být větší nebo rovno :value kilobytů',
        'string'  => 'Text musí být minimálně :value znaků dlouhý',
        'array'   => 'Pole musí být obsahovat více nebo rovno než :value položek',
    ],
    'image'                => 'Pole musí obsahovat obrázek',
    'in'                   => 'Zvolená hodnota pro pole je neplatná',
    'in_array'             => 'Pole není obsažen v :other',
    'integer'              => 'Pole musí obsahovat celé číslo',
    'ip'                   => ':attribute musí být platnou IP adresou',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address',
    'json'                 => ':attribute musí být platný JSON řetězec',
    'lt'                   => [
        'numeric' => 'Pole musí být menší než :value',
        'file'    => 'Soubor musí být menší než :value kilobytů',
        'string'  => 'Text musí být kratší než :value znaků',
        'array'   => 'Pole musí být obsahovat méně než :value položek',
    ],
    'lte'                  => [
        'numeric' => 'Pole musí být menší nebo rovno :value',
        'file'    => 'Soubor musí být menší nebo rovno :value kilobytů',
        'string'  => 'Text musí být maximálně :value znaků dlouhý',
        'array'   => 'Pole musí být obsahovat méně nebo rovno :value položek',
    ],
    'max'                  => [
        'numeric' => 'Číslo nesmí být vyšší než :max',
        'file'    => 'Soubor musí být menší než :max Kilobytů',
        'string'  => 'Text musí být maximálně :max znaků dlouhý',
        'array'   => 'Pole nesmí obsahovat více než :max prvků',
    ],
    'mimes'                => 'Soubor musí mít jeden z následujících datových typů :values',
    'mimetypes'            => 'Soubor musí mít jeden z následujících datových typů :values',
    'min'                  => [
        'numeric' => 'Číslo nesmí být nižší než :min',
        'file'    => 'Soubor musí být větší než :min Kilobytů',
        'string'  => 'Text musí být minimálně :min znaků dlouhý',
        'array'   => 'Pole musí obsahovat více než :min prvků',
    ],
    'not_in'               => 'Zvolená hodnota pro pole je neplatná',
    'not_regex'            => 'Formát pole není platný',
    'numeric'              => 'Pole musí obsahovat číslo',
    'present'              => 'Pole musí být vyplněno',
    'regex'                => 'Pole nemá správný formát',
    'required'             => 'Toto pole je povinné',
    'required_if'          => 'Pole musí být vyplněno pokud :other je :value',
    'required_unless'      => 'Pole musí být vyplněno dokud :other je v :values',
    'required_with'        => 'Pole musí být vyplněno pokud :values je vyplněno',
    'required_with_all'    => 'Pole musí být vyplněno pokud :values je zvoleno',
    'required_without'     => 'Pole musí být vyplněno pokud :values není vyplněno',
    'required_without_all' => 'Pole musí být vyplněno pokud není žádné z :values zvoleno',
    'same'                 => ':attribute a :other se musí shodovat',
    'size'                 => [
        'numeric' => 'Pole musí obsahovat :size',
        'file'    => 'Soubor musí být velký přesně :size Kilobytů',
        'string'  => 'Text musí být dlouhý přesně :size znaků',
        'array'   => 'Pole musí obsahovat přesně :size prvků',
    ],
    'string'               => 'Pole musí být řetězec znaků',
    'timezone'             => 'Pole musí být platná časová zóna',
    'unique'               => 'Pole musí být unikátní',
    'uploaded'             => 'Nahrávání souboru se nezdařilo',
    'url'                  => 'Pole není platná URL adresa',
    'phone'                => 'Telefon není ve správném formátu',
    'bank_account'         => 'Pole neobsahuje validní číslo účtu',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'               => [
        'password' => [
            'min' => 'Heslo musí být minimálně :min znaků dlouhé',
        ],
    ],

    'password' => [
        'min' => 'Heslo musí být minimálně :min znaků dlouhé',
        'mixed' => 'Heslo musí obsahovat minimálně jedno velké a jedno malé písmeno',
        'numbers' => 'Heslo musí obsahovat minimálně jednu číslici',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes'           => [],
];
