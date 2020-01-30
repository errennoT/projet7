<?php

namespace App\Controller;

use App\Entity\Product;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as JMS;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractFOSRestController
{
    /**
     * @Get(
     *     path = "/produit/{id}",
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
     * @Get("/liste-produits/{page}", name="app_product_list", requirements = {"page"="\d+"})
     * @JMS\View(serializerGroups={"list_products"})
     */
    public function listProducts()
    {
        $products = $this->getDoctrine()->getRepository('App\Entity\Product')->findAll();
        
        return $products;
    }

}
