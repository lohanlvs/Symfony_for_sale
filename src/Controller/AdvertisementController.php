<?php

namespace App\Controller;

use App\Entity\Advertisement;
use App\Entity\User;
use App\Form\AdvertisementType;
use App\Repository\AdvertisementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

class AdvertisementController extends AbstractController
{
    public function __construct(
        #[Target('advertisement_publishing')]
        private readonly WorkflowInterface $workflow
    ) {
    }

    #[Route('/advertisement', name: 'app_advertisement_index')]
    public function index(PaginatorInterface $paginator, AdvertisementRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search');

        $advertisement_list = $repository->queryAllByDate(
            null !== $search ? $search : ''
        );

        $pagination = $paginator->paginate($advertisement_list, $request->query->getInt('page', 1), 10);

        return $this->render('advertisement/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/advertisement/{id}', name: 'app_advertisement_show', requirements: ['id' => '\d+'])]
    #[IsGranted('USER_SHOW', 'advertisement')]
    public function show(#[MapEntity(expr: 'repository.findWithCategory(id)')] Advertisement $advertisement): Response
    {
        return $this->render('advertisement/show.html.twig', [
            'adv' => $advertisement,
        ]);
    }

    #[Route('/advertisement/new', name: 'app_advertisement_new')]
    public function new(Request $request, ManagerRegistry $managerRegistry)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $entityManager = $managerRegistry->getManager();
        $advertisement = new Advertisement();
        $form = $this->createForm(AdvertisementType::class, $advertisement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($advertisement);
            $entityManager->flush();

            return $this->redirectToRoute('app_advertisement_index');
        }

        return $this->render('advertisement/new.html.twig', ['form' => $form]);
    }

    #[Route('/advertisement/{id<\d+>}/edit', name: 'app_advertisement_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('USER_VIEW', 'advertisement')]
    public function edit(#[MapEntity(expr: 'repository.findWithCategory(id)')] Advertisement $advertisement, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $form = $this->createForm(AdvertisementType::class, $advertisement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_advertisement_show', ['id' => $advertisement->getId()]);
        }

        return $this->render('advertisement/edit.html.twig', ['form' => $form]);
    }

    #[Route('/advertisement/{id<\d+>}/delete', name: 'app_advertisement_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('USER_VIEW', 'advertisement')]
    public function delete(#[MapEntity(expr: 'repository.find(id)')] Advertisement $advertisement, ManagerRegistry $managerRegistry, Request $request): Response
    {
        $submittedToken = $request->request->get('token');
        $entityManager = $managerRegistry->getManager();

        if ($this->isCsrfTokenValid('delete-item', $submittedToken) && Advertisement::STATE_DRAFT === $advertisement->getCurrentState()) {
            $entityManager->remove($advertisement);
            $entityManager->flush();

            return $this->redirectToRoute('app_advertisement_index');
        } else {
            return $this->redirectToRoute('app_advertisement_show', ['id' => $advertisement->getId()]);
        }
    }

    #[Route('/advertisement/liked', name: 'app_advertisement_liked')]
    public function liked(PaginatorInterface $paginator, AdvertisementRepository $repository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /* @var $user User */
        $user = $this->getUser();

        $advertisements = $repository->queryLikedByUser($user);

        $pagination = $paginator->paginate($advertisements, $request->query->getInt('page', 1), 10);

        return $this->render('advertisement/liked.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/advertisement/workflow/publish/{id<\d+>}', name: 'app_advertisement_workflow_publish', requirements: ['id' => '\d+'])]
    #[IsGranted('USER_WORKFLOW_PUBLISH', 'advertisement')]
    public function workflow_publish(#[MapEntity(expr: 'repository.find(id)')] Advertisement $advertisement): Response
    {
        try {
            $this->workflow->apply($advertisement, Advertisement::TRANSITION_PUBLISH);

            return $this->redirectToRoute('app_advertisement_index');
        } catch (\Exception $exception) {
            return $this->redirectToRoute('app_advertisement_show', ['id' => $advertisement->getId()]);
        }
    }
}
