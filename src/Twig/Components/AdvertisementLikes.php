<?php

namespace App\Twig\Components;

use App\Entity\Advertisement;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AdvertisementLikes
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public Advertisement $advertisement;

    #[LiveProp(writable: true)]
    public ?User $user;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security
    ) {
    }

    public function getLikesCount(): int
    {
        return count($this->advertisement->getLikes());
    }

    public function isLikedByUser(): bool
    {
        $liked = false;
        if (null !== $this->user) {
            foreach ($this->advertisement->getLikes() as $like) {
                if ($like->getOwner()->getId() === $this->user->getId()) {
                    $liked = true;
                }
            }
        }

        return $liked;
    }

    #[LiveAction]
    public function toggleLike(): void
    {
        if (!$this->security->isGranted('USER_LIKE', $this->advertisement)) {
            return;
        }

        $entityManager = $this->managerRegistry->getManager();

        if ($this->isLikedByUser()) {
            foreach ($this->advertisement->getLikes() as $like) {
                if ($like->getOwner()->getId() === $this->user->getId()) {
                    $entityManager->remove($like);
                }
            }
        } else {
            $like = new Like();
            $like->setAdvertisement($this->advertisement);
            $like->setOwner($this->user);

            $this->advertisement->addLike($like);

            $entityManager->persist($like);
        }
        $entityManager->flush();
    }
}
