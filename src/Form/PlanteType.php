<?php

namespace App\Form;

use App\Entity\Plante;
use App\Entity\Bienfait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PlanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCommun', TextType::class, [
                'label' => 'Nom commun',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom commun est obligatoire.']),
                    new Length(['max' => 255]),
                ],
                'attr' => ['maxlength' => 255, 'autocomplete' => 'off'],
            ])
            ->add('nomScientifique', TextType::class, [
                'label' => 'Nom scientifique',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom scientifique est obligatoire.']),
                    new Length(['max' => 255]),
                ],
                'attr' => ['maxlength' => 255, 'autocomplete' => 'off'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                ],
                'attr' => ['rows' => 5],
            ])
            ->add('partieUtilisee', TextType::class, [
                'label' => 'Partie utilisée',
                'constraints' => [
                    new NotBlank(['message' => 'La partie utilisée est obligatoire.']),
                    new Length(['max' => 255]),
                ],
                'attr' => ['maxlength' => 255],
            ])
            ->add('precautions', TextareaType::class, [
                'label' => 'Précautions',
                'constraints' => [
                    new NotBlank(['message' => 'Les précautions sont obligatoires.']),
                ],
                'attr' => ['rows' => 4],
            ])
            ->add('bienfaits', EntityType::class, [
                'class' => Bienfait::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'by_reference' => false,
                'required' => false,
                'label' => 'Bienfaits',
                'attr' => ['class' => 'form-select', 'data-placeholder' => 'Sélectionner des bienfaits'],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (jpeg/png/webp, 2 Mo max)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg','image/png','image/webp'],
                        'mimeTypesMessage' => 'Formats autorisés : JPEG, PNG ou WEBP (max 2 Mo).',
                    ]),
                ],
                'attr' => ['accept' => 'image/*'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Plante::class]);
    }
}
