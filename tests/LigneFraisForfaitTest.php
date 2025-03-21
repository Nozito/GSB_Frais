<?php

namespace App\Tests;

use App\Entity\FicheFrais;
use App\Entity\FraisForfait;
use App\Entity\LigneFraisForfait;
use PHPUnit\Framework\TestCase;

class LigneFraisForfaitTest extends TestCase
{
    public function testGetMontant()
    {
        $ficheFrais = new FicheFrais();
        $fraisForfait = new FraisForfait();
        $fraisForfait->setMontant(10.0);

        $ligneFraisForfait = new LigneFraisForfait($ficheFrais, $fraisForfait, 3);

        $this->assertEquals(30.0, $ligneFraisForfait->getMontant());

    }
}
