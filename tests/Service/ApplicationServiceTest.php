<?php

namespace App\Tests\Service;

use App\Entity\Posting;
use App\Repository\ApplicationRepository;
use App\Service\ApplicationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationServiceTest extends TestCase
{
    private ApplicationRepository|MockObject $applicationRepository;
    private ApplicationService $applicationService;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock(ApplicationRepository::class);
        $this->applicationService = new ApplicationService($this->applicationRepository);
    }

    public function testGetApplicationsByPosting(): void
    {
        // Arrange
        $posting = $this->createMock(Posting::class);
        $expectedApplications = ['application1', 'application2'];
        
        $this->applicationRepository
            ->expects($this->once())
            ->method('findByPostingOrderedByScore')
            ->with($posting)
            ->willReturn($expectedApplications);
        
        // Act
        $result = $this->applicationService->getApplicationsByPosting($posting);
        
        // Assert
        $this->assertSame($expectedApplications, $result);
    }
}
