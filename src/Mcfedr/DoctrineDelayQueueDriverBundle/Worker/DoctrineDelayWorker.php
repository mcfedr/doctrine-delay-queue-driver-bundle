<?php
/**
 * Created by mcfedr on 7/28/16 09:28
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Worker;

use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\QueueManagerBundle\Exception\UnrecoverableJobException;
use Mcfedr\QueueManagerBundle\Manager\QueueManagerRegistry;
use Mcfedr\QueueManagerBundle\Queue\Worker;

class DoctrineDelayWorker implements Worker
{
    /**
     * @var QueueManagerRegistry
     */
    private $queueManagerRegistry;

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
        if (!isset($arguments['job'])) {
            throw new UnrecoverableJobException('Missing doctrine delay job');
        }

        $job = $arguments['job'];
        if (!$job instanceof DoctrineDelayJob) {
            throw new UnrecoverableJobException('Invalid job');
        }

        $this->queueManagerRegistry->put($job->getName(), $job->getArguments(), $job->getOptions(), $job->getManager());
    }
}
