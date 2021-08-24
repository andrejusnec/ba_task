<?php

namespace App\Tests\Unit\Form\Type;

use App\Entity\AddressBook;
use App\Form\AddressBookType;
use Symfony\Component\Form\Test\TypeTestCase;

class AddressBookTypeTest extends TypeTestCase
{
    /**
     * @test
     */
    public function testSubmitValidData()
    {
        $formData = [
            'name' => 'Test name',
            'number' => '+37060784616',
        ];

        $model = new AddressBook();

        $form = $this->factory->create(AddressBookType::class, $model);
        $expected = new AddressBook();
        $expected->setName('Test name');
        $expected->setNumber('+37060784616');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }

    /**
     * @test
     */
    public function testCustomFormView()
    {
        $addressBook = new AddressBook();
        $addressBook->setName('BA');
        $addressBook->setNumber('+37060784612');

        $view = $this->factory->create(AddressBookType::class, $addressBook)
            ->createView();

        $this->assertArrayHasKey('data', $view->vars);
        $this->assertSame($addressBook, $view->vars['data']);
    }
}
