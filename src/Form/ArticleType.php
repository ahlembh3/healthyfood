<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Length;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l’article',
                'trim'  => true,
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr'  => ['rows' => 10],
                'trim'  => true,
            ])
            ->add('image', FileType::class, [
                'label'    => 'Image (PNG, JPG, WEBP)',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize'          => '2M',
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, webp).',
                        'maxSizeMessage'   => 'L’image ne doit pas dépasser 2 Mo.',
                    ]),
                ],
            ])
            ->add('source', TextType::class, [
                'label'       => 'Source',
                'required'    => false,
                'trim'        => true,
                'attr'        => ['placeholder' => 'Nom du média, livre… ou une URL'],
                'help'        => 'Exemples : « Le Monde », « Rapport OMS 2023 » ou https://exemple.com/article',
                'constraints' => [
                    new Length(max: 255, maxMessage: '255 caractères maximum.'),
                ],
                // Si tu veux forcer la valeur vide à null côté modèle :
                'empty_data'  => '',
            ])
            ->add('categorie', ChoiceType::class, [
                'label'       => 'Catégorie',
                'choices'     => [
                    'Bien-être' => 'Bien-être',
                    'Nutrition' => 'Nutrition',
                    'Plantes'   => 'Plantes',
                    'Conseils'  => 'Conseils',
                    'Autre'     => 'Autre',
                ],
                'placeholder' => 'Choisir une catégorie',
                'required'    => true,
            ]);

        /**
         * Normalisation douce du champ "source":
         * - trim
         * - '' -> null
         * - si ça ressemble à une URL et commence par "www." -> on préfixe "https://"
         * - si ça commence par "//" -> on préfixe "https:"
         * Laisse tel quel si c’est juste un nom de source (texte libre).
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }

            if (array_key_exists('source', $data)) {
                $src = \is_string($data['source']) ? trim($data['source']) : '';

                if ($src === '') {
                    // persiste null dans l’entité (champ nullable)
                    $data['source'] = null;
                } else {
                    // heuristique "ça ressemble à une URL"
                    $looksUrl = preg_match('~^(?i)(https?://|//|www\.)~', $src) === 1;

                    if ($looksUrl) {
                        if (str_starts_with($src, 'www.')) {
                            $src = 'https://' . $src;
                        } elseif (str_starts_with($src, '//')) {
                            $src = 'https:' . $src;
                        }
                    }

                    // coupe proprement à 255 (UTF-8 safe) pour respecter la contrainte BD
                    if (function_exists('mb_substr')) {
                        $src = mb_substr($src, 0, 255, 'UTF-8');
                    } else {
                        $src = substr($src, 0, 255);
                    }

                    $data['source'] = $src;
                }
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
