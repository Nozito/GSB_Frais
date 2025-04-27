<?php
namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserProperties()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles(['ROLE_VISITEUR']);
        $user->setPassword('hashed_password');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertContains('ROLE_VISITEUR', $user->getRoles());
        $this->assertEquals('hashed_password', $user->getPassword());
    }
}