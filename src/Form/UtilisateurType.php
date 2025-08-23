<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;


class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => ['placeholder' => 'Prénom']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['placeholder' => 'Nom']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'disabled' => $isEdit,
                'attr' => ['placeholder' => 'Email']
            ])
            ->add('preferences', TextareaType::class, [
                'label' => 'Préférences',
                'required' => false,
                'attr' => ['placeholder' => 'Décrivez vos préférences...']
            ]);

        if (!$isEdit) {
            // Pour l'inscription
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'mapped' => true,
                    'required' => true,
                    'attr' => ['placeholder' => 'Mot de passe'],
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    ],
                ])
                ->add('confirmPassword', PasswordType::class, [
                    'label' => 'Confirmer le mot de passe',
                    'mapped' => false,
                    'required' => true,
                    'attr' => ['placeholder' => 'Confirmez votre mot de passe'],
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez confirmer votre mot de passe.']),
                    ],
                ]);
                 //  Ajout du validateur des mots de passe
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                      $form = $event->getForm();
                      $password = $form->get('password')->getData();
                      $confirmPassword = $form->get('confirmPassword')->getData();

                     if ($password !== $confirmPassword) {
                         $form->get('confirmPassword')->addError(
                        new FormError("Les mots de passe ne correspondent pas.")
                        );
                      }
                 });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'is_edit' => false,
        ]);
    }
}
