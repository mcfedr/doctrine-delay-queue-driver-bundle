<?php
/**
 * Created by mcfedr on 7/28/16 10:29
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Queue;

use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\QueueManagerBundle\Queue\Job;

class WorkerJob implements Job
{
    /**
     * @var DoctrineDelayJob
     */
    private $delayJob;

    public function __construct(DoctrineDelayJob $delayJob)
    {
        $this->delayJob = $delayJob;
    }

    /**
     * @return DoctrineDelayJob
     */
    public function getDelayJob()
    {
        return $this->delayJob;
    }

    public function getName()
    {
        return 'mcfedr_doctrine_delay_queue_driver.worker';
    }

    public function getArguments()
    {
        return [
            'job' => $this->delayJob
        ];
    }
}
