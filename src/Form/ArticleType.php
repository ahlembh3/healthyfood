<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

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
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, webp).',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo.',
                    ]),
                ],
            ])
            ->add('source', TextType::class, [
                'label' => 'Source',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du média, livre, etc.'],
            ])

            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Bien-être' => 'Bien-être',
                    'Nutrition' => 'Nutrition',
                    'Plantes' => 'Plantes',
                    'Conseils' => 'Conseils',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Choisir une catégorie',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
