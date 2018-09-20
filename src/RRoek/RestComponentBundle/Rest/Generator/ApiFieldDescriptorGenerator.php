<?php

namespace RRoek\RestComponentBundle\Rest\Generator;

use Traversable;

/**
 * Class ApiFieldDescriptorGenerator.
 */
class ApiFieldDescriptorGenerator extends AbstractApiFieldDescriptorGenerator
{
    //---- --- Public Methods --- ----
    /**
     * @param Traversable $entityFields
     *
     * @return array
     */
    public function getAllFieldDescriptors($entityFields)
    {
        $simpleFields = [];
        if (isset($entityFields['entityFields']) && !empty($entityFields['entityFields'])) {
            $simpleFields = $this->getSimpleFieldDescriptors(
                $entityFields['entityFields'],
                $entityFields['entityName']
            );
        }
        $manyToOneJoinedFields = [];
        if (isset($entityFields['joinManyToOneEntityFields']) && !empty($entityFields['joinManyToOneEntityFields'])) {
            $manyToOneJoinedFields = $this->getJoinedFieldDescriptors(
                $entityFields['joinManyToOneEntityFields'],
                $entityFields['entityName']
            );
        }
        $oneToOneJoinedFields = [];
        if (isset($entityFields['joinOneToOneEntityFields']) && !empty($entityFields['joinOneToOneEntityFields'])) {
            $oneToOneJoinedFields = $this->getJoinedFieldDescriptors(
                $entityFields['joinOneToOneEntityFields'],
                $entityFields['entityName']
            );
        }
        $oneToManyJoinedFields = [];
        if (isset($entityFields['joinOneToManyEntityFields']) && !empty($entityFields['joinOneToManyEntityFields'])) {
            $oneToManyJoinedFields = $this->getOneToManyJoinedFieldDescriptors(
                $entityFields['joinOneToManyEntityFields'],
                $entityFields['entityName']
            );
        }

        return array_merge($simpleFields, $manyToOneJoinedFields, $oneToOneJoinedFields, $oneToManyJoinedFields);
    }
}
