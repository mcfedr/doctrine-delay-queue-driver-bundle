<?php
/**
 * Created by mcfedr on 7/28/16 09:18
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Command;

use Mcfedr\DoctrineDelayQueueDriverBundle\Manager\DoctrineDelayTrait;
use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\WorkerJob;
use Mcfedr\QueueManagerBundle\Command\RunnerCommand;
use Mcfedr\QueueManagerBundle\Exception\UnexpectedJobDataException;
use Mcfedr\QueueManagerBundle\Exception\WrongJobException;
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
    protected function getJob()
    {
        $job = $this->getEntityManager()->getRepository(DoctrineDelayJob::class)->createQueryBuilder('job')
            ->andWhere('job.time < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('job.time', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$job) {
            return null;
        }

        return new WorkerJob($job);
    }

    protected function finishJob(Job $job)
    {
        if (!$job instanceof WorkerJob) {
            throw new WrongJobException('Doctrine delay runner should only finish doctrine delay jobs');
        }

        $args = $job->getArguments();
        if (!isset($args['job'])) {
            throw new WrongJobException('Missing doctrine delay job');
        }

        $job = $args['job'];
        $this->queueManager->delete($job);
    }
}
