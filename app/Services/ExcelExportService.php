<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use Carbon\Carbon;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\Term;
use CzechitasApp\Services\Models\StudentService;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public const COLOR_CANCELED         = 'bababa';
    public const COLOR_LOGGED_OUT       = 'f5c6cb';
    public const COLOR_OVER_PAID        = 'c0ffa6';
    public const COLOR_NOT_PAID         = 'f9ebff';

    /** @var ?Spreadsheet */
    protected $spreadsheet = null;

    /** @var StudentService */
    private $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    protected function getSheet(string $name = 'Export'): Worksheet
    {
        if (!empty($this->spreadsheet)) {
            unset($this->spreadsheet);
        }

        $this->spreadsheet = new Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();
        if (!empty($name)) {
            $sheet->setTitle($name);
        }

        return $sheet;
    }

    public function sendToBrowser(string $filename = 'export.xlsx'): void
    {
        \header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        \header('Content-Transfer-Encoding: Binary');
        \header('Content-Disposition: attachment;filename="' . $filename . '"');
        \header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        die;
    }

    protected function setRangeFillColor(Worksheet $sheet, string $range, string $rgbColor): void
    {
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgbColor);
    }

    public function fullTermExport(Term $term): self
    {
        /** @var Collection<Student> $students */
        $students = $this->studentService->getListForFullTermExport($term)->get();

        $sheet = $this->getSheet('Termín');

        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getColumnDimension('B')->setWidth(21);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getStyle('B1:B2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('B4')->getFont()->setBold(true);

        $sheet->getCell('B1')->setValue($term->term_range);
        $sheet->getCell('B2')->setValue($term->category->name);

        $sheet->getCell('B4')->setValue('Cena');
        $sheet->getCell('C4')->setValue($term->price);

        $startRow = $row = 7;

        $sheet->fromArray([
            'Jméno', // B
            'Narození', // C
            'Email', // D
            'Částka', // E
            'Zapl.', // F
        ], null, 'B' . ($startRow - 1));

        foreach ($students as $student) {
            if (!empty($student->canceled)) {
                $this->setRangeFillColor($sheet, "A{$row}:F{$row}", self::COLOR_CANCELED);
            } elseif (!empty($student->logged_out)) {
                $this->setRangeFillColor($sheet, "A{$row}:F{$row}", self::COLOR_LOGGED_OUT);
            } else {
                $sheet->getCellByColumnAndRow(1, $row)->setValue($row - $startRow + 1);
            }

            $sheet->getCellByColumnAndRow(2, $row)->setValue($student->name);
            $sheet->getCellByColumnAndRow(3, $row)->setValue($student->birthday->format('d.m.Y'));
            $sheet->getCellByColumnAndRow(4, $row)->setValue($student->email);
            $sheet->setCellValueByColumnAndRow(5, $row, $student->total_price);
            $sheet->setCellValueByColumnAndRow(6, $row, $student->total_paid);
            $row += 1;
        }
        $sheet->getStyle('A' . ($startRow - 1) . ':F' . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . ($startRow - 1) . ':F' . ($startRow - 1))
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_DOUBLE);

        $row += 1;
        $this->addNotesFooter($sheet, $students, $row);

        return $this;
    }

    /**
     * @param Collection<Student> $students
     */
    protected function addNotesFooter(
        Worksheet $sheet,
        Collection $students,
        int $row,
        string $startCell = 'B',
        string $endCell = 'H'
    ): void {
        foreach ($students as $student) {
            $notes = [];
            if (!empty($student->restrictions)) {
                $notes[] = "Omezení: {$student->restrictions}";
            }
            if (!empty($student->note)) {
                $notes[] = "Poznámka: {$student->note}";
            }
            if (empty($notes)) {
                continue;
            }

            $sheet->getCellByColumnAndRow(2, $row)->setValue($student->name);
            $sheet->getStyleByColumnAndRow(2, $row)->getFont()->setBold(true);
            $row += 1;
            $sheet->mergeCells("{$startCell}{$row}:{$endCell}{$row}");
            $sheet->getStyle("{$startCell}{$row}:{$endCell}{$row}")->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(\count($notes) * 15);
            $sheet->getCellByColumnAndRow(2, $row)->setValue(\implode("\n", $notes));

            $row += 2;
        }
    }

    public function exportOverUnderPaid(Carbon $termStart, Carbon $termEnd): self
    {
        /** @var Collection<Student> $students */
        $students = $this->studentService
            ->getListOfOverUnderPaid($termStart, $termEnd)
            ->get()
            ->filter(static function ($value) {
                return $value->price_to_pay != 0;
            })->sortBy('price_to_pay');

        $sheet = $this->getSheet();

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(21);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(13);

        $sheet->getCell('B1')->setValue('Vygenerováno: ');
        $sheet->getCell('C1')->setValue(Carbon::now()->format('d.m.Y H:i'));
        $sheet->getCell('D1')->setValue(\sprintf(
            'Období: %s - %s',
            $termStart->format('d.m.Y'),
            $termEnd->format('d.m.Y')
        ));

        $sheet->getCell('B2')->setValue('Jméno');
        $sheet->getCell('C2')->setValue('Termín');
        $sheet->getCell('D2')->setValue('Kategorie');
        $sheet->getCell('E2')->setValue('Celková cena');
        $sheet->getCell('F2')->setValue('Zaplaceno');
        $sheet->getCell('G2')->setValue('Nedoplatek');
        $sheet->getCell('H2')->setValue('Přeplatek');

        $startRow = $row = 3;
        foreach ($students as $student) {
            if (!empty($student->canceled)) {
                $this->setRangeFillColor($sheet, "A{$row}:H{$row}", self::COLOR_CANCELED);
            } elseif (!empty($student->logged_out)) {
                $this->setRangeFillColor($sheet, "A{$row}:H{$row}", self::COLOR_LOGGED_OUT);
            }

            $sheet->getCellByColumnAndRow(1, $row)->setValue($row - $startRow + 1);
            $sheet->getCellByColumnAndRow(2, $row)->setValue($student->name);
            $sheet->getCellByColumnAndRow(3, $row)->setValue($student->term->term_range);
            $sheet->getCellByColumnAndRow(4, $row)->setValue($student->term->category->name);
            $sheet->getCellByColumnAndRow(5, $row)->setValue($student->total_price);
            $sheet->getCellByColumnAndRow(6, $row)->setValue($student->total_paid);
            $sheet->getCellByColumnAndRow(7, $row)->setValue("0");
            $sheet->getCellByColumnAndRow(8, $row)->setValue("0");

            $row += 1;
        }

        $sheet->getStyle("E{$startRow}:H" . ($row - 1))->getNumberFormat()->setFormatCode('# ##0 Kč');

        $sheet->getStyle('A2:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:H2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);

        return $this;
    }
}
