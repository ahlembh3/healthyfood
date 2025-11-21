<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class RecetteNewTest extends WebTestCase
{
    public function testUserCanAddRecette(): void
    {
        // ARRANGE
        $client = static::createClient();
        $container = $client->getContainer(); // ✅ CORRIGÉ
        $em = $container->get('doctrine')->getManager();

        // 1) Utilisateur avec email unique
        $user = new Utilisateur();
        $user->setEmail('testadd_'.uniqid().'@user.fr')
            ->setNom('Testeur')
            ->setPrenom('Unit')
            ->setRoles(['ROLE_USER'])
            ->setPassword(
                $container->get('security.user_password_hasher')
                    ->hashPassword($user, 'Password123')
            );

        $em->persist($user);

        // 2) Ingrédient
        $ingredient = (new Ingredient())
            ->setNom('Tomate')
            ->setType('Légume')
            ->setUnite('g')
            ->setCalories(18);

        $em->persist($ingredient);
        $em->flush();

        // 3) Connexion
        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => $user->getEmail(),
            '_password' => 'Password123',
        ]);
        $client->followRedirect();

        // 4) Accès formulaire
        $crawler = $client->request('GET', '/recettes/new');
        $this->assertResponseIsSuccessful();

        // 5) Image fake
        $tmp = tempnam(sys_get_temp_dir(), 'img_');
        imagepng(imagecreatetruecolor(10, 10), $tmp);
        $file = new UploadedFile($tmp, 'photo.png', 'image/png', null, true);

        // ACT
        $form = $crawler->filter('form[name="recette_form"]')->form([
            'recette_form[titre]' => 'Recette AAA',
            'recette_form[description]' => 'Une bonne description',
            'recette_form[instructions]' => 'Faire ceci puis cela',
            'recette_form[tempsPreparation]' => 10,
            'recette_form[tempsCuisson]' => 5,
            'recette_form[difficulte]' => 'Facile',
            'recette_form[portions]' => 2,
            'recette_form[recetteIngredients][0][ingredient]' => $ingredient->getId(),
            'recette_form[recetteIngredients][0][quantite]' => 100,
        ]);

        $form['recette_form[image]']->upload($file);
        $client->submit($form);

        // ASSERT
        $this->assertResponseRedirects('/recettes/mes-recettes');
        $client->followRedirect();

        $recette = $em->getRepository(Recette::class)
            ->findOneBy(['titre' => 'Recette AAA']);

        $this->assertNotNull($recette);
        $this->assertSame($user->getId(), $recette->getUtilisateur()->getId());
    }
}
