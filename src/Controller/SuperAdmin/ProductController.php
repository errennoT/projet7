<?php

namespace App\Controller\SuperAdmin;

use App\Entity\Product;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class ProductController extends AbstractFOSRestController
{
    /**
     * @Post(
     *    path = "/api/admin/products",
     *    name = "admin_app_product_create"
     * )
     * @View(StatusCode = 201)
     * @ParamConverter(
     *     "product",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="Ajouter un produit",
     *     in="body",
     *     description="Ajouter un produit",
     *     @Model(type=Product::class, groups={"addproduct"})
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Ajouter un produit",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @SWG\Tag(name="SuperAdmin Product")
     */
    public function addProduct(Product $product, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return $this->view($product, Response::HTTP_CREATED);
    }

    /**
     * @Delete("/api/admin/products/{id}", name="admin_app_product_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'effectuer cette action")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du produit"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Supprime un produit",
     * )
     * @SWG\Tag(name="SuperAdmin Product")
     */
    public function DeleteProduct(Product $product = null, $id)
    {
        if ($product) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();

            return $this->view("Le produit a bien été supprimé", Response::HTTP_OK);
        }

        return $this->view("Aucun produit trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
    }
}
