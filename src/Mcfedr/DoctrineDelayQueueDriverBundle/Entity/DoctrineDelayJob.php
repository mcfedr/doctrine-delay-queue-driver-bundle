<?php

namespace Mcfedr\DoctrineDelayQueueDriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mcfedr\QueueManagerBundle\Queue\RetryableJob;

/**
 * @ORM\Entity
 * @ORM\Table(name="DoctrineDelayJob", indexes={
 *     @ORM\Index(columns={"time"}),
 *     @ORM\Index(columns={"processing"})
 * })
 */
class DoctrineDelayJob implements RetryableJob
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $arguments;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $manager;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime")
     */
    private $time;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="processing", type="boolean")
     */
    private $processing = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $retryCount;

    /**
     * @param string    $name
     * @param array     $arguments
     * @param array     $options
     * @param string    $manager
     * @param \DateTime $time
     */
    public function __construct($name, array $arguments, array $options, $manager, \DateTime $time, $retryCount = 0)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->manager = $manager;
        $this->time = $time;
        $this->createdAt = new \DateTime();
        $this->retryCount = $retryCount;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Used to count retries.
     *
     * @return int
     */
    public function getRetryCount()
    {
        return $this->retryCount;
    }
}
