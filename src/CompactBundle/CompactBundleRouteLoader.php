<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/15/2017
 * Time: 12:07 PM
 */

namespace CompactBundle;


use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
/**
 * Automatically loads routing configuration in compact bundles to favor convention over configuration.
 *
 * Currently loads:
 *
 * - <Bundle>/Resources/config/routing.yml
 */
class CompactBundleRouteLoader extends Loader
{
    /** @var KernelInterface required to access bundles */
    private $kernel;
    /** @var LoggerInterface */
    private $log;
    /** @var bool stores whether the routes have already been loaded */
    private $loaded = false;
    /**
     * Provide access to kernel in order to load bundle routes.
     */
    public function __construct(KernelInterface $kernel, LoggerInterface $log)
    {
        $this->kernel = $kernel;
        $this->log = $log;
    }
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "convention" loader twice');
        }
        $collection = new RouteCollection();
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof CompactBundle) {
                continue;
            }
            $routing = sprintf("%s/Resources/config/routing.yml", $bundle->getPath());
            if (!file_exists($routing)) {
                $this->log->debug('Compact bundle "{bundle}" has no routes defined.', [
                    'bundle' => $bundle->getName()
                ]);
                continue;
            }
            $collection->addCollection($this->import($routing, 'yaml'));
            $this->log->debug('Loaded routes for compact bundle "{bundle}".', [
                'bundle' => $bundle->getName()
            ]);
        }
        return $collection;
    }
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'convention' === $type;
    }
}