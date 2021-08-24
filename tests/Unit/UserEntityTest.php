<?php

namespace App\Tests\Unit;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    /**
     * @test
     */
    public function createUserTest()
    {
        $user = new User();
        $user->setName('Test');
        $user->setEmail('testEmail@gmail.com');
        $user->setPhone('55555');
        $user->setPassword('0000');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(false);
        $addressBooks = $user->getAddressBooks();
        $this->assertEquals('Test', $user->getName());
        $this->assertEquals('testEmail@gmail.com', $user->getEmail());
        $this->assertEquals('55555', $user->getPhone());
        $this->assertEquals('0000', $user->getPassword());
        $this->assertIsArray($user->getRoles());
        $this->assertEmpty($addressBooks);
    }
}
