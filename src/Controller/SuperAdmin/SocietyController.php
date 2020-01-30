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

class SocietyController extends AbstractFOSRestController
{
    /**
     * @Post(
     *    path = "/admin/creer-societe",
     *    name = "app_society_create"
     * )
     * @JMS\View(serializerGroups={"list_society"})
     * @ParamConverter(
     *     "society",
     *     converter="fos_rest.request_body"
     * )
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
     *     path = "/admin/societe/{id}",
     *     name = "app_society_show",
     *     requirements = {"id"="\d+"}
     * )
     * @JMS\View(serializerGroups={"detail_society"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function showSociety(Society $society)
    {
        return $society;
    }

    /**
     * @Get("/admin/liste-societes/{page}", name="app_society_list", requirements = {"page"="\d+"})
     * @JMS\View(serializerGroups={"list_society"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function listSocieties()
    {
        $societies = $this->getDoctrine()->getRepository('App\Entity\Society')->findAll();
        
        return $societies;
    }

    /**
     * @Delete("/admin/supprimer-societe/{id}", name="app_society_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function DeleteSociety(Society $society)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($society);
        $em->flush();

        return $this->view(null, Response::HTTP_OK);
    }
}
