<?php

/*
 * This file is part of RRoek\RestComponentBundle.
 *
 * (c) RRoek
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace RRoek\RestComponentBundle\Rest\Model;

use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use RRoek\RestComponentBundle\Rest\ListBuilder\GroupedListRepresentation;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListBuilderInterface;
use RRoek\RestComponentBundle\Rest\ListBuilder\ListRepresentation;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractRestController.
 */
abstract class AbstractRestController extends FOSRestController
{
    //---- --- Private & Protected Methods : --- ----
    /**
     * @return mixed
     */
    abstract protected function _getFieldDescriptors();

    /**
     * @param int|null $id
     *
     * @return DoctrineListBuilder
     */
    protected function _initDataItems(int $id = null)
    {
        $restHelper  = $this->get('rroek_rest_component.doctrine_rest_helper');
        $factory     = $this->get('rroek_rest_component.doctrine_list_builder_factory');
        $listBuilder = $factory->create($this->getEntityName());
        $restHelper->initializeListBuilder($listBuilder, $this->_getFieldDescriptors());
        if (null !== $id) {
            $listBuilder->where(
                $this->_getFieldDescriptors()['id'],
                $id,
                DoctrineListBuilder::WHERE_COMPARATOR_EQUAL
            );
            $listBuilder->addGroupBy($this->_getFieldDescriptors()['id']);
        }

        return $listBuilder;
    }

    /**
     * @param int|null $id
     *
     * @return array|mixed
     */
    protected function _getDataItems(int $id = null)
    {
        $listBuilder = $this->_initDataItems($id);

        if (null !== $id) {
            return $listBuilder->execute();
        } else {
            return [$listBuilder, $listBuilder->execute()];
        }
    }

    /**
     * @param int|null $id
     * @param string   $specificFunctionToCall
     *
     * @return array
     */
    protected function _getDataItemsWithConditions(int $id = null, string $specificFunctionToCall = '_specificQuery')
    {
        $listBuilder = $this->_initDataItems($id);
        $this->$specificFunctionToCall($listBuilder);

        return [$listBuilder, $listBuilder->execute()];
    }

    /**
     * @param array                $items
     * @param string               $itemsLabel
     * @param string               $route
     * @param Request              $request
     * @param ListBuilderInterface $listBuilder
     *
     * @return ListRepresentation
     */
    protected function _getListRepresentation(
        array $items,
        string $itemsLabel,
        string $route,
        Request $request,
        $listBuilder
    ) {
        return new ListRepresentation(
            $items,
            $itemsLabel,
            $route,
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );
    }

    /**
     * @param array                $items
     * @param string               $route
     * @param Request              $request
     * @param ListBuilderInterface $listBuilder
     *
     * @return GroupedListRepresentation
     */
    protected function _getGroupedListRepresentation(array $items, string $route, Request $request, $listBuilder)
    {
        return new GroupedListRepresentation(
            $items,
            $route,
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );
    }

    /**
     * Create a specific error view when FM doesn't have access to datas.
     *
     * @param array $authorizationStatus
     *
     * @return bool|View
     */
    protected function _createForbiddenView($authorizationStatus)
    {
        return $this->view([self::MESSAGE => $authorizationStatus[self::MESSAGE]], Response::HTTP_FORBIDDEN);
    }
}