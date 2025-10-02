<?php
namespace App\Form;

use App\Model\ContactData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => ['autocomplete' => 'name', 'minlength' => 2],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre e-mail',
                'attr' => ['autocomplete' => 'email', 'inputmode' => 'email'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 6, 'minlength' => 10],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'J’accepte le traitement de mes données (voir Confidentialité)',
                'mapped' => true,
                'required' => true,
                'row_attr' => ['class' => 'form-check mb-3'],
            ])
            // Honeypot (anti-bot) — caché à l’écran mais présent dans le DOM
            ->add('website', HiddenType::class, [
                'mapped' => true,
                'required' => false,
                'attr' => [
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactData::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact',
        ]);
    }
}
