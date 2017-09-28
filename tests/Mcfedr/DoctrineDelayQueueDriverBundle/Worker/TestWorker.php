<?php

namespace Mcfedr\DoctrineDelayQueueDriverBundle\Worker;

use Mcfedr\QueueManagerBundle\Exception\UnrecoverableJobException;
use Mcfedr\QueueManagerBundle\Queue\Worker;
use Psr\Log\LoggerInterface;

class TestWorker implements Worker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $count = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Called to start the queued task.
     *
     * @param array $options
     *
     * @throws \Exception
     */
    public function execute(array $options)
    {
        if (!isset($options['job'])) {
            throw new UnrecoverableJobException('Missing job argument');
        }

        $job = $options['job'];

        if (isset($this->count[$job])) {
            ++$this->count[$job];
            $this->logger->warning('counted!', ['options' => $options]);
        } else {
            $this->count[$job] = 1;
            $this->logger->info('once', ['options' => $options]);
        }
    }
}
