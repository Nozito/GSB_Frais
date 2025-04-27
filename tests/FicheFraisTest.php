<?php
namespace App\Tests;

use App\Entity\FicheFrais;
use PHPUnit\Framework\TestCase;

class FicheFraisTest extends TestCase
{
public function testCreateFicheFrais()
{
$ficheFrais = new FicheFrais();
$ficheFrais->setMois(new \DateTimeImmutable('2024-03-01'));
$ficheFrais->setMontantValid(200);

$this->assertEquals('2024-03-01', $ficheFrais->getMois()->format('Y-m-d'));
$this->assertEquals(200, $ficheFrais->getMontantValid());
}
}