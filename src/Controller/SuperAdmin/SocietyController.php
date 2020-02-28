<?php

namespace App\Controller\SuperAdmin;

use App\Entity\Society;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as JMS;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class SocietyController extends AbstractFOSRestController
{
    /**
     * @Post(
     *    path = "/api/admin/societies",
     *    name = "app_society_create"
     * )
     * @JMS\View(serializerGroups={"list_society"})
     * @ParamConverter(
     *     "society",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="Nom de la société",
     *     in="body",
     *     description="Ajouter une société",
     *     @Model(type=Society::class, groups={"addsociety"})
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Ajouter une société",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Society::class))
     *     )
     * )
     * @SWG\Tag(name="SuperAdmin Society")
     */
    public function addSociety(Society $society, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($society);
        $em->flush();

        return $this->view($society, Response::HTTP_CREATED);
    }

    /**
     * @Get(
     *     path = "/api/admin/societies/{id}",
     *     name = "app_society_show",
     *     requirements = {"id"="\d+"}
     * )
     * @View(serializerGroups={"detail_admin_society"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id de la société"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie le détail de la société",
     * )
     * @SWG\Tag(name="SuperAdmin Society")
     */
    public function showSociety(Society $society = null, $id)
    {
        if ($society) {
            return $society;
        }

        return $this->view("Aucune société trouvée avec l'id $id", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Get("/api/admin/list-societies/{page}", name="app_society_list", requirements = {"page"="\d+"})
     * @JMS\View(serializerGroups={"list_society"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="page",
     *     in="path",
     *     type="integer",
     *     description="Choisir la page"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie la liste des sociétés",
     * )
     * @SWG\Tag(name="SuperAdmin Society")
     */
    public function listSocieties(PaginatorInterface $paginatorInterface, Request $request, $page)
    {
        $societies = $paginatorInterface->paginate($this->getDoctrine()->getRepository('App\Entity\Society')->findAll(), $request->query->getInt('page', $page), 5);

        return $societies->getItems();
    }

    /**
     * @Delete("/api/admin/societies/{id}", name="app_society_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'effectuer cette action")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id de la société"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Suppression d'une société",
     * )
     * @SWG\Tag(name="SuperAdmin Society")
     */
    public function DeleteSociety(Society $society = null, $id)
    {
        if ($society) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($society);
            $em->flush();

            return $this->view("La société a bien été supprimée", Response::HTTP_OK);
        }

        return $this->view("Aucune société trouvée avec l'id $id", Response::HTTP_NOT_FOUND);
    }
}
