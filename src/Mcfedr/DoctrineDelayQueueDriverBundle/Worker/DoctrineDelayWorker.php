<?php
/**
 * Created by mcfedr on 7/28/16 09:28
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Worker;

use Mcfedr\QueueManagerBundle\Exception\UnrecoverableJobException;
use Mcfedr\QueueManagerBundle\Manager\QueueManagerRegistry;
use Mcfedr\QueueManagerBundle\Queue\Worker;

class DoctrineDelayWorker implements Worker
{
    /**
     * @var QueueManagerRegistry
     */
    private $queueManagerRegistry;

    /**
     * @param QueueManagerRegistry $queueManagerRegistry
     */
    public function __construct(QueueManagerRegistry $queueManagerRegistry)
    {
        $this->queueManagerRegistry = $queueManagerRegistry;
    }

    /**
     * Called to start the queued task
     *
     * @param array $arguments
     * @throws \Exception
     * @throws UnrecoverableJobException
     */
    public function execute(array $arguments)
    {
        $this->queueManagerRegistry->put($arguments['name'], $arguments['arguments'], $arguments['options'], $arguments['manager']);
    }
}
