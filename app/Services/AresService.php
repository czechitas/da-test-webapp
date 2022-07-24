<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AresService
{
    public const ARES_ICO_URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico=%d';

    private Client $client;

    private DOMXPath $finder;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Load data into form by ICO
     *
     * @return array<string, string>|false|null
     */
    public function loadCompanyInfoByICO(string $ico): array|false|null
    {
        $data = null;
        try {
            if (!\is_numeric($ico)) {
                throw new \Exception("ICO is not numeric: ={$ico}=");
            }

            $domDocument = new DOMDocument();
            $domDocument->loadXML($this->loadAresData($ico));
            $this->finder = new DOMXPath($domDocument);

            if ($this->finder->query('//are:Zaznam')->length !== 1) {
                return null;
            }

            $street         = $this->getNodeValue('//dtt:Nazev_ulice');
            $houseNumber    = $this->getNodeValue('//dtt:Cislo_domovni');
            $zip            = $this->getNodeValue('//dtt:PSC');
            $city           = $this->getNodeValue('//dtt:Nazev_obce');

            $data = [
                'company' => $this->getNodeValue('//are:Obchodni_firma'),
                'address' => "{$street} {$houseNumber}, {$zip} {$city}",
            ];
        } catch (\Throwable $e) {
            Log::warning($e->getMessage(), ['exception' => $e]);

            return false;
        }

        return $data;
    }

    public function loadAresData(string $ico): string
    {
        return (string)$this->client->get(\sprintf(self::ARES_ICO_URL, $ico))->getBody();
    }

    /**
     * @return mixed
     */
    protected function getNodeValue(string $path)
    {
        $nodes = $this->finder->query($path);
        if ($nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        return null;
    }
}
