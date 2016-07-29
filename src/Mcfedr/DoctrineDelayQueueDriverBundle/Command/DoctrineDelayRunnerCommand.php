<?php
/**
 * Created by mcfedr on 7/28/16 09:18
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Command;

use Mcfedr\DoctrineDelayQueueDriverBundle\Manager\DoctrineDelayTrait;
use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\DoctrineDelayQueueDriverBundle\Queue\WorkerJob;
use Mcfedr\QueueManagerBundle\Command\RunnerCommand;
use Mcfedr\QueueManagerBundle\Exception\UnexpectedJobDataException;
use Mcfedr\QueueManagerBundle\Manager\QueueManager;
use Mcfedr\QueueManagerBundle\Queue\Job;

class DoctrineDelayRunnerCommand extends RunnerCommand
{
    use DoctrineDelayTrait;

    public function __construct($name, array $options, QueueManager $queueManager)
    {
        parent::__construct($name, $options, $queueManager);
        $this->setOptions($options);
    }

    /**
     * @throws UnexpectedJobDataException
     * @return Job
     */
    protected function getJobs()
    {
        return array_map(function(DoctrineDelayJob $job) {
            return new WorkerJob($job);
        }, $this->getEntityManager()->getRepository(DoctrineDelayJob::class)->createQueryBuilder('job')
            ->andWhere('job.time < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('job.time', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult());
    }

    protected function finishJobs(array $okJobs, array $retryJobs, array $failedJobs)
    {
        /** @var WorkerJob $job */
        foreach ($okJobs as $job) {
            $this->queueManager->delete($job->getDelayJob());
        }

        /** @var WorkerJob $job */
        foreach ($failedJobs as $job) {
            $this->queueManager->delete($job->getDelayJob());
        }
    }
}
