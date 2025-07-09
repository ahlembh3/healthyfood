<?php
namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('typeIngredient', ChoiceType::class, [
            'choices' => [
                'Fruit' => 'Fruit',
                'Fruit sec' => 'Fruit sec',
                'Légume' => 'Légume',
                'Herbe aromatique' => 'Herbe aromatique',
                'Céréale' => 'Céréale',
                'Produit laitier' => 'Produit laitier',
                'Produit animal' => 'Produit animal',
                'Viande' => 'Viande',
                'Poisson' => 'Poisson',
                'Sucre naturel' => 'Sucre naturel',
            ],
            'placeholder' => 'Sélectionnez un type',
            'label' => 'Type d\'ingrédient',
            'mapped' => false,
            'attr' => ['class' => 'form-control ingredient-type'],
        ])
        ->add('ingredient', EntityType::class, [
            'class' => Ingredient::class,
            'choices' => [], // rempli dynamiquement via JS
            'placeholder' => 'Sélectionnez un ingrédient',
            'attr' => ['class' => 'form-control ingredient-select']
        ])
        ->add('quantite', IntegerType::class, [
            'attr' => ['class' => 'form-control'],//class html special
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecetteIngredient::class,
        ]);
    }
}
