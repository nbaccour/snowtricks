<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;


    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="image")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $trick;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

//-----------------------------
//
//    public function getUploadDir()
//    {
//        // Upload directory
//        return 'uploads/trick/';
//        // This means /web/uploads/documents/
//    }

//    protected function getUploadRootDir()
//    {
//        // On retourne le chemin relatif vers l'image pour notre code PHP
//        // Image location (PHP)
//        return __DIR__ . '/../../../../public/' . $this->getUploadDir();
//    }


//-----------------------------

}
