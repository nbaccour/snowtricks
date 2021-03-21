<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use App\Form\ImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class,
                [
                    'label'    => 'Nom de la Figure',
                    'attr'     => ['placeholder' => 'Taper le nom de la figure'],
                    'required' => false,
                ])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('category', EntityType::class,
                [
                    'placeholder'  => '-- choisir une catégorie --',
                    'class'        => Category::class,
                    'choice_label' => 'name',
                ])
            ->add('video', TextType::class,
                [
                    'label'    => 'Nom de la Video : (exemple : https://www.youtube.com/embed/V9xuy-rVj9w)',
                    'attr'     => ['placeholder' => "Taper l'url de la video"],
                    'required' => false,
                    'mapped'   => false,
                ])
            ->add('image', FileType::class,
                [
                    'label'    => 'Ajouter Une à quatre images pour la figure',
                    'multiple' => true,
                    'mapped'   => false,
                    'required' => false,
                ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
