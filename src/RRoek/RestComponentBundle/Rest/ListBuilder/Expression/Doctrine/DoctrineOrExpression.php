<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace RRoek\RestComponentBundle\Rest\ListBuilder\Expression\Doctrine;

use RRoek\RestComponentBundle\Rest\ListBuilder\Expression\Exception\InsufficientExpressionsException;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListBuilderInterface;

/**
 * Combines an array of expressions with OR.
 */
class DoctrineOrExpression extends DoctrineConjunctionExpression
{
    /**
     * @var string
     */
    protected $conjunction = ListBuilderInterface::CONJUNCTION_OR;

    /**
     * DoctrineAndExpression constructor.
     *
     * @param AbstractDoctrineExpression[] $expressions
     *
     * @throws InsufficientExpressionsException
     */
    public function __construct(array $expressions)
    {
        parent::__construct($this->conjunction, $expressions);
    }
}
