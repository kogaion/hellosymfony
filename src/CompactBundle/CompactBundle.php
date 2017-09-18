<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/15/2017
 * Time: 12:02 PM
 */

namespace CompactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The compact bundle combines a bundle definition with an {@link ExtensionInterface} to provide sound defaults for
 * implementing custom bundles.
 */
abstract class CompactBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new CompactBundleExtension($this);
    }
}