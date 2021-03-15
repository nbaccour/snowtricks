<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Entity\Image;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{


    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoryList()
    {

        $category = $this->categoryRepository->findAll();

        return $this->render("category/_list.html.twig", ['categoryList' => $category]);
    }


    /**
     * @Route("/category/{slug}", name="category_show")
     */
    public function show($slug, TrickRepository $trickRepository): Response
    {
        $category = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas");
        }


        $tricks = $trickRepository->findBy(['category' => $category]);
        return $this->render('category/show.html.twig',
            ['category' => $category, 'tricks' => $tricks]);
    }
}
