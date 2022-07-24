<?php

declare(strict_types=1);

use Carbon\Carbon;
use CzechitasApp\Models\BaseModel;
use CzechitasApp\Models\Student;
use CzechitasApp\Modules\Parsedown\NewLineParsedown;
use CzechitasApp\Modules\Parsedown\PlaintextParsedown;
use CzechitasApp\Services\FormatNameService;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Generate the URL to a named route with routeBack link
 *
 * @param int|string|BaseModel|array<string, int|string|BaseModel>|array<int|string|BaseModel> $parameters
 */
function routeBack(
    string $name,
    int|string|BaseModel|array $parameters = [],
    ?string $back = null,
    bool $absolute = true,
): string {
    $parameters = Arr::wrap($parameters);

    $routeBack = $back ?? Request::query('routeBack', null);
    if ($routeBack !== null) {
        $parameters['routeBack'] = $routeBack;
    }

    return app('url')->route($name, $parameters, $absolute);
}

/**
 * Print checked or another checkKeyword if old value is equal input value
 *
 * @param string     $inputName    Input name, passed to old function
 * @param mixed      $inputValue   Input value, for which should be checked
 * @param mixed|null $defaultValue Default value if old is empty. Second parametr for old function
 * @param string     $checkKeyword Keyword returned is should be checked
 */
function oldChecked(string $inputName, $inputValue, $defaultValue = null, string $checkKeyword = 'checked'): string
{
    // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators
    if (old($inputName, $defaultValue) == $inputValue) {
        return $checkKeyword;
    }

    return '';
}

/**
 * Print selected if old value is equal input value
 *
 * @param string     $inputName    Input name, passed to old function
 * @param mixed      $inputValue   Input value, for which should be checked
 * @param mixed|null $defaultValue Default value if old is empty. Second parametr for old function
 */
function oldSelected(string $inputName, $inputValue, $defaultValue = null): string
{
    return oldChecked($inputName, $inputValue, $defaultValue, 'selected');
}

/**
 * Print selected if one of item in old array is equal input value
 *
 * @param string     $inputName    Input name, passed to old function
 * @param mixed      $inputValue   Input value, for which should be checked
 * @param mixed|null $defaultValue Default value if old is empty. Second parametr for old function
 */
function oldSelectedMultiple(string $inputName, $inputValue, $defaultValue = null): string
{
    $old = old($inputName, $defaultValue);
    if (is_array($old)) {
        return in_array($inputValue, $old) ? 'selected' : '';
    }

    return oldSelected($inputName, $inputValue, $defaultValue);
}

/**
 * Test, if given field exists in old
 *
 * @param string      $inputName Input name, passed to old function
 * @param string|null $keyword   If is null, true/false is returned. If is string and input exists, keyword is returned
 */
function oldExists(string $inputName, ?string $keyword = null): bool|string
{
    $old = old($inputName);
    if (empty($keyword)) {
        return !empty($old);
    }

    return empty($old) ? '' : $keyword;
}

/**
 * Get Carbon instance from text or default if text is empty
 *
 * @param string|null $text    Get Carbon initiated by string
 * @param mixed|null  $default Default value to return, if text is empty
 */
function getCarbon(?string $text, $default = null): ?Carbon
{
    if (empty($text)) {
        return $default;
    }

    return new Carbon($text);
}

/**
 * Convert table in markdown to plain text table
 */
function drawTextTableFromMD(?string $markdown): HtmlString
{
    $markdown = preg_replace('/<br(\s?\/)?>/i', ' ', $markdown ?? '');
    $markdown = preg_replace('/[\*]{2,}/', '', $markdown);
    $markdown = strip_tags($markdown);
    $plainParsedown = new PlaintextParsedown();

    return new HtmlString($plainParsedown->text($markdown));
}

/**
 * Convert markdown into HTML
 */
function markdownToHtml(?string $markdown): HtmlString
{
    $parsedown = new Parsedown();

    return new HtmlString($parsedown->text($markdown ?? ''));
}

/**
 * Secure produced Markdown to not contain any HTML or PHP
 */
