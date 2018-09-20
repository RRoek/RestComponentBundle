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

/**
 * Interface EntityDescriptorInterface.
 */
interface EntityDescriptorInterface
{
    /**
     * This function will define your entityName, fields, & joinedEntity
     * ([entityName,entityFields,joinManyToOneEntityFields,joinOneToManyEntityFields,joinOneToOneEntityFields])
     * @return array
     */
    public static function getFieldsDescription();
}
