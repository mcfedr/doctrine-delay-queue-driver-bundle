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
     * @var EntityManager
     */
    private $entityManager;

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
        if (!$this->entityManager) {
            /** @var Registry $doctrine */
            $doctrine = $this->container->get('doctrine');
            $this->entityManager = $doctrine->getManager($this->entityManagerName);
        }
        return $this->entityManager;
    }

    private function setOptions(array $options)
    {
        $this->defaultManager = $options['default_manager'];
        $this->defaultManagerOptions = $options['default_manager_options'];
        $this->entityManagerName = $options['entity_manager'];
    }
}
