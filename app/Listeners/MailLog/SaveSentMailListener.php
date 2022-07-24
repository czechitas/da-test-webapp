<?php

declare(strict_types=1);

namespace CzechitasApp\Listeners\MailLog;

use CzechitasApp\Services\Models\SendEmailService;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class SaveSentMailListener
{
    private SendEmailService $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
    }

    public function handle(MessageSent $event): void
    {
        if (empty($event->data['student'])) {
            return;
        }

        try {
            $data = [
                'student'       => $event->data['student'],
                'from'          => $this->formatAddresses($event->message->getFrom()),
                'to'            => $this->formatAddresses($event->message->getTo()),
                'subject'       => $event->message->getSubject(),
                'body'          => $this->getBodyWithEmbededImage($event->message),
                'attachments'   => $this->formatAttachments($event->message->getAttachments()),
            ];

            $this->sendEmailService->insert($data);
        } catch (\Throwable $e) {
            Log::warning($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @param Address[] $addresses
     */
    protected function formatAddresses(array $addresses): ?string
    {
        $formated = [];
        foreach ($addresses as $addr) {
            $formated[] = $addr->toString();
        }

        return empty($formated) ? null : \implode(', ', $formated);
    }

    /**
     * @param  array|DataPart[] $children
     * @return array<string>
     */
    protected function formatAttachments(array $children): ?array
    {
        $attachments = [];
        foreach ($children as $child) {
            $attachments[] = $child->getFilename();
        }

        return empty($attachments) ? null : $attachments;
    }

    public function getBodyWithEmbededImage(Email $message): string
    {
        $body = $message->getHtmlBody();
        if (!\is_string($body)) {
            return '';
        }

        if (Str::contains($body, '</body>')) {
            $script = "document.querySelectorAll('img[src^=cid]').forEach(
                function(el){
                    var key = el.src.replace(/^cid:/, '');
                    if( cids[key] ){ el.src = cids[key]; }
                }
            )";

            $cids = [];
            /** @var DataPart[] $attachments */
            $attachments = $message->getAttachments();
            foreach ($attachments as $child) {
                if ($child->getMediaType() === 'image' && $child->hasContentId()) {
                    $cids[$child->getContentId()] = \sprintf(
                        'data:%s;base64,%s',
                        $child->getContentType(),
                        \base64_encode($child->getBody()),
                    );
                }
            }
            $body = Str::replaceFirst(
                '</body>',
                \sprintf('<script>var cids = %s; %s</script></body>', \json_encode($cids), $script),
                $body,
            );
        }

        return $body;
    }
}
