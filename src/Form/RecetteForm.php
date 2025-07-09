<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\RecetteIngredientType;



class RecetteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $isEdit = $options['is_edit'];

        $builder
            ->add('titre', TextType::class, ['label' => 'Titre de la recette'])
            ->add('description', TextareaType::class, [
                  'label' => 'Description',
                  'required' => false,
                  'attr' => ['rows' => 5]
                ])
            ->add('instructions',TextareaType::class,[
                'label' => 'Instructions',
                'attr' => ['rows' => 10],
                'required' => true,
                ])
            ->add('tempsPreparation', IntegerType::class, [
                  'label' => 'Temps de préparation (en minutes)',
                ])
            ->add('difficulte', ChoiceType::class, [
                  'choices' => [
                  'Facile' => 'facile',
                  'Moyenne' => 'moyenne',
                  'Difficile' => 'difficile',
                   ],
                  'placeholder' => 'Sélectionnez une difficulté',
                  'required' => false,
                  'attr' => ['class' => 'form-control'],
                ])
            ->add('portions', IntegerType::class, [
                  'label' => 'Nombre de portions',
                  'required' => false,
                ])
            ->add('tempsCuisson', IntegerType::class, [
                  'label' => 'Temps de cuisson (en minutes)',
                  'required' => false,
                ])
           
            ->add('image', FileType::class, [
                  'label' => 'Image (fichier PNG, JPG, etc.)',
                  'mapped' => false,
                  'required' => false,
                  'help' => 'Formats acceptés : .jpg, .png, .webp (max 2 Mo)'
                   ])
            
            ->add('recetteIngredients', CollectionType::class, [
                 'entry_type' => RecetteIngredientType::class,
                 'entry_options' => ['label' => false],
                 'allow_add' => true,
                 'allow_delete' => true,
                 'by_reference' => false,
                  'prototype' => true,
                 
            ])

        ;
            if ($isEdit) {
                  $builder->add('validation', CheckboxType::class, [
                                'required' => false,
                                'label' => 'Valider la recette',
                             ]);
                         }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
            'is_edit' => false,
        ]);
    }
}
