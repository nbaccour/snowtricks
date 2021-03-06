<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photo', FileType::class,
                [
                    'label'       => 'Photo',
                    'mapped'      => false,
                    'required'    => false,
                    'attr'        => ['placeholder' => 'Votre photo'],
                    'constraints' => new File([
                        'maxSize'          => '1024k',
                        'mimeTypes'        => ['image/jpeg', 'image/jpg', 'image/png'],
                        'mimeTypesMessage' => 'Le fichier ne possède pas une extension valide ! Veuillez insérer une image en .jpg, .jpeg ou .png',
                    ]),
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
