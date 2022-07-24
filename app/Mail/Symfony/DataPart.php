<?php

declare(strict_types=1);

namespace CzechitasApp\Mail\Symfony;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\DataPart as SymfonyDataPart;

// TODO: this class might be removed, when Symfony DataPart supports creating custom CID
// https://github.com/symfony/symfony/issues/46959

class DataPart extends SymfonyDataPart
{
    private ?string $contentId;

    public function setContentId(string $cid): void
    {
        $this->contentId = $cid;
    }

    public function getContentId(): string
    {
        return $this->contentId ?: $this->contentId = $this->generateMyContentId();
    }

    public function hasContentId(): bool
    {
        return null !== $this->contentId;
    }

    public function getPreparedHeaders(): Headers
    {
        $headers = parent::getPreparedHeaders();

        if (null !== $this->contentId) {
            $headers->setHeaderBody('Id', 'Content-ID', $this->contentId);
        }

        if (null !== $this->getFilename()) {
            $headers->setHeaderParameter('Content-Disposition', 'filename', $this->getFilename());
        }

        return $headers;
    }

    private function generateMyContentId(): string
    {
        return \bin2hex(\random_bytes(16)) . '@czechitasapp.generated';
    }
}
