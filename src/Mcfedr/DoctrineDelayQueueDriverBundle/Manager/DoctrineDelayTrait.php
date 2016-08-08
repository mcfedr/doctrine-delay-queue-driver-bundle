<?php
/**
 * Created by mcfedr on 7/28/16 10:18
 */
namespace Mcfedr\DoctrineDelayQueueDriverBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

trait DoctrineDelayTrait
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $entityManagerName;

    /**
     * @var string
     */
    private $defaultManager;

    /**
     * @var array
     */
    private $defaultManagerOptions = [];

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        if (!$this->doctrine) {
            $this->doctrine = $this->container->get('doctrine');
        }
        return $this->doctrine->getManager($this->entityManagerName);
    }

    private function setOptions(array $options)
    {
        $this->defaultManager = $options['default_manager'];
        $this->defaultManagerOptions = $options['default_manager_options'];
        $this->entityManagerName = $options['entity_manager'];
    }
}
