<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class,
                [
                    'label'       => 'Nom',
                    'attr'        => ['placeholder' => 'Votre nom'],
                    'constraints' => new Length(['min' => '3', 'max' => '255', 'minMessage' => 'Nom Invalide']),
                ])
            ->add('prenom', TextType::class,
                [
                    'label'       => 'Prénom',
                    'attr'        => ['placeholder' => 'Votre prénom'],
                    'constraints' => new Length(['min' => '3', 'max' => '255', 'minMessage' => 'Prénom Invalide']),
                ])
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
            'data_class' => User::class,
        ]);
    }
}
