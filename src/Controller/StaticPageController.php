<?php
namespace App\Controller;

use App\Form\ContactType;
use App\Model\ContactData;
use App\Service\HtmlCleaner; // si tu as ce service ; sinon retire et on fallback
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales', methods: ['GET'])]
    public function mentions(): Response {
        return $this->render('static/mentions_legales.html.twig');
    }

    #[Route('/contact', name: 'contact', methods: ['GET','POST'])]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        ?HtmlCleaner $cleaner = null
    ): Response {
        $data = new ContactData();
        $form = $this->createForm(ContactType::class, $data, [
            'attr' => [
                'novalidate' => 'novalidate',
                'aria-describedby' => 'privacy-note',
                'class' => 'needs-validation'
            ]
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Honeypot (si rempli => bot) : on "fait comme si" tout était OK
            if (!empty($data->website)) {
                $this->addFlash('success', 'Merci, votre message a bien été envoyé.');
                return $this->redirectToRoute('contact');
            }

            // Nettoyage côté serveur (sécurité XSS)
            $cleanMessage = $cleaner
                ? $cleaner->clean($data->message ?? '')
                : nl2br(htmlspecialchars($data->message ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

            // Adresse de destination (mets ça dans .env si tu veux : CONTACT_TO=...)
            $to = $this->getParameter('app.contact_to');


            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@sante-nature.local', 'Santé & Nature'))
                ->to($to)
                ->replyTo(new Address($data->email, $data->name ?? ''))
                ->subject('[Contact] '.$data->subject)
                ->htmlTemplate('email/contact_email.html.twig')
                ->textTemplate('email/contact_email.txt.twig')
                ->context([
                    'name'        => $data->name,
                    'user_email'  => $data->email,   // <-- renomme ici
                    'subject'     => $data->subject,
                    'message'     => $cleanMessage,
                ]);


            try {
                $mailer->send($email);
                $this->addFlash('success', 'Merci, votre message a bien été envoyé.');
                return $this->redirectToRoute('contact'); // PRG
            } catch (\Throwable $e) {
                // En DEV, affiche le détail pour diagnostiquer
                $this->addFlash('error', 'Erreur envoi: '.$e->getMessage());
            }

        }


        return $this->render('static/contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route('/confidentialite', name: 'confidentialite', methods: ['GET'])]
    public function confidentialite(): Response {
        return $this->render('static/confidentialite.html.twig');
    }
}
