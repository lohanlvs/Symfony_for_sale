<?php

namespace App\Entity;

use App\Repository\AdvertisementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertisementRepository::class)]
class Advertisement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(
        min: 10,
        max: 100,
        minMessage: 'The title must be at least 10 characters long.',
        maxMessage: 'The title cannot be longer than 100 characters.'
    )]
    #[Assert\NotNull]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        min: 20,
        max: 1000,
        minMessage: 'The description must be at least 20 characters long.',
        maxMessage: 'The description cannot be longer than 1000 characters.'
    )]
    #[Assert\NotNull]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $price = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: ['title', 'description', 'price', 'location'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'The location must be at least 2 characters long.',
        maxMessage: 'The location cannot be longer than 100 characters.'
    )]
    #[Assert\NotNull]
    private ?string $location = null;

    #[ORM\ManyToOne(inversedBy: 'advertisements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(inversedBy: 'advertisements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'advertisement', targetEntity: Like::class, orphanRemoval: true)]
    private Collection $likes;

    #[ORM\Column(length: 50)]
    private ?string $currentState = self::STATE_DRAFT;

    public const STATE_DRAFT = 'draft';
    public const STATE_PUBLISHED = 'published';
    public const STATE_CLOSED = 'closed';
    public const STATE_ARCHIVED = 'archived';
    public const TRANSITION_PUBLISH = 'publish';
    public const TRANSITION_CLOSE = 'close';
    public const TRANSITION_ARCHIVE = 'archive';
    public const TRANSITION_REPUBLISH = 'republish';

    public function __construct()
    {
        $this->likes = new ArrayCollection();
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setAdvertisement($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getAdvertisement() === $this) {
                $like->setAdvertisement(null);
            }
        }

        return $this;
    }

    public function getCurrentState(): ?string
    {
        return $this->currentState;
    }

    public function setCurrentState(string $currentState): static
    {
        $this->currentState = $currentState;

        return $this;
    }

    public function getMarking(): array
    {
        return [$this->currentState];
    }
}
