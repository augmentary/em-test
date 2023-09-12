<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected AbstractDatabaseTool $databaseTool;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        /** @var DatabaseToolCollection $col */
        $col = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $col->get();
    }
}
