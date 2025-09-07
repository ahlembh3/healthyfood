<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use App\Repository\IngredientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var IngredientRepository|null $ingredientRepo */
        $ingredientRepo = $options['ingredient_repository'];

        $types       = $ingredientRepo ? $ingredientRepo->findDistinctTypes() : [];
        $typeChoices = !empty($types) ? array_combine($types, $types) : [];
        $all         = $ingredientRepo ? $ingredientRepo->findBy([], ['nom' => 'ASC']) : [];

        $builder
            ->add('typeIngredient', ChoiceType::class, [
                'choices'     => $typeChoices,
                'placeholder' => 'Sélectionner un type',
                'mapped'      => false,
                'required'    => false,
                'attr'        => ['class' => 'form-select ingredient-type', 'data-ingredient-type' => true],
            ])
            ->add('ingredient', EntityType::class, [
                'class'        => Ingredient::class,
                'choices'      => $all,
                'choice_label' => 'nom',
                'choice_attr'  => static function(Ingredient $i) {
                    return [
                        'data-unit' => $i->getUnite() ?: 'unité',
                        'data-type' => $i->getType() ?: 'Autre',
                    ];
                },
                'placeholder'  => 'Sélectionner un ingrédient',
                'attr'         => ['class' => 'form-select ingredient-select'],
                'required'     => true,
                'constraints'  => [new Assert\NotNull(message: 'Choisissez un ingrédient.')],
            ])
            ->add('quantite', NumberType::class, [
                'required'   => true,
                'scale'      => 2,
                'empty_data' => '100',
                'html5'      => true,
                'attr'       => ['min' => '0.01', 'step' => '0.01', 'class' => 'form-control ingredient-quantite'],
                'constraints'=> [
                    new Assert\NotNull(message: 'La quantité est obligatoire.'),
                    new Assert\Positive(message: 'La quantité doit être > 0.'),
                ],
            ])
        ;

        // Pré-sélection du type en édition
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($ingredientRepo, $typeChoices) {
            $data = $event->getData();
            if (!$data || !$data->getIngredient() || !$ingredientRepo) return;

            $form       = $event->getForm();
            $ingredient = $data->getIngredient();
            $type       = $ingredient->getType() ?: null;

            $form->add('typeIngredient', ChoiceType::class, [
                'choices'     => $typeChoices,
                'data'        => $type,
                'mapped'      => false,
                'required'    => false,
                'placeholder' => 'Sélectionner un type',
                'attr'        => ['class' => 'form-select ingredient-type', 'data-ingredient-type' => true],
            ]);

            $filtered = $type
                ? $ingredientRepo->findBy(['type' => $type], ['nom' => 'ASC'])
                : $ingredientRepo->findBy([], ['nom' => 'ASC']);

            $form->add('ingredient', EntityType::class, [
                'class'        => Ingredient::class,
                'choices'      => $filtered,
                'data'         => $ingredient,
                'choice_label' => 'nom',
                'choice_attr'  => static function(Ingredient $i) {
                    return [
                        'data-unit' => $i->getUnite() ?: 'unité',
                        'data-type' => $i->getType() ?: 'Autre',
                    ];
                },
                'placeholder'  => 'Sélectionner un ingrédient',
                'attr'         => ['class' => 'form-select ingredient-select'],
                'required'     => true,
                'constraints'  => [new Assert\NotNull(message: 'Choisissez un ingrédient.')],
            ]);
        });

        // Re-filtrage serveur à la soumission
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($ingredientRepo) {
            if (!$ingredientRepo) return;

            $data     = $event->getData();
            $form     = $event->getForm();
            $type     = $data['typeIngredient'] ?? null;

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
                'required'     => true,
                'constraints'  => [new Assert\NotNull(message: 'Choisissez un ingrédient.')],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'            => RecetteIngredient::class,
            'ingredient_repository' => null,
        ]);
        $resolver->setAllowedTypes('ingredient_repository', ['null', IngredientRepository::class]);
    }
}
