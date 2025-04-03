<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Posting;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApplicationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,

        private readonly string              $emailKey,
        private readonly string              $emailUrl,
        private readonly string              $emailTo,
    )
    {
    }

    public function getApplicationsByPosting(Posting $posting): array
    {
        return $posting->getApplications()->toArray();
    }

    public function sendEmail(Application $application): void
    {
        $this->httpClient->request('POST', $this->emailUrl, [
            'headers' => $this->getEmailHeaders(),
            'json' => $this->getEmailContent($application),
        ]);
    }

    private function getEmailHeaders(): array
    {
        return [
            'x-api-key' => $this->emailKey,
        ];
    }

    private function getEmailContent(Application $application): array
    {
        $posting = $application->getPosting();

        return [
            'to' => $this->emailTo,
            'subject' => "{$application->getName()} just applied to the Posting #{$posting->getId()}",
            'content' => "That's just about it.",
        ];
    }
}
