<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace RRoek\RestComponentBundle\Rest;

use RRoek\RestComponentBundle\Persistence\RelationTrait;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListBuilderInterface;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListRestHelper;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListRestHelperInterface;

/**
 * Defines some common REST functionalities.
 */
class RestHelper implements RestHelperInterface
{
    use RelationTrait;

    /**
     * @var ListRestHelperInterface
     */
    private $listRestHelper;

    public function __construct(ListRestHelper $listRestHelper)
    {
        $this->listRestHelper = $listRestHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeListBuilder(ListBuilderInterface $listBuilder, array $fieldDescriptors)
    {
        $listBuilder->limit($this->listRestHelper->getLimit())->setCurrentPage($this->listRestHelper->getPage());
        $listBuilder->setFieldDescriptors($fieldDescriptors);

        $fields = $this->listRestHelper->getFields();
        if (null != $fields) {
            foreach ($fields as $field) {
                if (!array_key_exists($field, $fieldDescriptors)) {
                    continue;
                }

                $listBuilder->addSelectField($fieldDescriptors[$field]);
            }
        } else {
            $listBuilder->setSelectFields($fieldDescriptors);
        }

        $searchFields = $this->listRestHelper->getSearchFields();
        if (null != $searchFields) {
            foreach ($searchFields as $searchField) {
                $listBuilder->addSearchField($fieldDescriptors[$searchField]);
            }

            $listBuilder->search($this->listRestHelper->getSearchPattern());
        }

        $sortBy = $this->listRestHelper->getSortColumn();
        if (null != $sortBy) {
            $listBuilder->sort($fieldDescriptors[$sortBy], $this->listRestHelper->getSortOrder());
        }
    }
}
