<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['city:read']]
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'iword_start'])]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['city:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['city:read', 'posting:read'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Posting>
     */
    #[ORM\ManyToMany(targetEntity: Posting::class, mappedBy: 'cities')]
    private Collection $postings;

    public function __construct()
    {
        $this->postings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Posting>
     */
    public function getPostings(): Collection
    {
        return $this->postings;
    }

    public function addPosting(Posting $posting): static
    {
        if (!$this->postings->contains($posting)) {
            $this->postings->add($posting);
            $posting->addCity($this);
        }

        return $this;
    }

    public function removePosting(Posting $posting): static
    {
        if ($this->postings->removeElement($posting)) {
            $posting->removeCity($this);
        }

        return $this;
    }
}
