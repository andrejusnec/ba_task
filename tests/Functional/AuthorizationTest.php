<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Generator;
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
     * @test
     * @dataProvider urlProvider
     */
    public function onlyRegisteredUsersCanEnterSite($url)
    {
        $this->client->request('GET', $url);
        $this->client->request('POST', '/address/1/edit');
        $this->assertResponseRedirects('/login');

    }

    /**
     * @test
     * @dataProvider urlProviderForUnauthorized
     */
    public function unauthorizedUsersCanAccess($url)
    {
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     * @dataProvider urlProvider
     */
    public function loggedUserCanAccessSite($url)
    {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('kilgor4ever@gmail.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function userCannotViewAnotherUsersAccount()
    {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('admin@admin.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/address/14/show'); //address id that doesn't belong to this user
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function userCanViewHisOwnAccount()
    {
        $container = static::getContainer();
        $testUser = $container->get(UserRepository::class)->findOneByEmail('kilgor4ever@gmail.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/address/14/show');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('td', 'Name');
    }


    /**
     * @return Generator
     */
    public function urlProvider(): Generator
    {
        yield ['/address/add'];
        yield ['/address/15/edit'];
        yield ['/address/15/show'];
        yield ['/login/resend_email_verification'];
        yield ['/querylist/share/15'];
        yield ['/query_list/all'];
        yield ['/query_list/1/show_received'];
        yield ['/query_list/6/show_sended'];
    }

    /**
     * @return Generator
     */
    public function urlProviderForUnauthorized(): Generator
    {
        yield ['/register'];
        yield ['/login'];
        yield ['/login/resend_email_verification'];
        yield ['/reset-password'];
    }

}