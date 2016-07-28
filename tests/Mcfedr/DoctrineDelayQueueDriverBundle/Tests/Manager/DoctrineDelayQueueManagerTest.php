<?php
/**
 * Created by mcfedr on 04/02/2016 10:22
 */

namespace Mcfedr\DoctrineDelayQueueDriverBundle\Tests\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mcfedr\DoctrineDelayQueueDriverBundle\Manager\DoctrineDelayQueueManager;
use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\QueueManagerBundle\Manager\QueueManagerRegistry;
use Mcfedr\QueueManagerBundle\Queue\Job;
use Symfony\Component\DependencyInjection\Container;

class DoctrineDelayQueueManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var DoctrineDelayQueueManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $repo;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->manager = new DoctrineDelayQueueManager([
            'entity_manager' => null,
            'default_manager' => 'default',
            'default_manager_options' => []
        ]);

        $this->repo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $this->entityManager->method('getRepository')
            ->with(DoctrineDelayJob::class)
            ->willReturn($this->repo);

        $doctrine = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $doctrine->method('getManager')
            ->with(null)
            ->willReturn($this->entityManager);

        $this->container = new Container();
        $this->container->set('doctrine', $doctrine);

        $this->manager->setContainer($this->container);
    }

    public function testPut()
    {
        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $job = $this->manager->put('test_worker', [], ['time' => new \DateTime()]);

        $this->assertEquals('test_worker', $job->getName());
    }

    public function testPutFast()
    {
        $job = $this->getMockBuilder(Job::class)->getMock();

        $registry = $this->getMockBuilder(QueueManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $registry
            ->expects($this->once())
            ->method('put')
            ->with('test_worker', [])
            ->willReturn($job);

        $this->container->set('mcfedr_queue_manager.registry', $registry);

        $putJob = $this->manager->put('test_worker', []);

        $this->assertEquals($job, $putJob);
    }

    public function testDelete()
    {
        $toDelete = new DoctrineDelayJob('test_worker', [], [], 'default', new \DateTime());

        $this->entityManager
            ->expects($this->once())
            ->method('contains')
            ->with($toDelete)
            ->willReturn(false);

        $fromRepo = new DoctrineDelayJob('test_worker', [], [], 'default', new \DateTime());

        $this->repo
            ->expects($this->once())
            ->method('find')
            ->willReturn($fromRepo);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($fromRepo);

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with($fromRepo);

        $this->manager->delete($toDelete);
    }

    public function testDeleteFromEM()
    {
        $toDelete = new DoctrineDelayJob('test_worker', [], [], 'default', new \DateTime());

        $this->entityManager
            ->expects($this->once())
            ->method('contains')
            ->with($toDelete)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($toDelete);

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with($toDelete);

        $this->manager->delete($toDelete);
    }

    /**
     * @expectedException \Mcfedr\QueueManagerBundle\Exception\NoSuchJobException
     */
    public function testInvalidDelete()
    {
        $toDelete = new DoctrineDelayJob('test_worker', [], [], 'default', new \DateTime());

        $this->entityManager
            ->expects($this->once())
            ->method('contains')
            ->with($toDelete)
            ->willReturn(false);

        $this->repo
            ->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->manager->delete($toDelete);
    }
}