function secureMarkdown(?string $content, bool $removeHtml = false, bool $addBrToNewLines = true): string
{
    if (!$content) {
        return '';
    }
    // Standardize line breaks
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    // Secure agains HTML ang PHP tags
    if ($removeHtml) {
        $content = preg_replace('/<\/?[a-z0-9]+(\s[^>]*)?>/i', '', $content);
    }
    $content = e($content);

    // If image is alone on line, put empty space before and after
    $content = preg_replace('/^[ \t]*(\!\[[^\]]*\]\([^)]+\))[ \t]*$/m', "\n$1\n", $content);

    if ($addBrToNewLines) {
        $content = (new NewLineParsedown())->addLineEndings($content);
    }

    return $content;
}

/**
 * Prepare saved markdown to be placed back to editor
 */
function markdownBackToEditor(?string $content): string
{
    return preg_replace('/<br\s*>/i', '', $content ?? '');
}

/**
 * Remove markdown text or transfer it to plaintext
 */
function removeMarkdown(?string $markdown): string
{
    // Img
    $markdown = preg_replace('/\!\[([^\]]*)\]\([^)]+\)/im', '(Obrázek: $1)', $markdown ?? '');
    // Link
    $markdown = preg_replace('/\[[^\]]*\]\((?:mailto:|tel:)?([^) ]+)(?:[ ]+[^)]+)?\)/im', '$1', $markdown);
    // horizontal line
    $markdown = preg_replace('/^-{3,}$/im', '-------------------------------------', $markdown);

    return $markdown;
}

/**
 * Add name to end of subject
 */
function mailSubject(string $subject, ?string $append = null): string
{
    $append = $append ?? config('mail.subject_add');
    if (!empty($append)) {
        $postfix = config('mail.subject_add_separator') . $append;
        if (!Str::endsWith($subject, $postfix)) {
            return $subject . $postfix;
        }
    }

    return $subject;
}

/**
 * Format price
 *
 * @param mixed $price
 */
function formatPrice($price, ?string $currency = null, bool $addCents = false, string $centsSeparator = ','): string
{
    if (empty($price) && $price !== 0) {
        return '';
    }
    if (is_string($price)) {
        $price = str_replace(',', '.', $price);
    }
    $parsed = explode('.', trim((string)$price), 2);
    $isNegativePrice = Str::startsWith($parsed[0], '-');

    $strVal = str_split(str_replace('-', '', $parsed[0]));
    $c = count($strVal);
    for ($i = 3; $i < $c; $i += 4) {
        array_splice($strVal, count($strVal) - $i, 0, ' ');
    }
    $cents = '';
    if ($addCents) {
        $cents = '00';
        if (!empty($parsed[1])) {
            $cents = strlen($parsed[1]) === 2 ? $parsed[1] : $parsed[1] . '0';
        }
        $cents = $centsSeparator . $cents;
    }
    $currency = $currency ?? trans('app.price_czk');
    if (!empty($currency)) {
        $currency = " {$currency}";
    }

    return ($isNegativePrice ? '-' : '') . implode('', $strVal) . $cents . $currency;
}

/**
 * Format price with cents
 *
 * @param mixed $price
 */
function formatPriceWithCents($price, ?string $currency = null, string $centsSeparator = ','): string
{
    return formatPrice($price, $currency, true, $centsSeparator);
}

/**
 * Generate slug from text
 */
function getSlug(string $text, int $maxLength = 30): string
{
    $text = Str::slug($text, '-', 'cs');

    return Str::substr($text, 0, $maxLength);
}

/**
 * Get current resource name to pass to class attribute of body
 */
function getResourceNameFromRoute(?string $routeName): string
{
    $routeName = preg_replace('/^(admin\.)?(.*?)(\.[a-z0-9_]+)?$/', '$2', Str::lower($routeName ?? ''));
    $routeName = preg_replace('/[^a-z0-9]/', '_', $routeName);

    return empty($routeName) ? 'other' : $routeName;
}

/**
 * Remove http or https from URL
 */
function prettyUrl(string $route): string
{
    return preg_replace('/https?:\/\//i', '', $route);
}

