<?php

namespace App\Tests\Unit;

use App\Tests\Factory\PlanteFactory;
use App\Entity\Tisane;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

final class TisaneDomainTest extends KernelTestCase
{
    use Factories;

    public function test_precautions_effectives_agrege_celles_des_plantes_si_absentes_sur_tisane(): void
    {
        // Ici on persiste (create) — OK pour un test simple
        $p1 = PlanteFactory::new(['precautions' => 'Déconseillé aux enfants.'])->create();
        $p2 = PlanteFactory::new(['precautions' => 'Éviter pendant la grossesse.'])->create();

        $t = new Tisane();
        $t->setNom('Calmante');
        $t->setModePreparation('Infuser.');
        $t->addPlante($p1)->addPlante($p2);

        $eff = $t->getPrecautionsEffectives();

        $this->assertNotNull($eff);
        $this->assertStringContainsString('Déconseillé', $eff);
        $this->assertStringContainsString('grossesse', $eff);
    }
}
