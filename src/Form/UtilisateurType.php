<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Symfony\Component\Validator\Constraints\NotBlank;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = (bool) ($options['is_edit'] ?? false);

        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => ['placeholder' => 'Prénom'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['placeholder' => 'Nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'disabled' => $isEdit, // on ne modifie pas l’email en édition
                'attr' => ['placeholder' => 'Email'],
            ])
            ->add('preferences', TextareaType::class, [
                'label' => 'Préférences',
                'required' => false,
                'attr' => ['placeholder' => 'Décrivez vos préférences...'],
            ]);

        if (!$isEdit) {
            // En création (inscription) : on affiche les champs liés au mot de passe.
            // ⚠ On laisse la COMPLEXITÉ au niveau ENTITÉ (groupe Registration).
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'mapped' => true,       // mappé sur Utilisateur::$password (texte en clair AVANT hash)
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                        'autocomplete' => 'new-password',
                    ],
                    // Pas de Regex ici, on la laisse sur l’entité via le groupe Registration
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    ],
                ])
                ->add('confirmPassword', PasswordType::class, [
                    'label' => 'Confirmer le mot de passe',
                    'mapped' => false,
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe',
                        'autocomplete' => 'new-password',
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez confirmer votre mot de passe.']),
                    ],
                ]);

            // Vérifier l’égalité des 2 mots de passe
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $pwd  = (string) $form->get('password')->getData();
                $pwd2 = (string) $form->get('confirmPassword')->getData();

                if ($pwd !== $pwd2) {
                    $form->get('confirmPassword')->addError(
                        new FormError('Les mots de passe ne correspondent pas.')
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
            'validation_groups' => function (FormInterface $form) {
                $isEdit = (bool) $form->getConfig()->getOption('is_edit', false);
                return $isEdit ? ['Default'] : ['Default', 'Registration'];
            },
        ]);
    }
}
