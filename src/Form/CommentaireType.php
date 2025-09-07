<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Cas recette : contenu optionnel (empty_data=null)
        // Cas article : contenu requis (empty_data='') pour que NotBlank côté entité s'applique
        $builder->add('contenu', TextareaType::class, [
            'label'       => 'Votre commentaire',
            'required'    => !$options['is_recette'],               // requis seulement pour un article
            'empty_data'  => $options['is_recette'] ? null : '',    // null pour recette (autorise "note seule")
            'attr'        => [
                'placeholder' => 'Écrivez votre avis…',
                'rows'        => 4,
            ],
            // trim est true par défaut, garde-le ainsi pour transformer "   " en ""
        ]);

        // Ajoute la note uniquement pour une recette
        if ($options['is_recette']) {
            $builder->add('note', IntegerType::class, [
                'label'       => 'Note sur 5',
                'required'    => false,
                'empty_data'  => null, // si vide → null (cohérent avec ta logique dans le contrôleur)
                'attr'        => [
                    'min'       => 0,
                    'max'       => 5,
                    'step'      => 1,
                    'inputmode' => 'numeric',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Commentaire::class,
            'is_recette'        => false, // par défaut : commentaire d’article
            'validation_groups' => ['Default'],
        ]);
    }
}
