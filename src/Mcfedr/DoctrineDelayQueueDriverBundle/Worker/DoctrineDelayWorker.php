<?php
/**
 * Created by mcfedr on 7/28/16 09:28
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Worker;

use Mcfedr\QueueManagerBundle\Exception\UnrecoverableJobException;
use Mcfedr\QueueManagerBundle\Queue\Worker;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DoctrineDelayWorker implements Worker, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Called to start the queued task
     *
     * @param array $arguments
     * @throws \Exception
     * @throws UnrecoverableJobException
     */
    public function execute(array $arguments)
    {
        $this->container->get("mcfedr_queue_manager.{$arguments['manager']}")->put($arguments['name'], $arguments['arguments'], $arguments['options']);
    }
}
