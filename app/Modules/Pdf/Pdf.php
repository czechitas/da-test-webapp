<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\Pdf;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class Pdf
{
    /** @var array<string, mixed> */
    protected array $config = [];

    protected Mpdf $mpdf;

    /**
     * Load a HTML string
     *
     * @param array<string, mixed> $config
     */
    public static function loadHTML(string $html, array $config = []): self
    {
        return new self($html, $config);
    }

    /**
     * Load a HTML file
     *
     * @param array<string, mixed> $config
     */
    public static function loadFile(string $file, array $config = []): self
    {
        return new self(File::get($file), $config);
    }

    /**
     * Load a View and convert to HTML
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $config
     */
    public static function loadView(string $view, array $data = [], array $config = []): self
    {
        return new self(View::make($view, $data)->render(), $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(string $html = '', array $config = [])
    {
        $this->config = $config;

        $mpdf_config = \array_merge([
            'mode'              => \config('pdf.mode', 'utf-8'),
            'format'            => \config('pdf.format', 'A4'),
            'margin_left'       => \config('pdf.margin_left', 25),
            'margin_right'      => \config('pdf.margin_right', 25),
            'margin_top'        => \config('pdf.margin_top', 25),
            'margin_bottom'     => \config('pdf.margin_bottom', 17),
            'margin_header'     => \config('pdf.margin_header', 9),
            'margin_footer'     => \config('pdf.margin_footer', 9),
            'tempDir'           => \config('pdf.tempDir'),
            'dpi'               => \config('pdf.dpi', 96),
            'img_dpi'           => \config('pdf.img_dpi', 96),
            'orientation'       => \config('pdf.orientation', 'P'),
        ], $config);

        // Handle custom fonts
        $mpdf_config = $this->addCustomFontsConfig($mpdf_config);

        $this->mpdf = new Mpdf($mpdf_config);

        // If you want to change your document title,
        // please use the <title> tag.
        $this->mpdf->SetTitle($mpdf_config['title'] ?? 'Dokument');

        $this->mpdf->SetAuthor($mpdf_config['author'] ?? \config('pdf.author'));
        $this->mpdf->SetCreator($mpdf_config['creator'] ?? \config('pdf.creator'));
        $this->mpdf->SetSubject($mpdf_config['subject'] ?? \config('pdf.subject'));
        $this->mpdf->SetKeywords($mpdf_config['keywords'] ?? \config('pdf.keywords'));
        $this->mpdf->SetDisplayMode($mpdf_config['display_mode'] ?? \config('pdf.display_mode'));

        $this->mpdf->WriteHTML($html);
    }

    /**
     * Get mPDF instance
     */
    public function mpdf(): Mpdf
    {
        return $this->mpdf;
    }

    /**
     * @param  array<string, mixed> $mpdf_config
     * @return array<string, mixed>
     */
    protected function addCustomFontsConfig(array $mpdf_config): array
    {
        if (!Config::has('pdf.font_path') || !Config::has('pdf.font_data')) {
            return $mpdf_config;
        }

        // Get default font configuration
        $fontDirs = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        // Merge default with custom configuration
        $mpdf_config['fontDir'] = \array_merge($fontDirs, [Config::get('pdf.font_path')]);
        $mpdf_config['fontdata'] = \array_merge($fontData, Config::get('pdf.font_data'));

        return $mpdf_config;
    }

    /**
     * Encrypts and sets the PDF document permissions
     *
     * @param array<string> $permisson
     */
    public function setProtection(array $permisson, string $userPassword = '', string $ownerPassword = ''): self
    {
        if (\func_get_args()[2] === null) {
            $ownerPassword = \bin2hex(\openssl_random_pseudo_bytes(8));
        }

        return $this->mpdf->SetProtection($permisson, $userPassword, $ownerPassword);
    }

    /**
     * Output the PDF as a string.
     */
    public function output(): string
    {
        return $this->mpdf->Output('', 'S');
    }

    /**
     * Save the PDF to a file
     */
    public function save(string $filename): void
    {
        $this->mpdf->Output($filename, 'F');
    }

    /**
     * Make the PDF downloadable by the user
     */
    public function download(string $filename = 'document.pdf'): void
    {
        $this->mpdf->Output($filename, 'D');
    }

    /**
     * Return a response with the PDF to show in the browser
     */
    public function stream(string $filename = 'document.pdf'): void
    {
        $this->mpdf->Output($filename, 'I');
    }
}
