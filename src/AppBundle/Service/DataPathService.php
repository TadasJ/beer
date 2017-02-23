<?php
namespace AppBundle\Service;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class DataPathService
 */
class DataPathService
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilePath($filename)
    {
        $kernel = $this->container->get('kernel');
        return $kernel->getRootDir() . '/Resources/importData/' . $filename;
    }
}
