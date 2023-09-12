<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommandTestCase extends KernelTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    public function setUp(): void
    {
        parent::setUp();

        /** @var DatabaseToolCollection $col */
        $col = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $col->get();
    }
}
