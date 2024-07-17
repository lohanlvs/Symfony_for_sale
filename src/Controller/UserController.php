<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdvertisementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/adv/{id}', name: 'app_user_adv', requirements: ['id' => '\d+'])]
    public function index(PaginatorInterface $paginator, AdvertisementRepository $repository, Request $request, User $id): Response
    {
        $advertisement_list = $repository->queryAllByDateAndUser($id);

        $pagination = $paginator->paginate($advertisement_list, $request->query->getInt('page', 1), 10);

        return $this->render('advertisement/userAdvIndex.html.twig', [
            'pagination' => $pagination,
            'user' => $id,
        ]);
    }

    #[Route('/user/advertisement/draft', name: 'app_user_draft')]
    public function draft(PaginatorInterface $paginator, AdvertisementRepository $repository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /* @var $user User */
        $user = $this->getUser();

        $advertisement_list = $repository->queryDraftByUser($user);

        $pagination = $paginator->paginate($advertisement_list, $request->query->getInt('page', 1), 10);

        return $this->render('advertisement/userAdvDraftIndex.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
