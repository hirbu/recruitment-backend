<?php

namespace App\Service;

use App\Entity\Posting;
use App\Repository\ApplicationRepository;

class ApplicationService
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
    )
    {
    }

    /**
     * Get applications for a posting ordered by score
     *
     * @param Posting $posting
     * @return array
     */
    public function getApplicationsByPosting(Posting $posting): array
    {
        return $this->applicationRepository->findByPostingOrderedByScore($posting);
    }
}
