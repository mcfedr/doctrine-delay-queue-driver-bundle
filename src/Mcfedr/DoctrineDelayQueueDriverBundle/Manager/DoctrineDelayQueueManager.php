<?php

namespace Mcfedr\DoctrineDelayQueueDriverBundle\Manager;

use Mcfedr\DoctrineDelayQueueDriverBundle\Entity\DoctrineDelayJob;
use Mcfedr\QueueManagerBundle\Exception\NoSuchJobException;
use Mcfedr\QueueManagerBundle\Exception\WrongJobException;
use Mcfedr\QueueManagerBundle\Manager\QueueManager;
use Mcfedr\QueueManagerBundle\Queue\Job;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DoctrineDelayQueueManager implements QueueManager, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use DoctrineDelayTrait;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * Put a new job on a queue
     *
     * @param string $name The service name of the worker that implements {@link \Mcfedr\QueueManagerBundle\Queue\Worker}
     * @param array $arguments Arguments to pass to execute - must be json serializable
     * @param array $options Options for creating the job - these depend on the driver used
     * @return Job
     */
    public function put($name, array $arguments = [], array $options = [])
    {
        if (array_key_exists('manager_options', $options)) {
            $jobOptions = array_merge($this->defaultManagerOptions, $options['manager_options']);
        } else {
            $jobOptions = array_merge($this->defaultManagerOptions, array_diff_key($options, ['manager' => 1, 'time' => 1]));
        }

        if (array_key_exists('manager', $options)) {
            $jobManager = $options['manager'];
        } else {
            $jobManager = $this->defaultManager;
        }

        if (isset($options['time'])) {
            $jobTime = $options['time'];
        } else {
            return $this->container->get('mcfedr_queue_manager.registry')->put($name, $arguments, $jobOptions, $jobManager);
        }

        $job = new DoctrineDelayJob($name, $arguments, $jobOptions, $jobManager, $jobTime);

        $em = $this->getEntityManager();
        $em->persist($job);
        $em->flush($job);

        return $job;
    }

    /**
     * Remove a job from the queue
     *
     * @param $job
     * @throws WrongJobException
     * @throws NoSuchJobException
     */
    public function delete(Job $job)
    {
        if (!$job instanceof DoctrineDelayJob) {
            throw new WrongJobException('Doctrine delay queue manager can only delete doctrine delay jobs');
        }

        $em = $this->getEntityManager();
        if (!$em->contains($job)) {
            $id = $job->getId();
            $job = $em->getRepository(DoctrineDelayJob::class)->find($id);
            if (!$job) {
                throw new NoSuchJobException("No job with the id $id");
            }
        }

        $em->remove($job);
        $em->flush($job);
    }


}
