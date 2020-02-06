<?php

namespace App\Controller\SuperAdmin;

use App\Entity\User;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as JMS;
use Knp\Component\Pager\PaginatorInterface;

class UserController extends AbstractFOSRestController
{
    /**
     * @Post(
     *    path = "/api/admin/customers",
     *    name = "admin_app_customer_create"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function addCustomer(User $customer, ConstraintViolationList $violations, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerContent = $request->request->All();
        
        $customerSociety = $this->getDoctrine()->getRepository('App\Entity\Society')->findOneBy(['name' => $customer->getSociety()->getName()]);
        $customer->setSociety($customerSociety);

        $customer->setPassword(
            $passwordEncoder->encodePassword(
                $customer,
                $customer->getPassword()
            )
        );

        switch ($customerContent['role']) {
            case "utilisateur":
                $customer->setRoles(['ROLE_USER']);
                break;
            case "admin":
                $customer->setRoles(['ROLE_ADMIN']);
                break;
            default:
                $customer->setRoles(['ROLE_USER']);
                break;
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($customer);
        $entityManager->flush();

        return $this->view($customer, Response::HTTP_CREATED);
    }

    /**
     * @Get(
     *     path = "/api/admin/customers/{id}",
     *     name = "admin_app_customer_show",
     *     requirements = {"id"="\d+"}
     * )
     * @JMS\View(serializerGroups={"detail_admin_customer"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function showCustomer(User $customer)
    {
        return $customer;
    }

    /**
     * @Get("/api/admin/list-customers/{page}", name="admin_app_customer_list", requirements = {"page"="\d+"})
     * @JMS\View(serializerGroups={"list_customer"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     */
    public function listCustomers(PaginatorInterface $paginatorInterface, Request $request, $page)
    {
        $customers = $paginatorInterface->paginate($this->getDoctrine()->getRepository('App\Entity\User')->findAll(),$request->query->getInt('page',$page),5);

        return $customers->getItems();
    }

    /**
     * @Delete("/api/admin/customers/{id}", name="admin_app_customer_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'effectuer cette action")
     */
    public function DeleteCustomer(User $customer)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($customer);
        $em->flush();

        return $this->view("Le client a bien été supprimé", Response::HTTP_OK);
    }
}
