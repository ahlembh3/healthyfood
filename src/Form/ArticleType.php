<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                 ->add('titre', TextType::class, [
                'label' => 'Titre de l’article',
            ])
             ->add('contenu', TextareaType::class, [
        'label' => 'Contenu',
        'attr' => ['rows' => 10],
    ])
             ->add('image', FileType::class, [
        'label' => 'Image (fichier PNG, JPG, etc.)',
        'mapped' => false,
        'required' => false,
    ])
    ->add('categorie', ChoiceType::class, [
        'label' => 'Catégorie',
        'choices' => [
            'Tisane' => 'tisane',
            'Plante' => 'plante',
            'Recette' => 'recette',
        ],
        'placeholder' => 'Choisir une catégorie',
    ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
