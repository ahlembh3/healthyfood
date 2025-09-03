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
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => ['placeholder' => 'Écrivez votre avis...']
            ]);

        // Ajoute le champ note uniquement si c’est pour une recette
        if ($options['is_recette']) {
            $builder->add('note', IntegerType::class, [
                'label' => 'Note sur 5',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 5],
                'empty_data' => null,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'is_recette' => false, // valeur par défaut
            'validation_groups' => ['Default'],
        ]);
    }
}
