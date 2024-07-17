<?php

namespace App\Security\Voter;

use App\Entity\Advertisement;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Workflow\WorkflowInterface;

class AdvertisementVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const LIKE = 'USER_LIKE';
    public const SHOW = 'USER_SHOW';
    public const WORK_FLOW_PUBLISH = 'USER_WORKFLOW_PUBLISH';
    public const WORK_FLOW_CLOSE = 'USER_WORKFLOW_CLOSE';
    public const WORK_FLOW_ARCHIVE = 'USER_WORKFLOW_ARCHIVE';
    public const WORK_FLOW_REPUBLISH = 'USER_WORKFLOW_REPUBLISH';

    public function __construct(
        #[Target('advertisement_publishing')]
        private readonly WorkflowInterface $workflow
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::VIEW,
            self::LIKE,
            self::SHOW,
            self::WORK_FLOW_PUBLISH,
            self::WORK_FLOW_CLOSE,
            self::WORK_FLOW_ARCHIVE,
            self::WORK_FLOW_REPUBLISH,
        ])) {
            return false;
        }

        if (!$subject instanceof Advertisement) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User && self::SHOW !== $attribute) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $advertisement = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($advertisement, $user),
            self::LIKE => $this->canLike($advertisement, $user),
            self::SHOW => $this->canShow($advertisement, $user),
            self::WORK_FLOW_PUBLISH => $this->canWorkflow($advertisement, $user, Advertisement::TRANSITION_PUBLISH),
            self::WORK_FLOW_CLOSE => $this->canWorkflow($advertisement, $user, Advertisement::TRANSITION_CLOSE),
            self::WORK_FLOW_ARCHIVE => $this->canWorkflow($advertisement, $user, Advertisement::TRANSITION_ARCHIVE),
            self::WORK_FLOW_REPUBLISH => $this->canWorkflow($advertisement, $user, Advertisement::TRANSITION_REPUBLISH),
        };
    }

    private function canView(Advertisement $advertisement, User $user): bool
    {
        return $advertisement->getOwner()->getId() === $user->getId();
    }

    private function canLike(Advertisement $advertisement, User $user): bool
    {
        return !($advertisement->getOwner()->getId() === $user->getId());
    }

    private function canShow(Advertisement $advertisement, mixed $user): bool
    {
        $condition = false;

        if (Advertisement::STATE_PUBLISHED === $advertisement->getCurrentState()) {
            $condition = true;
        } else {
            if ($user instanceof User) {
                if ($advertisement->getOwner()->getId() === $user->getId()) {
                    $condition = true;
                    if (Advertisement::STATE_CLOSED === $advertisement->getCurrentState()) {
                        $condition = false;
                    }
                }
            }
        }

        return $condition;
    }

    private function canWorkflow(Advertisement $advertisement, User $user, string $transitionName): bool
    {
        $condition = false;
        if ($advertisement->getOwner()->getId() === $user->getId()) {
            $condition = $this->workflow->can($advertisement, $transitionName);
        }

        return $condition;
    }
}
