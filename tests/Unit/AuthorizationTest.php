<?php

namespace App\Tests\Unit;

use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class AuthorizationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     *@test
     */
    public function only_registered_users_can_enter_site() {
        $this->client->request('GET', '/addresses');
        $this->client->request('POST', '/address/1/edit');
        $this->assertResponseRedirects('/login');

    }

    /**
     * @test
     * @throws NonUniqueResultException
     */
    public function logged_user_can_access_site() {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('kilgor4ever@gmail.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/address/add');
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     * @throws NonUniqueResultException
     */
    public function user_cannot_view_another_user_account() {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('admin@admin.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/address/14/show');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     * @throws NonUniqueResultException
     */
    public function user_can_view_his_own_account() {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('admin@admin.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/address/14/show');
        $this->assertResponseStatusCodeSame(403);
    }

}