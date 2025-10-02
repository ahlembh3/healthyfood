<?php
namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactData
{
    #[Assert\NotBlank(message: "Votre nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 80)]
    public ?string $name = null;

    #[Assert\NotBlank(message: "Votre e-mail est obligatoire.")]
    #[Assert\Email(message: "Adresse e-mail invalide.")]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Le sujet est obligatoire.")]
    #[Assert\Length(min: 3, max: 120)]
    public ?string $subject = null;

    #[Assert\NotBlank(message: "Le message est obligatoire.")]
    #[Assert\Length(min: 10, max: 5000)]
    public ?string $message = null;

    #[Assert\IsTrue(message: "Vous devez accepter le traitement de vos données.")]
    public bool $consent = false;

    // Honeypot : doit rester vide
    #[Assert\Blank]
    public ?string $website = null;
}
