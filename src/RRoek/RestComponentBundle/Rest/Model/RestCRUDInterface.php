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

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RestCRUDInterface.
 */
interface RestCRUDInterface
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getListAction(Request $request);

    /**
     * @param mixed $id
     *
     * @return mixed
     */
    public function getAction($id);

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function postAction(Request $request);

    /**
     * @param mixed   $id
     * @param Request $request
     *
     * @return mixed
     */
    public function putAction($id, Request $request);

    /**
     * @param mixed $id
     *
     * @return mixed
     */
    public function deleteAction($id);
}
