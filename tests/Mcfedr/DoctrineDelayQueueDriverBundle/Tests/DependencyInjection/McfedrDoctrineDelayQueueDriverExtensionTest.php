<?php

namespace Mcfedr\DoctrineDelayQueueDriverBundle\Tests\DependencyInjection;

use Mcfedr\DoctrineDelayQueueDriverBundle\Manager\DoctrineDelayQueueManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class McfedrDoctrineDelayQueueDriverExtensionTest extends WebTestCase
{
    public function testConfiguration()
    {
        $client = static::createClient();
        $this->assertTrue($client->getContainer()->has(DoctrineDelayQueueManager::class));
        $this->assertTrue($client->getContainer()->has('mcfedr_queue_manager.default'));
    }
}
