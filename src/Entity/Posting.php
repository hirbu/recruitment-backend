<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Enum\ExperienceLevel;
use App\Enum\JobType;
use App\Repository\PostingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['posting:read']],
    denormalizationContext: ['groups' => ['posting:write']],
    order: ['createdAt' => 'DESC'],
    paginationClientEnabled: false,
    paginationClientItemsPerPage: false,
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'iword_start',
    'jobType' => 'exact',
    'experienceLevel' => 'exact',
    'cities.id' => 'exact',
    'owner.id' => 'exact',
])]
class Posting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['posting:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['posting:read', 'posting:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['posting:read', 'posting:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['posting:read', 'posting:write'])]
    private ?string $fields = null;

    #[ORM\Column(type: 'string', enumType: JobType::class)]
    #[Groups(['posting:read', 'posting:write'])]
    private JobType $jobType;

    #[ORM\Column(type: 'string', enumType: ExperienceLevel::class)]
    #[Groups(['posting:read', 'posting:write'])]
    private ExperienceLevel $experienceLevel;

    /**
     * @var Collection<int, City>
     */
    #[ORM\ManyToMany(targetEntity: City::class, inversedBy: 'postings')]
    #[Groups(['posting:read', 'posting:write'])]
    private Collection $cities;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'postings')]
    #[Groups(['posting:read', 'posting:write'])]
    private Collection $tags;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'posting', orphanRemoval: true)]
    private Collection $applications;

    #[ORM\Column]
    #[Groups(['posting:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['posting:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'postings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['posting:read', 'posting:write'])]
    private ?User $owner = null;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFields(): ?string
    {
        return $this->fields;
    }

    public function setFields(string $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function getJobType(): JobType
    {
        return $this->jobType;
    }

    public function setJobType(JobType $jobType): void
    {
        $this->jobType = $jobType;
    }

    public function getExperienceLevel(): ExperienceLevel
    {
        return $this->experienceLevel;
    }

    public function setExperienceLevel(ExperienceLevel $experienceLevel): void
    {
        $this->experienceLevel = $experienceLevel;
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        $this->cities->removeElement($city);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        $this->applications->add($application);

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        $this->applications->removeElement($application);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    #[ORM\PrePersist]
    public function handlePrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function handlePreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
