<?php

namespace App\Service;

use App\Entity\Resume;
use App\Repository\ResumeRepository;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ResumeService
{
    private int $maximumFileSizeBytes;

    public function __construct(
        private readonly ResumeRepository $resumeRepository,
        private readonly string           $targetDirectory,
        private readonly string           $maximumFileSizeMega,
        private readonly array            $allowedMimeTypes,
        private readonly string           $pythonScriptPath
    )
    {
        $this->maximumFileSizeBytes = (int)$this->maximumFileSizeMega * 1024 * 1024;
    }

    /**
     * Validate the Resume file and create it
     */
    public function processUpload(Request $request): Resume
    {
        $file = $this->extractFile($request);

        $this->validateResumeFile($file);

        $filePath = $this->storeResumeFile($file);

        return $this->createResumeEntity($filePath);
    }

    /**
     * Returns the actual Resume file
     */
    public function processDownload(Resume $resume): File
    {
        return $this->getFile($resume);
    }

    /**
     * Score the Resume
     */
    public function scoreResume(Resume $resume, array $tags): int
    {
        $file = $this->getFile($resume);

        return $this->callScript($file, $tags);
    }

    /**
     * Extract the Resume file from the Request
     */
    protected function extractFile(Request $request): File
    {
        return $request->files->get('resume');
    }

    /**
     * Validate that the uploaded Resume file meets requirements
     */
    protected function validateResumeFile(File $file): void
    {
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $allowedMimeTypes = implode(', ', $this->allowedMimeTypes);

            throw new InvalidArgumentException(
                "Invalid file type. Please upload $allowedMimeTypes."
            );
        }

        if ($file->getSize() > $this->maximumFileSizeBytes) {
            throw new InvalidArgumentException(
                "File size exceeds maximum allowed ({$this->maximumFileSizeMega}MB)"
            );
        }
    }

    /**
     * Store the actual Resume file
     */
    protected function storeResumeFile(File $file): string
    {
        $newFilename = $this->generateName($file);

        $file->move($this->targetDirectory, $newFilename);

        return $newFilename;
    }

    /**
     * Generates a new name for the uploaded file
     */
    protected function generateName(File $file): string
    {
        return Uuid::v4()->toRfc4122() . '.' . $file->guessExtension();
    }

    /**
     * Create a Resume entity from a file path
     */
    protected function createResumeEntity(string $filePath): Resume
    {
        return $this->resumeRepository->createResumeFromFilePath($filePath);
    }

    /**
     * Retrieve the actual Resume file
     */
    protected function getFile(Resume $resume): File
    {
        $filePath = $this->getAbsoluteFilePath($resume);

        return $this->createFileObject($filePath);
    }

    /**
     * Creates a File object
     */
    protected function createFileObject(string $path): File
    {
        return new File($path);
    }

    /**
     * Returns the absolute Resume file path
     */
    protected function getAbsoluteFilePath(Resume $resume): string
    {
        return $this->targetDirectory . $resume->getPath();
    }

    /**
     * Call the Python script to score the Resume
     * 
     * @codeCoverageIgnore
     */
    protected function callScript(File $file, array $tags): int
    {
        $command = $this->buildScriptCommand($file, $tags);
        
        $process = $this->createProcess($command);
        $this->runProcess($process);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return (int) $process->getOutput();
    }
    
    /**
     * Build the script command
     * 
     * @codeCoverageIgnore
     */
    protected function buildScriptCommand(File $file, array $tags): array
    {
        return array_merge(
            ['python3', $this->pythonScriptPath, $file->getPathname()],
            $tags
        );
    }
    
    /**
     * Create Process instance
     * 
     * @codeCoverageIgnore
     */
    protected function createProcess(array $command): Process
    {
        return new Process($command);
    }
    
    /**
     * Run the Process
     * 
     * @codeCoverageIgnore
     */
    protected function runProcess(Process $process): void
    {
        $process->run();
    }
}
