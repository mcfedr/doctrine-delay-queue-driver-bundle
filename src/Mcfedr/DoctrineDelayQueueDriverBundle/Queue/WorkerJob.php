<?php
/**
 * Created by mcfedr on 7/28/16 10:29
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Entity;

use Mcfedr\QueueManagerBundle\Queue\AbstractJob;

class WorkerJob extends AbstractJob
{
    public function __construct(DoctrineDelayJob $delayJob)
    {
        parent::__construct('mcfedr_doctrine_delay_queue_driver.worker', [
            'job' => $delayJob
        ], []);
    }
}
