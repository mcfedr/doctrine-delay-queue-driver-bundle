<?php
/**
 * Created by mcfedr on 7/28/16 09:18
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Command;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Types\Type;
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

    private $reverse = false;

    public function __construct($name, array $options, QueueManager $queueManager)
    {
        parent::__construct($name, $options, $queueManager);
        $this->setOptions($options);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of messages to fetch at once', 20)
            ->addOption('reverse', null, InputOption::VALUE_NONE, 'Fetch jobs from the database in reverse order (newest first)');
    }

    /**
     * @throws UnexpectedJobDataException
     * @return Job
     */
    protected function getJobs()
    {
        try {
            $now = new \DateTime(null, new \DateTimeZone('UTC'));

            $em = $this->getEntityManager();
            $em->getConnection()->beginTransaction();

            $repo = $em->getRepository(DoctrineDelayJob::class);

            $orderDir = $this->reverse ? 'DESC' : 'ASC';

            $em->getConnection()->executeUpdate("UPDATE DoctrineDelayJob job SET job.processing = TRUE WHERE job.time < :now ORDER BY job.time $orderDir LIMIT :limit",
                [
                    'now' => $now,
                    'limit' => $this->batchSize
                ], [
                    'now' => Type::getType(Type::DATETIME),
                    'limit' => Type::getType(Type::INTEGER)
                ]);

            $jobs = $repo->createQueryBuilder('job')
                ->andWhere('job.processing = true')
                ->getQuery()
                ->getResult();

            $repo->createQueryBuilder('job')
                ->delete()
                ->andWhere('job.processing = true')
                ->getQuery()
                ->execute();

            $em->getConnection()->commit();

            return array_map(function (DoctrineDelayJob $job) {
                return new WorkerJob($job);
            }, $jobs);
        } catch (DriverException $e) {
            if ($e->getErrorCode() == 1213) { //Deadlock found when trying to get lock;
                $em->rollback();
                throw new UnexpectedJobDataException('Deadlock trying to lock table', 0, $e);
            }
        }
    }

    protected function finishJobs(array $okJobs, array $retryJobs, array $failedJobs)
    {
        if (count($retryJobs)) {
            $em = $this->getEntityManager();

            /** @var WorkerJob $job */
            foreach ($retryJobs as $job) {
                $oldJob = $job->getDelayJob();
                $retryCount = $oldJob->getRetryCount() + 1;
                $newJob = new DoctrineDelayJob($oldJob->getName(), $oldJob->getArguments(), $oldJob->getOptions(),
                    $oldJob->getManager(), new \DateTime(sprintf('+%d seconds', $this->getRetryDelaySeconds($retryCount))), $retryCount);
                $em->persist($newJob);
            }

            $em->flush();
        }
    }

    protected function handleInput(InputInterface $input)
    {
        if (($batch = $input->getOption('batch-size'))) {
            $this->batchSize = (int) $batch;
        }
        $this->reverse = $input->getOption('reverse');
    }
}
