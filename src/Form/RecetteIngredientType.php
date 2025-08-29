<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use App\Repository\IngredientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var IngredientRepository|null $ingredientRepo */
        $ingredientRepo = $options['ingredient_repository'];

        // 1) Types BDD (triés) => choices "valeur affichée" => "valeur envoyée"
        $types = $ingredientRepo ? $ingredientRepo->findDistinctTypes() : [];
        $typeChoices = !empty($types) ? array_combine($types, $types) : [];
        // Liste complète (triée) au chargement initial
        $allIngredients = $ingredientRepo
            ? $ingredientRepo->findBy([], ['nom' => 'ASC'])
            : [];



        $builder
            ->add('typeIngredient', ChoiceType::class, [
                'choices'     => $typeChoices,
                'placeholder' => 'Sélectionner un type',
                'mapped'      => false,
                'required'    => false,
                'attr'        => [
                    'class' => 'form-select ingredient-type',
                    'data-ingredient-type' => true,
                ],
            ])
            ->add('ingredient', EntityType::class, [
                'class'        => Ingredient::class,
                'choices'      => $allIngredients, // liste complète au départ
                'choice_label' => 'nom',
                'choice_attr'  => static function(Ingredient $ingredient) {
                    return [
                        'data-unit' => $ingredient->getUnite() ?: 'unité',
                        'data-type' => $ingredient->getType() ?: 'Autre',
                    ];
                },
                'placeholder'  => 'Sélectionner un ingrédient',
                'attr'         => ['class' => 'form-select ingredient-select'],
                'required'     => false,
            ])
            ->add('quantite', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control ingredient-quantite',
                    'placeholder' => 'Quantité',
                ],
            ])
        ;

        // --- Edition : si un ingrédient existe, on présélectionne le type et on filtre la liste
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($ingredientRepo, $typeChoices) {
            /** @var RecetteIngredient|null $data */
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data || !$data->getIngredient() || !$ingredientRepo) {
                return;
            }

            $ingredient = $data->getIngredient();
            $type = $ingredient->getType() ?: null;

            $form->add('typeIngredient', ChoiceType::class, [
                'choices'  => $typeChoices,
                'data'     => $type,
                'mapped'   => false,
                'required' => false,
                'placeholder' => 'Sélectionner un type',
                'attr' => [
                    'class' => 'form-select ingredient-type',
                    'data-ingredient-type' => true,
                ],
            ]);

            $filtered = $type
                ? $ingredientRepo->findBy(['type' => $type], ['nom' => 'ASC'])
                : $ingredientRepo->findBy([], ['nom' => 'ASC']);

            $form->add('ingredient', EntityType::class, [
                'class'        => Ingredient::class,
                'choices'      => $filtered,
                'data'         => $ingredient, // conserve la valeur existante
                'choice_label' => 'nom',
                'choice_attr'  => static function(Ingredient $i) {
                    return [
                        'data-unit' => $i->getUnite() ?: 'unité',
                        'data-type' => $i->getType() ?: 'Autre',
                    ];
                },
                'placeholder'  => 'Sélectionner un ingrédient',
                'attr'         => ['class' => 'form-select ingredient-select'],
                'required'     => false,
            ]);
        });

        // --- Soumission : re-filtre selon le type choisi (verrouille côté serveur)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($ingredientRepo) {
            if (!$ingredientRepo) return;

            $data = $event->getData();        // tableau des valeurs postées
            $form = $event->getForm();
            $type = $data['typeIngredient'] ?? null;

            $filtered = $type
                ? $ingredientRepo->findBy(['type' => $type], ['nom' => 'ASC'])
                : $ingredientRepo->findBy([], ['nom' => 'ASC']);

            $form->add('ingredient', EntityType::class, [
                'class'        => Ingredient::class,
                'choices'      => $filtered,
                'choice_label' => 'nom',
                'choice_attr'  => static function(Ingredient $i) {
                    return [
                        'data-unit' => $i->getUnite() ?: 'unité',
                        'data-type' => $i->getType() ?: 'Autre',
                    ];
                },
                'placeholder'  => 'Sélectionner un ingrédient',
                'attr'         => ['class' => 'form-select ingredient-select'],
                'required'     => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => RecetteIngredient::class,
            'ingredient_repository'  => null,
        ]);

        $resolver->setAllowedTypes('ingredient_repository', ['null', IngredientRepository::class]);
    }
}
