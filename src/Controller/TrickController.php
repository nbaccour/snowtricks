<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\Image;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TrickController extends AbstractController
{


    protected $trickRepository;
    protected $imageRepository;
    protected $manager;
    protected $slugger;
    protected $commentRepository;

    public function __construct(
        TrickRepository $trickRepository,
        EntityManagerInterface $manager,
        SluggerInterface $slugger,
        ImageRepository $imageRepository,
        CommentRepository $commentRepository
    ) {
        $this->trickRepository = $trickRepository;
        $this->manager = $manager;
        $this->slugger = $slugger;
        $this->imageRepository = $imageRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/createtrick", name="trick_create")
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos figures")
     */
    public function create(Request $request, SluggerInterface $slugger)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trickName = $this->trickRepository->findByName($trick->getName());
            if (count($trickName) !== 0) {
                $this->addFlash("warning",
                    "Veuillez modifier le nom de la figure : '" . $trick->getName() . "' Ce Nom existe déjà dans la base");
                return $this->redirectToRoute("trick_create");
            }
        }


        $create = $this->createOrUpdate($form, $trick);
        if ($create === true) {
            return $this->redirectToRoute("trick_mytricks");
        }


        return $this->render('/trick/create.html.twig', ['formView' => $form->createView(), 'trick' => $trick]);
    }

    /**
     * @Route("/{category_slug}/{slug}", name="trick_show", priority=-1)
     */
    public function show($slug, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
//        dd($trick);
        $imagesTrick = $this->imageRepository->findByTrick($trick->getId());
        $oListImage = [];
        foreach ($imagesTrick as $key => $value) {
            if ($value->getId() !== $trick->getMainImage()->getId()) {
                $oListImage[$key] = $value;
            }
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setUser($user)
                ->setTrick($trick)
                ->setCreateDate(new \DateTime())
                ->setIsvalid(1);

            $this->manager->persist($comment);
            $this->manager->flush();
        }


        $comments = $this->commentRepository->findByTrick($trick->getId());

        $commentslist = $paginator->paginate(
            $comments, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );

        return $this->render('trick/show.html.twig',
            [
                'trick'    => $trick,
                'images'   => $oListImage,
                'comments' => $commentslist,
                'formView' => $form->createView(),
            ]);
    }

    /**
     * @Route("/mytricks", name="trick_mytricks")
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos figures")
     */
    public function mytricks()
    {

        $user = $this->getUser();
//        dd($user->getTrick());
        return $this->render('/user/mytricks.html.twig', ['tricks' => $user->getTrick()]);


    }

    /**
     * @param $form
     * @param $trick
     * @param string $type
     * @return bool
     */
    public function createOrUpdate($form, $trick, string $type = 'create')
    {

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute("security_login");
        }

        $return = false;
        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('image')->getData();

            foreach ($images as $image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);

                $bddFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
//                $pathFilename = 'uploads/trick/' . $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
                $pathFilename = 'uploads/trick/' . $bddFilename;

                try {
                    $image->move(
                        $this->getParameter('imgTrick_directory'),
                        $pathFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash(
                        'error',
                        "Erreur téléchargement de votre photo"
                    );
                }
                // On crée l'image dans la base de données
                $img = new Image();
                $img->setName($bddFilename)
                    ->setTrick($trick);

                $trick->addImage($img)
                    ->setMainImage($img);

                $this->manager->persist($img);

            }
            $trick->setSlug($this->slugger->slug($trick->getName()))
                ->setUser($user);

            $this->manager->persist($trick);

            $this->manager->flush();
            $msg = ($type === 'create') ? "Votre figure a été ajoutée" : "Votre figure a été modifiée";
            $this->addFlash('success', $msg);

            $return = true;
        }
        return $return;
    }


    /**
     * @Route("/modifymytrick/{id}", name="trick_modify")
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos figures")
     */
    public function modify($id, Request $request)
    {
        $trick = $this->trickRepository->find($id);

        $imagesTrick = $this->imageRepository->findByExampleField($trick->getId());
        $oListImage = [];
        foreach ($imagesTrick as $key => $value) {
            if ($value->getId() !== $trick->getMainImage()->getId()) {
                $oListImage[$key] = $value;
            }
        }

        if (!$trick) {
            throw $this->createNotFoundException("La figure $id n'existe pas");
        }
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        $modify = $this->createOrUpdate($form, $trick, 'modify');
        if ($modify === true) {
            return $this->redirectToRoute("trick_mytricks");
        }


        return $this->render('/trick/modify.html.twig',
            ['formView' => $form->createView(), 'trick' => $trick, 'images' => $oListImage]);


    }

    /**
     * @Route("/deletepicturetrick/{id}", name="trick_delete_picture")
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos figures")
     */
    public function deletePicture($id, ImageRepository $imageRepository)
    {

        $image = $imageRepository->find($id);

        $filesystem = new Filesystem();
        $filesystem->remove('/uploads/trick/' . $image->getName());
//        dd($image->getTrick()->getId());

        $this->manager->remove($image);
        $this->manager->flush();

        $this->addFlash("warning", "L'image a bien été suprimée ");

        return $this->redirectToRoute("trick_modify", ['id' => $image->getTrick()->getId()]);
    }

    /**
     * @Route("/deletemytrick/{id}", name="trick_delete", requirements={"id": "\d+"})
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos figures")
     */
    public function delete($id)
    {

        $trick = $this->trickRepository->find($id);

        if (!$trick) {
            throw $this->createNotFoundException("La figure $id n'existe pas");
        }

        $filesystem = new Filesystem();

        foreach ($trick->getImage() as $image) {


            try {
                $filesystem->remove('/uploads/trick/' . $image->getName());

            } catch (IOExceptionInterface $exception) {
                $this->addFlash(
                    'warning',
                    "Erreur sur la suppression de la photo"
                );
            }
        }
        $imagesTrick = $trick->getImage();

        foreach ($imagesTrick as $image) {
            $this->manager->remove($image);

        }
        $this->manager->remove($trick);
        $this->manager->flush();

        $this->addFlash("warning", "La figure a bien été suprimée ");

        return $this->redirectToRoute("trick_mytricks");
    }

}
