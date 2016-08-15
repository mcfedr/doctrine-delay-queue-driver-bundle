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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class DoctrineDelayRunnerCommand extends RunnerCommand
{
    use DoctrineDelayTrait;

    /**
     * @var int
     */
    private $batchSize = 20;

    public function __construct($name, array $options, QueueManager $queueManager)
    {
        parent::__construct($name, $options, $queueManager);
        $this->setOptions($options);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of messages to fetch at once', 20);
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
            ->setParameter('now', new \DateTime(null, new \DateTimeZone('UTC')))
            ->orderBy('job.time', 'ASC')
            ->setMaxResults($this->batchSize)
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

    protected function handleInput(InputInterface $input)
    {
        if (($batch = $input->getOption('batch-size'))) {
            $this->batchSize = $batch;
        }
    }
}
