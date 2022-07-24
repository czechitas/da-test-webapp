<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\QRPayment;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\GDImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use Imagick;

class QRPayment
{
    /** @var array<string, string> */
    protected array $qrCodeData = [];

    public function __construct(
        float $price,
        string|int|null $vs = null,
        string|int|null $ks = null,
        string|int|null $ss = null,
        ?string $msg = null,
        ?string $bankAcc = null,
    ) {
        $this->qrCodeData = \array_filter([
            'ACC'   => static::bankAccToIBAN($bankAcc ?? \config('czechitas.bank_acc')),
            'AM'    => \str_replace(' ', '', \formatPrice($price, '', true, '.')),
            'CC'    => 'CZK',
            'X-VS'  => (string)$vs,
            'X-KS'  => (string)$ks,
            'X-SS'  => (string)$ss,
            'MSG'   => $this->validateMessageText($msg),
        ]);
    }

    /**
     * Validate text to pass into QR payment
     */
    public function validateMessageText(?string $text, int $maxLength = 50): ?string
    {
        if ($text === null) {
            return null;
        }
        $text = Str::ascii(Str::upper($text), 'cs');
        $text = \str_replace(['_', ',', ':'], ' ', $text);

        $text = \preg_replace('/[^A-Z0-9_+.\- ]/', '', $text); // jiné než povolené znaky se vymažou
        $text = \preg_replace('/\s+/', ' ', $text); //jsou-li 2 a více mezer za sebou, nahradíme za jednu

        return Str::limit($text, $maxLength, '');
    }

    public function getCodeContent(string $version = '1.0'): string
    {
        $dataToJoin = [];
        foreach ($this->qrCodeData as $key => $value) {
            $dataToJoin[] = Str::upper($key) . ':' . $value;
        }

        return "SPD*{$version}*" . \implode('*', $dataToJoin);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function toText(
        ImageBackEndInterface $imageBackend,
        bool $base64 = false,
        array $settings = [],
        ?Fill $fill = null,
        ?ModuleInterface $module = null,
    ): string {
        $settings = \array_merge([
            'size'            => 400,
            'correctionLevel' => ErrorCorrectionLevel::M(),
            'encoding'        => Encoder::DEFAULT_BYTE_MODE_ECODING,
            'margin'          => 4,
        ], $settings);

        $renderer = new ImageRenderer(
            new RendererStyle($settings['size'], $settings['margin'], $module, null, $fill),
            $imageBackend,
        );
        $writer = new Writer($renderer);

        $binary = $writer->writeString($this->getCodeContent(), $settings['encoding'], $settings['correctionLevel']);
        if ($base64) {
            return \base64_encode($binary);
        }

        return $binary;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function toPngText(bool $base64 = false, array $settings = []): string
    {
        if (\class_exists(Imagick::class)) {
            return $this->toText(
                new ImagickImageBackEnd(),
                $base64,
                $settings,
                Fill::uniformGradient(
                    new Alpha(0, new Rgb(0, 0, 0)),
                    new Gradient(new Rgb(157, 0, 86), new Rgb(110, 0, 61), GradientType::VERTICAL()),
                ),
                SquareModule::instance(),
            );
        }

        return $this->toText(
            new GDImageBackEnd(),
            $base64,
            $settings,
            Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(157, 0, 86)),
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function toSvgText(bool $base64 = false, array $settings = []): string
    {
        return $this->toText(
            new SvgImageBackEnd(),
            $base64,
            $settings,
            Fill::uniformGradient(
                new Alpha(0, new Rgb(0, 0, 0)),
                new Gradient(new Rgb(139, 58, 58), new Rgb(111, 34, 34), GradientType::VERTICAL()),
            ),
            new RoundnessModule(0.5),
        );
    }

    public static function bankAccToIBAN(string $bankAcc): string
    {
        if (\strlen($bankAcc) === 24) {
            return $bankAcc;
        }

        $matches = [];
        if (!\preg_match('/^(?:([0-9]{1,6})-)?([0-9]{2,10})\/([0-9]{4})$/', $bankAcc, $matches)) {
            return $bankAcc;
        }

        $prefix = \str_pad($matches[1], 6, '0', \STR_PAD_LEFT);
        $main = \str_pad($matches[2], 10, '0', \STR_PAD_LEFT);
        $bankCode = $matches[3];

        $controllNumber = \str_pad(
            (string)(98 - (int)\bcmod("{$bankCode}{$prefix}{$main}123500", '97')),
            2,
            '0',
            \STR_PAD_LEFT,
        );

        return "CZ{$controllNumber}{$bankCode}{$prefix}{$main}";
    }
}
