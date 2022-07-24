# Údaje o kurzu a žákovi
@component('mail::table', ['twoColumns' => true])
| Jméno dítěte              | {{ $student->name }} |
| -----: |:-----|
| **Termín kurzu**          | {{ $student->term->term_range }}<br>{{ $student->term->category->name }} |
| **Jméno zákonného zástupce** | {{ $student->parent_name }} |
| **Datum narození**        | {{ $student->birthday->format("d.m.Y") }} |
| **Zdravotní omezení**     | {{ empty($student->restrictions) ? "Ne" : $student->restrictions }} |
@endcomponent
