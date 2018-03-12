# RestComponentBundle
### Features

- Give you ability to to easier & faster make great RESTful HATEOAS Apis


**Table of Contents**

[TOCM]

[TOC]

## Introduction
This bundle is initially forked from [Sulu CMS (a CMF based on Symfony CMF)](https://github.com/sulu/sulu "Sulu CMS (a CMF based on Symfony CMF)").
It give you ability to to easier & faster make great RESTful HATEOAS Apis.

### Advantages
Easier. Faster. Make great RESTful HATEOAS APIs !

## Use
### Activate bundle
To use it, simply `composer require rroek/rest-component-bundle`
and enable it :
in AppKernel.php :
```php
     /**
         * @return array
         */
        public function registerBundles()
        {
            $bundles = [
    		[...]
    		new RRoek\RestComponentBundle\RRoekRestComponentBundle(),
    		[...]
```
### Make your Controller
For this example, we will take "MyPersonalEntity" class wich is an *Doctrine Entity* in our Symfony Project.

Our Entity will have an id, a label, a relation with another entity & getters/setters for it.

#### So let's see interresting things :

I want to make my Controller. it job is to give access to my entity & make is CRUD callable.
We will use [nelmio/NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle "nelmio/NelmioApiDocBundle") to generate our apiDoc.
So lets create our Controller :

------------

In : 


    MyBundle
		Controller
			RestController
				MyEntityRestController.php
    	Entity
    		MyPersonalEntity.php
		...


------------

```php
   <?php

namespace Acme\BackendApiBundle\Controller\RestController;

use RRoek\RestComponentBundle\Rest\Model\AbstractRestController;
use RRoek\RestComponentBundle\Rest\Exception\EntityNotFoundException;
use RRoek\RestComponentBundle\Model\Controller\RestController\RestCRUDInterface;
use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use RRoek\RestComponentBundle\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineGroupConcatFieldDescriptor;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Acme\BackendApiBundle\Entity\MyEntity;

/**
 * Class MyEntityRestController.
 */
class MyEntityRestController extends AbstractRestController implements RestCRUDInterface
{
	// ---- --- Protected Methods --- ----
    /**
     * Returns array of existing field-descriptors.
     *
     * @return array
     */
    protected function _getFieldDescriptors()
    {
        //Describe Join to another entity :
        $offerJoinForManyToOne =  [
            'offer' => new DoctrineJoinDescriptor(
                'AcmeBackendApiBundle:Offer',
                'AcmeBackendApiBundle:MyEntity' . '.' . 'offer'
            ),
        ];
        $offerJoinForOneToMany =  [
            'offers' => new DoctrineJoinDescriptor(
                'AcmeBackendApiBundle:Offer',
                'AcmeBackendApiBundle:MyEntity' . '.' . 'offer' . 's'
            ),
        ];

        return [
            'id'                         => new DoctrineFieldDescriptor(
                'id',
                'id',
                'AcmeBackendApiBundle:MyEntity',
                'id',
                []
            ),
            'label'                      => new DoctrineFieldDescriptor(
                'label',
                'label',
                'AcmeBackendApiBundle:MyEntity',
                'label',
                []
            ),
            //[...]
            //if MyEntity have a join on another entity (case with ManyToOne or OneToOne) :
            'offer'                   => new DoctrineFieldDescriptor(
                'id',
                'offer',
                'AcmeBackendApiBundle:Offer',
                'offer',
                $offerJoinForManyToOne
            ),
            //if MyEntity have a join on another entity (case with OneToMany Be careful to have a doctrine extension for this !) :
            'offers'           => new DoctrineGroupConcatFieldDescriptor(
                new DoctrineFieldDescriptor(
                    'id',
                    'offer',
                    'AcmeBackendApiBundle:Offer',
                    'offer',
                    $offerJoinForOneToMany
                ),
                'offers'
            ),
        ];
    }
	
    // ---- --- Public methods--- ----
    /**
     * Returns all fields for a entity that can be used by list.
     *
     * @ApiDoc(
     *    description="Get available fields of my entity with options",
     *    output="Acme\BackendApiBundle\Entity\MyEntity",
     *      statusCodes={
     *         200 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @return Response
     */
    public function getFieldsAction()
    {
        try {
            $fields = array_values($this->_getFieldDescriptors());
            $view   = $this->view($fields, Response::HTTP_OK);
        } catch (Throwable $e) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->handleView($view);
    }

    /**
     * Shows all entity items.
     *
     * @ApiDoc(
     *    description="Get my Entity Collection",
     *    output="Acme\BackendApiBundle\Entity\MyEntity",
     *      statusCodes={
     *         200 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getListAction(Request $request)
    {
        try {
            // Get collection of entity
            list($listBuilder, $items) = $this->_getDataItems();

            //Make our list representation to get paginated collection + filter, search etc. :
            $list = $this->_getListRepresentation(
                $items,
                'my_entity_items',
                'private_get_my_entity_list',
                $request,
                $listBuilder
            );

            //Build view :
            $view = $this->view($list, Response::HTTP_OK);
        } catch (Throwable $e) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        //Return view representation to selected format like json :
        return $this->handleView($view);
    }
    
    /**
     * Returns a single entity item identified by id.
     *
     * @ApiDoc(
     *    description="Get my Entity Instance",
     *    output="Acme\BackendApiBundle\Entity\MyEntity",
     *      statusCodes={
     *         200 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        try {
            //Get only one instance of entity with selected id :
            $item = $this->_getDataItems($id);

            //If entity existe return ok response :
            if (!$item) {
                $view = $this->view(
                    ["message" => "Not found message"],
                    Response::HTTP_NOT_FOUND
                );
            } else {
                $view = $this->view($item, Response::HTTP_OK);
            }
        } catch (Throwable $e) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->handleView($view);
    }

    /**
     * Create a new entity and returns it.
     *
     * @ApiDoc(
     *    description="Create my Entity",
     *    input="Acme\BackendApiBundle\Entity\MyEntity",
     *    output="Acme\BackendApiBundle\Entity\MyEntity",
     *    statusCodes={
     *         201 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         404 = "Http code returned if instance of entity not found",
     *         409 = "Http code returned if conflict",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        try {
            //Create you entity here...
            $item = new MyEntity();
//            [...]
            $view = $this->view($item, Response::HTTP_CREATED);
        } catch (EntityNotFoundException $e) {
            $view = $this->view(
                ["message" => "Not found message"],
                Response::HTTP_NOT_FOUND
            );
        } catch (Throwable $t) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->handleView($view);
    }

    /**
     * Update a entity with given id.
     *
     * @ApiDoc(
     *    description="Ypdate my entire Entity",
     *    input="Acme\BackendApiBundle\Entity\MyEntity",
     *    statusCodes={
     *         204 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         404 = "Http code returned if instance of entity not found",
     *         409 = "Http code returned if conflict",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function putAction($id, Request $request)
    {
        $data = $request->request->all();

        try {
            //Update here your entity...

            $view = $this->view(null, Response::HTTP_NO_CONTENT);
        } catch (EntityNotFoundException $e) {
            $view = $this->view(
                ["message" => "Not found message"],
                Response::HTTP_NOT_FOUND
            );
        } catch (Throwable $t) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->handleView($view);
    }

    /**
     * Change a entity to closed.
     *
     * @ApiDoc(
     *    description="Patch one part of my Entity",
     *     statusCodes={
     *         204 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         404 = "Http code returned if instance of entity not found",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function patchAction($id)
    {
        //[...] Same way
    }

    /**
     * Delete a entity.
     *
     * @ApiDoc(
     *    description="Delete my Entity",
     *     statusCodes={
     *         201 = "Http code returned if success",
     *         401 = "Http code returned if our auth failed",
     *         404 = "Http code returned if instance of entity not found",
     *         500 = "Http code returned is server error or unexpected error"
     *    },
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            //Delete you entity here...

            $view = $this->view(null, Response::HTTP_NO_CONTENT);
        } catch (EntityNotFoundException $e) {
            $view = $this->view(
                ["message" => "Not found message"],
                Response::HTTP_NOT_FOUND
            );
        } catch (Throwable $t) {
            //log error for example...

            $view = $this->view(
                ["message" => "my error message"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->handleView($view);
    }
}

```
------------

Declare our routes :
```yaml
   # ------ ------ ------ ------ ------
# my_entity api routes
# ------ ------ ------ ------ ------
private_get_my_entity_fields:
    path:     /my-entities/fields.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:getFields, _format: json }
    methods: GET
    requirements:
        _format: json|xml|csv

private_get_my_entity_list:
    path:     /my-entities.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:getList, _format: json }
    methods: GET
    requirements:
        _format: json|xml|csv

private_get_my_entity:
    path:     /my-entities/{id}.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:get, _format: json }
    methods: GET
    requirements:
        _format: json|xml|csv

private_post_my_entity:
    path:     /my-entities.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:post, _format: json }
    methods: POST
    requirements:
        _format: json|xml|csv

private_put_my_entity:
    path:     /my-entities/{id}.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:put, _format: json }
    methods: PUT
    requirements:
        _format: json|xml|csv

private_patch_my_entity:
    path:     /my-entities/{id}/close.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:patchClose, _format: json }
    methods: PATCH
    requirements:
        _format: json|xml|csv

private_delete_my_entity:
    path:     /my-entities/{id}.{_format}
    defaults: { _controller: AcmeBackendApiBundle:RestController\MyEntityRest:delete, _format: json }
    methods: DELETE
    requirements:
        _format: json|xml|csv
```
------------


And its all ! Your entity Full CRUD APi is created, it have allready CRUD methods and respect RESTful HATEOAS recommendations :

```json
    {
  "page": 1,
  "limit": 10,
  "pages": 1,
  "total": 7,
  "_links": {
    "self": {
      "href": "/app_dev.php/private/api/v1/frequencies?page=1&limit=10"
    },
    "first": {
      "href": "/app_dev.php/private/api/v1/frequencies?page=1&limit=10"
    },
    "last": {
      "href": "/app_dev.php/private/api/v1/frequencies?page=1&limit=10"
    },
    "filter": {
      "href": "/app_dev.php/private/api/v1/frequencies?fields=%7BfieldsList%7D&page=1&limit=10"
    },
    "find": {
      "href": "/app_dev.php/private/api/v1/frequencies?search=%7BsearchString%7D&searchFields=%7BsearchFields%7D&page=1&limit=10"
    },
    "pagination": {
      "href": "/app_dev.php/private/api/v1/frequencies?page=%7Bpage%7D&limit=%7Blimit%7D"
    },
    "sortable": {
      "href": "/app_dev.php/private/api/v1/frequencies?sortBy=%7BsortBy%7D&sortOrder=%7BsortOrder%7D&page=1&limit=10"
    }
  },
  "_embedded": {
    "my_entity_items": [
      {
        "id": 1,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 2,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 3,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 4,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 5,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 6,
        "code": "XXXX",
        "label": "example label"
      },
      {
        "id": 7,
        "code": "XXXX",
        "label": "example label"
      }
    ]
  }
}
```

## Enjoy !
