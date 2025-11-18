<?php

namespace App\Tests\Unit;

use App\Entity\Plante;
use App\Entity\Tisane;
use PHPUnit\Framework\TestCase;

final class TisaneDomainTest extends TestCase
{
    public function test_precautions_effectives_agrege_celles_des_plantes_si_absentes_sur_tisane(): void
    {
        // On crée deux plantes "en mémoire", sans base de données
        $p1 = (new Plante())
            ->setNomCommun('Plante 1')
            ->setNomScientifique('Plantae uno')
            ->setDescription('Une plante test.')
            ->setPartieUtilisee('Feuilles')
            ->setPrecautions('Déconseillé aux enfants.');

        $p2 = (new Plante())
            ->setNomCommun('Plante 2')
            ->setNomScientifique('Plantae duo')
            ->setDescription('Une autre plante test.')
            ->setPartieUtilisee('Fleurs')
            ->setPrecautions('Éviter pendant la grossesse.');

        // On crée la tisane et on lui associe les plantes
        $t = new Tisane();
        $t->setNom('Tisane calmante');
        $t->setModePreparation('Infuser 10 minutes dans une eau frémissante.');
        // IMPORTANT : on ne définit PAS de précautions sur la tisane
        // pour vérifier que la méthode agrège celles des plantes.
        $t->addPlante($p1);
        $t->addPlante($p2);

        // Act : on récupère les précautions « effectives »
        $eff = $t->getPrecautionsEffectives();

        // Assert : on vérifie que le texte est bien construit
        self::assertNotNull($eff, 'Les précautions effectives ne doivent pas être nulles.');
        self::assertStringContainsString('Déconseillé aux enfants', $eff);
        self::assertStringContainsString('grossesse', $eff);
    }
}