/**
 * Translate value to Yes or No in current language
 */
function transYesNo(string $value): string
{
    $keyword = ((int)$value <= 0 || $value === 'no') ? 'no' : 'yes';

    return trans('app.' . $keyword);
}

/**
 * Get class for student in list to colorate row
 */
function studentListClass(Student $student, string $prefix = 'table-'): string
{
    if ($student->canceled) {
        return "{$prefix}dark";
    }
    if ($student->logged_out) {
        return "{$prefix}danger";
    }

    return '';
}

/**
 * Get number for Data table sorting by status
 */
function studentSortNumber(Student $student): int
{
    if ($student->canceled) {
        return 9;
    }
    if ($student->logged_out) {
        return 6;
    }

    return 0;
}

/**
 * Get FontAwesome CSS class according to suffix of file
 */
function getFontAwesomeFileIconClass(string $filename): string
{
    $mapping = [
        // Excel
        'xls'   => 'fa-file-excel',
        'xlsx'  => 'fa-file-excel',
        'ods'   => 'fa-file-excel',
        'csv'   => 'fa-file-excel',
        // Word
        'doc'   => 'fa-file-word',
        'docx'  => 'fa-file-word',
        'odt'   => 'fa-file-word',
        // Powerpoint
        'ppt'   => 'fa-file-powerpoint',
        'pps'   => 'fa-file-powerpoint',
        'pptx'  => 'fa-file-powerpoint',
        'ppsx'  => 'fa-file-powerpoint',
        'odp'   => 'fa-file-powerpoint',
        // Others
        'pdf'   => 'fa-file-pdf',
        'rar'   => 'fa-file-archive',
        'zip'   => 'fa-file-archive',
        'jpg'   => 'fa-file-image',
        'jpeg'  => 'fa-file-image',
        'png'   => 'fa-file-image',
    ];
    $extension = preg_replace('/^.*\.([a-z]+)$/i', '$1', $filename);
    if (!empty($extension) && isset($mapping[$extension])) {
        return $mapping[$extension];
    }

    return 'fa-file';
}

/**
 * Translate the given message or return default, if not found
 *
 * @param  array<string> $replace
 * @return Translator|string|array<string>|null
 */
function transDef(
    string $key,
    ?string $default = '',
    array $replace = [],
    ?string $locale = null,
): Translator|string|array|null {
    $translated = trans($key, $replace, $locale);

    return $translated === $key ? $default : $translated;
}

/**
 * Get current release name
 */
function getReleaseVersion(): string
{
    return sprintf(
        '%s@%s',
        Str::slug(config('app.name')),
        config('app.release_version'),
    );
}

/**
 * @param mixed $value
 */
function boolToString($value): string
{
    return $value ? 'True' : 'False';
}

function nameToVocative(?string $name, bool $firstNameOnly = true): ?string
{
    if (!$name) {
        return null;
    }

    return resolve(FormatNameService::class)->vocative($name, $firstNameOnly);
}

function isWomanName(string $name): bool
{
    return resolve(FormatNameService::class)->isWoman($name);
}

function formatNameCase(?string $name): ?string
{
    if (!$name) {
        return null;
    }

    return resolve(FormatNameService::class)->formatCase($name);
}

function getServerName(): string
{
    return env('OVERRIDE_APP_NAME', env('HEROKU_APP_NAME', ''));
}

function dbTablePrefix(): string
{
    $serverName = getServerName();
    if (env('DISABLE_PREFIXES', false) === true || $serverName === '') {
        return '';
    }
    $prefix = str_replace(['czechitas', 'app'], '', Str::before($serverName, '.'));
    $prefix = Str::slug($prefix);
    if ($prefix === '') {
        return '';
    }

    return "{$prefix}_";
}

function baseFolderName(): string
{
    $serverName = getServerName();
    if (env('DISABLE_SUBFOLDERS', false) === true || $serverName === '') {
        return '';
    }
    $prefix = str_replace(['czechitas', 'app'], '', Str::before($serverName, '.'));
    $prefix = Str::slug($prefix);
    if ($prefix === '') {
        return '';
    }

    return "{$prefix}/";
}
