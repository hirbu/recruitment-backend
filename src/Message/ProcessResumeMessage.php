<?php

namespace App\Message;

class ProcessResumeMessage
{
    public function __construct(
        private readonly int $resumeId
    )
    {
    }

    public function getResumeId(): int
    {
        return $this->resumeId;
    }
}