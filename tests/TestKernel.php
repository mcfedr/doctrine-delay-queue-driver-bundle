<?php


class TestKernel extends Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Mcfedr\QueueManagerBundle\McfedrQueueManagerBundle(),
            new Mcfedr\DoctrineDelayQueueDriverBundle\McfedrDoctrineDelayQueueDriverBundle(),
            new \Mcfedr\SqsQueueDriverBundle\McfedrSqsQueueDriverBundle(),
        ];
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config_test.yml');
    }
}
