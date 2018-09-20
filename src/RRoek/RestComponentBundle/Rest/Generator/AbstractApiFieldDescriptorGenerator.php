<?php

namespace RRoek\RestComponentBundle\Rest\Generator;

use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineGroupConcatFieldDescriptor;
use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Traversable;

/**
 * Class AbstractApiFieldDescriptorGenerator.
 */
class AbstractApiFieldDescriptorGenerator
{
    // ---- --- Private & Protected methods--- ----
    /**
     * Returns array of existing field-descriptors.
     *
     * @param Traversable $entityFields
     * @param string      $entityName
     *
     * @return array
     */
    protected function getSimpleFieldDescriptors($entityFields, $entityName)
    {
        $results = [];
        foreach ($entityFields as $field) {
            if (is_array($field)) {
                $results[$field['field']] = new DoctrineFieldDescriptor(
                    $field['field'],
                    (isset($field['serializedName']) ? $field['serializedName'] : $field['delimitor'] . $field['field']),
                    $entityName,
                    $field['field'],
                    []
                );
            } else {
                $results[$field] = new DoctrineFieldDescriptor(
                    $field,
                    $field,
                    $entityName,
                    $field,
                    []
                );
            }
        }

        return $results;
    }

    /**
     * Returns array of existing field-descriptors.
     *
     * @param Traversable $joinEntityFields
     * @param string      $entityName
     *
     * @return array
     */
    protected function getJoinedFieldDescriptors($joinEntityFields, $entityName)
    {
        $results = [];
        foreach ($joinEntityFields as $joinEntityClassName => $fields) {
            if (is_array($fields)) {
                $joinToLinkedEntity
                    = self::getDynamicEntityJoin($joinEntityClassName, $entityName, false);

                foreach ($fields as $field) {
                    if (isset($field['joinedEntitySearchedFieldName'])) {
                        $results[$field['joinedEntitySearchedFieldName']] = new DoctrineFieldDescriptor(
                            $field['joinedEntitySearchedField'],
                            $field['joinedEntitySearchedFieldName'],
                            $joinEntityClassName,
                            $field['field'],
                            $joinToLinkedEntity
                        );
                    } else {
                        $results[$field['field']] = new DoctrineFieldDescriptor(
                            $field['joinedEntitySearchedField'],
                            $field['field'],
                            $joinEntityClassName,
                            $field['field'],
                            $joinToLinkedEntity
                        );
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Returns array of existing field-descriptors.
     *
     * @param Traversable $joinEntityFields
     * @param string      $entityName
     *
     * @return array
     */
    protected function getOneToManyJoinedFieldDescriptors($joinEntityFields, $entityName)
    {
        $results = [];
        foreach ($joinEntityFields as $joinEntityClassName => $field) {
            if (is_array($field)) {
                $joinToLinkedEntity       =
                    self::getDynamicEntityJoin($joinEntityClassName, $entityName, true);
                $results[$field["field"]] = new DoctrineGroupConcatFieldDescriptor(
                    new DoctrineFieldDescriptor(
                        $field["joinedEntitySearchedField"],
                        $field["field"],
                        $joinEntityClassName,
                        $field["field"],
                        $joinToLinkedEntity
                    ),
                    $field["field"],
                    null,
                    ',',
                    false,
                    false,
                    '',
                    '',
                    '',
                    true,
                    false,
                    '',
                    true
                );
            } else {
                $joinToLinkedEntity
                                 = self::getDynamicEntityJoin($joinEntityClassName, $entityName, true);
                $results[$field] = new DoctrineGroupConcatFieldDescriptor(
                    new DoctrineFieldDescriptor(
                        'id',
                        $field,
                        $joinEntityClassName,
                        $field,
                        $joinToLinkedEntity
                    ),
                    $field
                );
            }
        }

        return $results;
    }

    //---- --- Public Methods --- ----
    /**
     * @param string $targetEntityJoin
     * @param string $mainEntityName
     * @param bool   $multiple
     * @param null   $joinCondition
     * @param string $joinMethod
     *
     * @return array
     */
    public static function getDynamicEntityJoin($targetEntityJoin, $mainEntityName, $targetPropertyOnEntityPlurialName = null, $multiple = false, $joinCondition = null, $joinMethod = DoctrineJoinDescriptor::JOIN_METHOD_LEFT)
    {
        $propertyOnEntity = lcfirst(substr($targetEntityJoin, (strpos($targetEntityJoin, ':') ?: -1) + 1));
         if (null !== $targetPropertyOnEntityPlurialName) {
             return [
                 $targetEntityJoin => new DoctrineJoinDescriptor(
                     $targetEntityJoin,
                     $mainEntityName . '.' . (true === $multiple ? $targetPropertyOnEntityPlurialName . 's' : $propertyOnEntity),
                     $joinCondition,
                     $joinMethod
                 ),
             ];
        } else {
             return [
                $targetEntityJoin => new DoctrineJoinDescriptor(
                    $targetEntityJoin,
                    $mainEntityName . '.' . (true === $multiple ? $propertyOnEntity . 's' : $propertyOnEntity),
                    $joinCondition,
                    $joinMethod
                ),
            ];
        }
    }

    /**
     * @param      $targetEntityJoin
     * @param bool $multiple
     *
     * @return array
     */
    public static function getDynamicSelfEntityJoin($targetEntityJoin, $multiple = false)
    {
        if (!$multiple) {
            return [
                $targetEntityJoin . 'Parent' => new DoctrineJoinDescriptor(
                    $targetEntityJoin,
                    $targetEntityJoin . '.parent'
                ),
            ];
        } else {
            return [
                $targetEntityJoin . 'Children' => new DoctrineJoinDescriptor(
                    $targetEntityJoin,
                    $targetEntityJoin . '.children'
                ),
            ];
        }
    }
}
