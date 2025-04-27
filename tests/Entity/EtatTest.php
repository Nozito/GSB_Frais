<?php
namespace App\Tests\Entity;

use App\Entity\Etat;
use PHPUnit\Framework\TestCase;

class EtatTest extends TestCase
{
    public function testEtatProperties()
    {
        $etat = new Etat();
        $etat->setLibelle('VA');

        $this->assertEquals('VA', $etat->getLibelle());
    }
}