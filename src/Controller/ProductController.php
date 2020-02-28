<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du produit"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie le détail d'un produit",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @SWG\Tag(name="Products")
     */
    public function showProduct(Product $product = null, $id)
    {
        if ($product) {
            return $product;
        }

        return $this->view("Aucun produit trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
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
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="Selectionne la page"
     * )
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="Limite le nombre de résultat par page"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie la liste des produits",
     * )
     * @SWG\Tag(name="Products")
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
            ceil($totalPages),
            'page',
            'limit',
            true,
            $totalProducts
        );

        return $paginatedCollection;
    }
}
