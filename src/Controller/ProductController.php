<?php

namespace App\Controller;

use App\Entity\Product;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ProductController extends AbstractFOSRestController
{
    private $serialize;

    public function __construct(SerializerInterface $serialize)
    {
        $this->serialize = $serialize;
    }

    /**
     * @Get(
     *     path = "/api/products/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     */
    public function showProduct(Product $product)
    {
        return $product;
    }

    /**
     * @Get("/api/products", name="app_product_list")
     * @QueryParam(
     *     name="page",
     *     requirements="[a-zA-Z0-9]+",
     * )
     * @QueryParam(
     *     name="limit",
     *     requirements="[a-zA-Z0-9]+",
     * )
     * @View
     */
    public function listProducts(PaginatorInterface $paginatorInterface, Request $request, $page, $limit)
    {
        $allProducts = $this->getDoctrine()->getRepository('App\Entity\Product')->findAll();

        $totalProducts = count($allProducts);
        $totalPages = ($totalProducts / $limit);

        $filterDataProducts = $this->serialize->serialize($allProducts, 'json', SerializationContext::create()->setGroups(array('list_products')));
        $filterProduct = json_decode($filterDataProducts, true);

        $products = $paginatorInterface->paginate($filterProduct, $request->query->getInt('page', $page), $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation($products),
            'app_product_list',
            array(),
            $page,
            $limit,
            $totalPages,
            'page',
            'limit',
            true,
            $totalProducts
        );

        return $paginatedCollection;
    }
}
