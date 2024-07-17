<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\AdvertisementRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $repository): Response
    {
        $category_list = $repository->findAllByNameASC();

        return $this->render('category/index.html.twig', [
            'category_list' => $category_list,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show', requirements: ['id' => '\d+'])]
    public function show(AdvertisementRepository $repository, Category $category, PaginatorInterface $paginator, Request $request): Response
    {
        $advertisement_list = $repository->findByCategory($category);

        $pagination = $paginator->paginate($advertisement_list, $request->query->getInt('page', 1), 10);

        return $this->render('category/show.html.twig', [
            'advertisement_list' => $pagination,
            'cat' => $category,
        ]);
    }
}
