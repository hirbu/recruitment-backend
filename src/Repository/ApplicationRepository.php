<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Posting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * Get applications for a posting ordered by score
     *
     * @param Posting $posting
     * @return array
     */
    public function findByPostingOrderedByScore(Posting $posting): array
    {
        return $this->findBy(
            ['posting' => $posting],
            ['score' => 'DESC']
        );
    }
}
