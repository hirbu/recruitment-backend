<?php

namespace App\Repository;

use App\Entity\Resume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resume>
 */
class ResumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resume::class);
    }

    /**
     * Create a resume from a file path
     * 
     * @param string $filePath
     * @return Resume
     */
    public function createResumeFromFilePath(string $filePath): Resume
    {
        $resume = new Resume();

        $resume->setPath($filePath);

        $this->getEntityManager()->persist($resume);
        $this->getEntityManager()->flush();
        
        return $resume;
    }
}
