<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var IngredientRepository $ingredientRepo */
        $ingredientRepo = $options['ingredient_repository'];
        $ingredients = $ingredientRepo->findAll(); // ← CORRECTION ICI

        $builder
            ->add('typeIngredient', ChoiceType::class, [
                'choices' => [
                    'Fruits' => 'Fruits',
                    'Fruit sec' => 'Fruit sec',
                    'Légumes' => 'Légumes',
                    'Céréales & féculents' => 'Céréales & féculents',
                    'Épice' => 'Épice',
                    'Légumineuses' => 'Légumineuses',
                    'Produits laitiers' => 'Produits laitiers',
                    'Viandes & substituts' => 'Viandes & substituts',
                    'Poissons & fruits de mer' => 'Poissons & fruits de mer',
                    'Œufs' => 'Œufs',
                    'Huiles, matières grasses & graines' => 'Huiles, matières grasses & graines',
                    'Épices, herbes & condiments' => 'Épices, herbes & condiments',
                    'Produits sucrants' => 'Produits sucrants',
                    'Boissons' => 'Boissons',
                ],
                'placeholder' => 'Sélectionner un type',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-select ingredient-type',
                    'data-ingredient-type' => true
                ]
            ])
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choices' => $ingredients, // ← MAINTENANT $ingredients EST DÉFINI
                'choice_label' => 'nom',
                'choice_attr' => function(Ingredient $ingredient) {
                    return [
                        'data-unit' => $ingredient->getUnite() ?: 'unité',
                        'data-type' => $ingredient->getType() ?: 'Autre'
                    ];
                },
                'placeholder' => 'Sélectionner un ingrédient',
                'attr' => ['class' => 'form-select ingredient-select'],
                'required' => false
            ])
            ->add('quantite', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control ingredient-quantite',
                    'placeholder' => 'Quantité'
                ],
            ]);

        // Événement pour pré-remplir le type si l'ingrédient est déjà sélectionné
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($ingredientRepo) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data && $data->getIngredient()) {
                $ingredient = $data->getIngredient();
                $type = $ingredient->getType() ?: 'Autre';

                $form->add('typeIngredient', ChoiceType::class, [
                    'choices' => [
                        'Fruits' => 'Fruits',
                        'Fruit sec' => 'Fruit sec',
                        'Légumes' => 'Légumes',
                        'Céréales & féculents' => 'Céréales & féculents',
                        'Épice' => 'Épice',
                        'Légumineuses' => 'Légumineuses',
                        'Produits laitiers' => 'Produits laitiers',
                        'Viandes & substituts' => 'Viandes & substituts',
                        'Poissons & fruits de mer' => 'Poissons & fruits de mer',
                        'Œufs' => 'Œufs',
                        'Huiles, matières grasses & graines' => 'Huiles, matières grasses & graines',
                        'Épices, herbes & condiments' => 'Épices, herbes & condiments',
                        'Produits sucrants' => 'Produits sucrants',
                        'Boissons' => 'Boissons',
                    ],
                    'data' => $type,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-select ingredient-type',
                        'data-ingredient-type' => true
                    ]
                ]);

                // Filtrer les ingrédients par type pour l'édition
                $ingredients = $ingredientRepo->findBy(['type' => $type]);
                $form->add('ingredient', EntityType::class, [
                    'class' => Ingredient::class,
                    'choices' => $ingredients,
                    'data' => $ingredient,
                    'choice_label' => 'nom',
                    'choice_attr' => function(Ingredient $ingredient) {
                        return [
                            'data-unit' => $ingredient->getUnite() ?: 'unité',
                            'data-type' => $ingredient->getType() ?: 'Autre'
                        ];
                    },
                    'attr' => ['class' => 'form-select ingredient-select'],
                    'required' => false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecetteIngredient::class,
            'ingredient_repository' => null,
        ]);

        $resolver->setAllowedTypes('ingredient_repository', ['null', IngredientRepository::class]);
    }
}