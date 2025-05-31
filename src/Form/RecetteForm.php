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


class RecetteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $isEdit = $options['is_edit'];

        $builder
            ->add('titre')
            ->add('description')
            ->add('instructions')
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
            ->add('portions')
            ->add('valeursNutrition', TextType::class, [
            'required' => false,
            'label' => 'Valeurs nutritionnelles',
            'help' => 'Exemple de format : 500 kcal / 20g protéines / 10g lipides...',
            'attr' => ['class' => 'form-control'],
    ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
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
