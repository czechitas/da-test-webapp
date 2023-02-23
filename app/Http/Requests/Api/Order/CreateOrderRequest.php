<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Api\Order;

use CzechitasApp\Http\Requests\Api\BaseFormRequest;
use CzechitasApp\Models\Enums\OrderType;
use CzechitasApp\Rules\EmailRule;
use CzechitasApp\Rules\PhoneRule;

class CreateOrderRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(?string $orderType = null): array
    {
        $commonRules = [
            'type'          => 'required|in:' . \implode(',', OrderType::getAvailableValues(true)),
            'client'        => 'required|string|max:255',
            'ico'           => 'required|string|max:15',
            'address'       => 'required|string|max:255',
            'substitute'    => 'required|string|max:255',
            'contact_name'  => 'required|string|max:255',
            'contact_tel'   => ['required', 'string', new PhoneRule()],
            'contact_mail'  => ['required', new EmailRule(), 'max:255'],
            'start_date_1'  => 'required|before_or_equal:end_date_1|date_format:Y-m-d',
            'end_date_1'  => 'required|after_or_equal:start_date_1|date_format:Y-m-d',
            'start_date_2'  => 'nullable|required_with:end_date_2|before_or_equal:end_date_2|date_format:Y-m-d',
            'end_date_2'  => 'nullable|required_with:start_date_2|after_or_equal:start_date_2|date_format:Y-m-d',
            'start_date_3'  => 'nullable|required_with:end_date_3|before_or_equal:end_date_3|date_format:Y-m-d',
            'end_date_3'  => 'nullable|required_with:start_date_3|after_or_equal:start_date_3|date_format:Y-m-d',
        ];

        $typeSpecificRules = [
            'students'        => 'required|integer|min:0',
            'age'             => 'required|string|max:30',
            'adults'          => 'required|integer|min:1',
        ];
        if ($this->input('type') === OrderType::CAMP || $orderType === OrderType::CAMP) {
            $typeSpecificRules = \array_merge($typeSpecificRules, [
                'date_part'   => 'required|in:forenoon,afternoon',
            ]);
        } else {
            $typeSpecificRules = \array_merge($typeSpecificRules, [
                'start_time'  => 'required|date_format:H:i',
                'start_food'  => 'required|in:breakfast,lunch,dinner',
                'end_time'    => 'required|date_format:H:i',
                'end_food'    => 'required|in:breakfast,lunch,dinner',
            ]);
        }

        return \array_merge($commonRules, $typeSpecificRules);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(?string $orderType = null): array
    {
        $ret = [
            'type'              => $this->input('type'),
            'client'            => $this->input('client'),
            'ico'               => $this->input('ico'),
            'address'           => $this->input('address'),
            'substitute'        => $this->input('substitute'),
            'contact_name'      => $this->input('contact_name'),
            'contact_tel'       => $this->input('contact_tel'),
            'contact_mail'      => $this->input('contact_mail'),
            'start_date_1'      => \getCarbon($this->input('start_date_1')),
            'start_date_2'      => \getCarbon($this->input('start_date_2')),
            'start_date_3'      => \getCarbon($this->input('start_date_3')),
            'xdata'             => [
                'students'          => \intval($this->input('students')),
                'age'               => $this->input('age'),
                'adults'            => \intval($this->input('adults')),
                'end_date_1'        => \getCarbon($this->input('end_date_1')),
                'end_date_2'        => \getCarbon($this->input('end_date_2')),
                'end_date_3'        => \getCarbon($this->input('end_date_3')),
            ],
        ];

        if ($this->input('type') === OrderType::CAMP || $orderType === OrderType::CAMP) {
            $ret['xdata']['date_part']      = $this->input('date_part');
        } else {
            $ret['xdata']['start_time']     = $this->input('start_time');
            $ret['xdata']['start_food']     = $this->input('start_food');
            $ret['xdata']['end_time']       = $this->input('end_time');
            $ret['xdata']['end_food']       = $this->input('end_food');
        }

        return $ret;
    }
}
