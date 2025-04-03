<?php

namespace App\Service;

use App\Entity\Resume;
use App\Repository\ResumeRepository;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResumeService
{
    private int $maximumFileSizeBytes;

    public function __construct(
        private readonly ResumeRepository    $resumeRepository,
        private readonly HttpClientInterface $httpClient,

        private readonly string              $targetDirectory,
        private readonly string              $maximumFileSizeMega,
        private readonly array               $allowedMimeTypes,
        private readonly string              $openAIURL,
        private readonly string              $openAIKey,
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
     * Parse the Resume
     */
    public function parseResume(Resume $resume): string
    {
        $analysisRaw = $this->analyzeResumeUsingOpenAI($resume);

        $analysis = json_decode($analysisRaw, true);

        return $analysis['output'][0]['content'][0]['text'];
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
     * Converts the Resume PDF file to base64
     */
    private function resumePDFToBase64(Resume $resume): string
    {
        $file = $this->getFile($resume);

        $content = $file->getContent();

        return base64_encode($content);
    }

    private function analyzeResumeUsingOpenAI(Resume $resume): string
    {
        $response = $this->httpClient->request('POST', $this->openAIURL, [
            'headers' => $this->getResumeOpenAIHeaders(),
            'json' => $this->getResumeOpenAIRequest($resume),
        ]);

        return $response->getContent();
    }

    /**
     * Returns the OpenAI request for a given Resume
     */
    private function getResumeOpenAIRequest(Resume $resume): array
    {
        return [
            'model' => 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_file',
                            'filename' => 'resume.pdf',
                            'file_data' => "data:application/pdf;base64,{$this->resumePDFToBase64($resume)}"
                        ],
                        [
                            'type' => 'input_text',
                            'text' => "You are an expert at structured data extraction from resumes. 
                                       You will be given unstructured resume and should convert it into the given structure. 
                                       Leave fields empty if you cannot find the information."
                        ]
                    ]
                ]
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'resume_info',
                    'schema' => $this->getResumeSchema(),
                    'strict' => true
                ]
            ]
        ];
    }

    /**
     * Defines the schema for the Resume analysis
     */
    private function getResumeSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'description' => 'Candidate\'s email address'
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Candidate\'s phone number'
                ],
                'linkedin' => [
                    'type' => 'string',
                    'description' => 'URL to LinkedIn profile'
                ],
                'github' => [
                    'type' => 'string',
                    'description' => 'URL to GitHub profile'
                ],
                'website' => [
                    'type' => 'string',
                    'description' => 'URL to personal website or portfolio'
                ],
                'skills' => [
                    'type' => 'array',
                    'description' => 'List of skills',
                    'items' => [
                        'type' => 'string'
                    ]
                ],
                'experience' => [
                    'type' => 'array',
                    'description' => 'Work experience of the candidate',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'company' => ['type' => 'string'],
                            'title' => ['type' => 'string'],
                            'location' => ['type' => 'string'],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Key responsibilities and achievements. Summarize it all in 100 words.'
                            ],
                            'start_date' => [
                                'type' => 'string',
                                'description' => 'Format YYYY-MM or Month YYYY'
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'description' => 'Format YYYY-MM or Month YYYY or \'Present\' if the candidate is currently working there'
                            ]
                        ],
                        'required' => ['company', 'title', 'location', 'description', 'start_date', 'end_date'],
                        'additionalProperties' => false
                    ]
                ],
                'education' => [
                    'type' => 'array',
                    'description' => 'Educational background of the candidate',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'school' => ['type' => 'string'],
                            'degree' => [
                                'type' => 'string',
                                'description' => 'Degree and field of study'
                            ],
                            'location' => ['type' => 'string'],
                            'start_date' => [
                                'type' => 'string',
                                'description' => 'Format YYYY or Month YYYY'
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'description' => 'Format YYYY or Month YYYY'
                            ]
                        ],
                        'required' => ['school', 'degree', 'location', 'start_date', 'end_date'],
                        'additionalProperties' => false
                    ]
                ],
                'other' => [
                    'type' => 'string',
                    'description' => 'Any other relevant information like projects, certifications, etc. Summarize it all in 200 words.'
                ]
            ],
            'required' => ['email', 'phone', 'linkedin', 'github', 'website', 'skills', 'experience', 'education', 'other'],
            'additionalProperties' => false
        ];
    }

    /**
     * Returns the OpenAI headers for a given Resume
     */
    private function getResumeOpenAIHeaders(): array
    {
        return [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->openAIKey,
        ];
    }
}
