<?php

namespace App\Tests\Unit;

use App\Entity\AddressBook;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AddressBookEntityTest extends TestCase
{
    /**
     * @test
     */
    public function createAddressBookTest()
    {
        $user = new User();
        $user->setName('Test');
        $user->setEmail('testEmail@gmail.com');
        $user->setPhone('55555');
        $user->setPassword('0000');
        $user->setRoles(['ROLE_USER']);

        $addressBook = new AddressBook();
        $addressBook->setName('Test');
        $addressBook->setNumber('+37060784616');
        $this->assertEquals('Test', $addressBook->getName());
        $this->assertEquals('+37060784616', $addressBook->getNumber());
        $this->assertEquals(null, $user->getId());
        $addressBook->setUser($user);
        $this->assertEquals($user, $addressBook->getUser());
        $this->assertNotEmpty($addressBook->getUser());
    }
}
