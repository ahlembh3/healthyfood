<?php
namespace App\Form;

use App\Entity\Tisane;
use App\Entity\Plante;
use App\Entity\Bienfait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class TisaneType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('nom')
->add('plantes', EntityType::class, [
'class' => Plante::class,
'choice_label' => 'nomCommun',
'multiple' => true,
'by_reference' => false,
'required' => false,
])
->add('bienfaits', EntityType::class, [
'class' => Bienfait::class,
'choice_label' => 'nom',
'multiple' => true,
'by_reference' => false,
'required' => false,
])
->add('modePreparation', TextareaType::class)
->add('dosage', TextareaType::class, ['required' => false])
->add('precautions', TextareaType::class, ['required' => false])
->add('imageFile', FileType::class, [
'mapped' => false,
'required' => false,
'constraints' => [
new FileConstraint([
'maxSize' => '2M',
'mimeTypes' => ['image/jpeg','image/png','image/webp'],
'mimeTypesMessage' => 'Formats autorisés : JPEG, PNG ou WEBP (max 2 Mo).',
]),
],
])
;
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults(['data_class' => Tisane::class]);
}
}
