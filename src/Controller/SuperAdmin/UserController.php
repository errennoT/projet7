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
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class UserController extends AbstractFOSRestController
{
    /**
     * @Post(
     *    path = "/api/admin/customers/user",
     *    name = "admin_app_customer_create"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="Créer un client utilisateur",
     *     in="body",
     *     description="Rentrer le nom d'utilisateur, le password et le nom de la société",
     *     @Model(type=User::class, groups={"addusersuperadmin"})
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Créer un client utilisateur",
     * )
     * @SWG\Tag(name="SuperAdmin Customer")
     */
    public function addCustomerUser(User $customer, ConstraintViolationList $violations, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerSociety = $this->getDoctrine()->getRepository('App\Entity\Society')->findOneBy(['name' => $customer->getSociety()->getName()]);

        if ($customerSociety === null){
            return $this->view("Aucune société trouvée pour l'associer à ce client", Response::HTTP_NOT_FOUND);
        }

        $customer->setSociety($customerSociety);

        $customer->setPassword(
            $passwordEncoder->encodePassword(
                $customer,
                $customer->getPassword()
            )
        );

        $customer->setRoles(['ROLE_USER']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($customer);
        $entityManager->flush();

        return $this->view($customer, Response::HTTP_CREATED);
    }

    /**
     * @Post(
     *    path = "/api/admin/customers/admin",
     *    name = "admin_app_customer_create_admin"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="Créer un client administrateur",
     *     in="body",
     *     description="Rentrer le nom d'utilisateur, le password et le nom de la société",
     *     @Model(type=User::class, groups={"addusersuperadmin"})
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Créer un client administrateur",
     * )
     * @SWG\Tag(name="SuperAdmin Customer")
     */
    public function addCustomerAdmin(User $customer, ConstraintViolationList $violations, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerSociety = $this->getDoctrine()->getRepository('App\Entity\Society')->findOneBy(['name' => $customer->getSociety()->getName()]);
        
        if ($customerSociety === null){
            return $this->view("Aucune société trouvée pour l'associer à ce client", Response::HTTP_NOT_FOUND);
        }

        $customer->setSociety($customerSociety);

        $customer->setPassword(
            $passwordEncoder->encodePassword(
                $customer,
                $customer->getPassword()
            )
        );

        $customer->setRoles(['ROLE_ADMIN']);

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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du client"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie le détail d'un client",
     * )
     * @SWG\Tag(name="SuperAdmin Customer")
     */
    public function showCustomer(User $customer = null, $id)
    {
        if ($customer) {
            return $customer;
        }

        return $this->view("Aucun client trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Get("/api/admin/list-customers/{page}", name="admin_app_customer_list", requirements = {"page"="\d+"})
     * @JMS\View(serializerGroups={"list_customer"})
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="page",
     *     in="path",
     *     type="integer",
     *     description="Choisir la page"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie la liste des clients",
     * )
     * @SWG\Tag(name="SuperAdmin Customer")
     */
    public function listCustomers(PaginatorInterface $paginatorInterface, Request $request, $page)
    {
        $customers = $paginatorInterface->paginate($this->getDoctrine()->getRepository('App\Entity\User')->findAll(), $request->query->getInt('page', $page), 5);

        return $customers->getItems();
    }

    /**
     * @Delete("/api/admin/customers/{id}", name="admin_app_customer_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_SUPER_ADMIN", message="Accès refusé, il faut être super admin afin d'effectuer cette action")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du client"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Suppression du client",
     * )
     * @SWG\Tag(name="SuperAdmin Customer")
     */
    public function DeleteCustomer(User $customer = null, $id)
    {
        if ($customer) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush();

            return $this->view("Le client a bien été supprimé", Response::HTTP_OK);
        }

        return $this->view("Aucun client trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
    }
}
