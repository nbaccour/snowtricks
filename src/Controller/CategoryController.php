<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
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

    public function getCategoryList(){

        $category = $this->categoryRepository->findAll();

        return $this->render("category/_list.html.twig", ['categoryList' =>$category]);
    }


//    /**
//     * @Route("/category/{slug}", name="show_category")
//     */
//    public function show(): Response
//    {
//        return $this->render('category/show.html.twig', [
//            'controller_name' => 'CategoryController',
//        ]);
//    }
}
