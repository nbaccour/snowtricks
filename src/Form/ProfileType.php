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
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
