<?php

declare(strict_types=1);

namespace CzechitasApp\Rules;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Contracts\Validation\Rule;

class EmailRule implements Rule
{
    private ?string $failsOnRule = null;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function passes($attribute, $value): bool
    {
        $validator = new EmailValidator();

        if (!$validator->isValid($value, new RFCValidation())) {
            $this->failsOnRule = 'rfc';

            return false;
        }

        if (!$validator->isValid($value, new DNSCheckValidation())) {
            $this->failsOnRule = 'dns';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        if ($this->failsOnRule === 'dns') {
            return \trans('validation.email_dns');
        }

        return \trans('validation.email');
    }
}
