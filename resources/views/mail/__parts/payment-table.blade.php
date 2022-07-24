# Pokyny k platbě
@if ($student->price_to_pay < 0) {{-- PAYMENT OVER/EXACT/MISSING --}}
Máte **přeplatek {{ formatPrice(abs($student->price_to_pay)) }}**, který Vám bude vracen po telefonické domluvě.

---
@elseif($student->price_to_pay == 0){{-- PAYMENT OVER/EXACT/MISSING --}}
Kurz je uhrazen v plné výši. Není potřeba žádné další kroky

---
@else {{-- PAYMENT OVER/EXACT/MISSING --}}
@if ($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::CASH) {{-- PAYMENT_TYPE --}}
**Způsob platby: Hotově** - Prosíme o realizaci platby v kanceláři na základě osobní domluvy.
@elseif($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::FKSP) {{-- PAYMENT_TYPE --}}
**Způsob platby: FKSP**
@else {{-- PAYMENT_TYPE --}}
@component('mail::table', ['twoColumns' => true])
| Způsob platby         | @lang('students.payments.'.$student->payment) |
| -----: |:-----|
| **Číslo účtu**        | 199488012/1234 - Kočičí banka |
@if ($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::POSTAL_ORDER)
| **Adresa majitele účtu**| Czechitas<br>Dlouhá 123, 123 45 Horní Dolní |
@endif
| **Částka**            | **{{ formatPrice($student->price_to_pay) }}** |
| **Variabilní symbol** | {{ $student->variable_symbol }} |
| **Konstantní symbol** | 308 |
| **Datum splatnosti** |  {{ $student->term->start->format("d.m.Y") }} |
| **Zpráva pro příjemce** | {{ $student->payment_message }} |
@endcomponent
@if (isset($showQRPayment) && $showQRPayment)
@component('mail::qr_payment')
@endcomponent
@endif

@endif {{-- PAYMENT_TYPE --}}
@endif {{-- PAYMENT OVER/EXACT/MISSING --}}
