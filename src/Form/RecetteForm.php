<?php

namespace App\Form;

use App\Entity\Recette;
use App\Form\RecetteIngredientType;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RecetteForm extends AbstractType
{
    public function __construct(private readonly IngredientRepository $ingredientRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la recette',
            ])
            ->add('image', FileType::class, [
                'label'    => 'Image (fichier PNG, JPG, etc.)',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'           => '2M',
                        'mimeTypes'         => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage'  => 'Veuillez télécharger une image valide (jpeg, png, webp).',
                        'maxSizeMessage'    => "L'image ne doit pas dépasser 2 Mo.",
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4],
            ])
            ->add('tempsCuisson', IntegerType::class, [
                'label'    => 'Temps de cuisson (minutes)',
                'required' => false,
                'attr'     => ['min' => 0],
            ])
            ->add('recetteIngredients', CollectionType::class, [
                'entry_type'    => RecetteIngredientType::class,
                'entry_options' => [
                    'ingredient_repository' => $this->ingredientRepository,
                ],
                'allow_add'       => true,
                'allow_delete'     => true,
                'by_reference'     => false,
                'label'            => false,
                'prototype'        => true,
                'prototype_name'   => '__name__',
                'error_bubbling'   => false, // pour voir les erreurs de collection
            ])
            ->add('instructions', TextareaType::class, [
                'label' => 'Instructions',
                'attr'  => ['rows' => 6],
            ])
            ->add('tempsPreparation', IntegerType::class, [
                'label'    => 'Temps de préparation (minutes)',
                'required' => false,
                'attr'     => ['min' => 0],
            ])
            ->add('difficulte', ChoiceType::class, [
                'label'       => 'Difficulté',
                'choices'     => [
                    'Facile'    => 'Facile',
                    'Moyen'     => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
                'placeholder' => 'Sélectionnez une difficulté',
                'required'    => false,
            ])
            ->add('portions', IntegerType::class, [
                'label'    => 'Nombre de portions',
                'required' => false,
                'attr'     => ['min' => 1],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
