<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace RRoek\RestComponentBundle\Rest\ListBuilder\Metadata\Provider;

use RRoek\RestComponentBundle\Rest\ListBuilder\Metadata\ClassMetadata;
use RRoek\RestComponentBundle\Rest\ListBuilder\Metadata\ProviderInterface;

/**
 * Returns merged metadata for other providers.
 */
class ChainProvider implements ProviderInterface
{
    /**
     * @var ProviderInterface[]
     */
    private $chain = [];

    public function __construct(array $chain)
    {
        $this->chain = $chain;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataForClass($className)
    {
        $classMetadata = new ClassMetadata($className);

        foreach ($this->chain as $provider) {
            $classMetadata->merge($provider->getMetadataForClass($className));
        }

        return $classMetadata;
    }
}
