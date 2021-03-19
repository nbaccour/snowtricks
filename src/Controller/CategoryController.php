<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Entity\Image;
use App\Repository\TrickRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function show(
        $slug,
        TrickRepository $trickRepository,
        Request $request,
        PaginatorInterface $paginator

    ): Response {
        $category = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas");
        }


        $tricks = $trickRepository->findBy(['category' => $category]);

        $trickslist = $paginator->paginate(
            $tricks, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );
        return $this->render('category/show.html.twig',
            ['category' => $category, 'tricks' => $trickslist]);
//            ['category' => $category, 'tricks' => $tricks]);
    }
}
