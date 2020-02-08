<?php

namespace App\Controller;

use App\Service\SecurityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as JMS;
use App\Entity\User;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\Delete;
use Knp\Component\Pager\PaginatorInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class CustomerController extends AbstractFOSRestController
{
    private $serialize;

    public function __construct(SerializerInterface $serialize)
    {
        $this->serialize = $serialize;
    }

    /**
     * @Get(
     *     path = "/api/customers/{id}",
     *     name = "app_customer_show",
     *     requirements = {"id"="\d+"}
     * )
     * @QueryParam(
     *     name="page",
     *     requirements="[a-zA-Z0-9]+",
     * )
     * @JMS\View(serializerGroups={"detail_society"})
     * @IsGranted("ROLE_ADMIN", message="Accès refusé, il faut être admin de la société afin d'accèder à ces informations")
     */
    public function showAllCustomers(SecurityManager $securityManager, PaginatorInterface $paginatorInterface, Request $request, $page)
    {
        if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
            return $customer;
        }
        return $this->view("Erreur: vous n'êtes pas autorisé à voir cet utilisateur", Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Get(
     *     path = "/api/customers",
     *     name = "app_customer_list"
     * )
     * @QueryParam(
     *     name="page",
     *     requirements="[a-zA-Z0-9]+",
     * )
     * @QueryParam(
     *     name="limit",
     *     requirements="[a-zA-Z0-9]+",
     * )
     * @View
     * @IsGranted("ROLE_ADMIN", message="Accès refusé, il faut être admin de la société afin d'accèder à ces informations")
     */
    public function showAllCustomers(SecurityManager $securityManager, PaginatorInterface $paginatorInterface, Request $request, $page, $limit)
    {
        $society = $securityManager->getSociety();
        $allCustomers = $this->getDoctrine()->getRepository('App\Entity\User')->findBy(['society' => $society]);

        $totalCustomers = count($allCustomers);
        $totalPages = ($totalCustomers / $limit);

        $filterDataCustomers = $this->serialize->serialize($allCustomers, 'json', SerializationContext::create()->setGroups(array('detail_society')));
        $filterCustomer = json_decode($filterDataCustomers, true);

        $customerSociety = $paginatorInterface->paginate($filterCustomer, $request->query->getInt('page', $page), $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation($customerSociety),
            'app_product_list',
            array(),
            $page,
            $limit,
            $totalPages,
            'page',
            'limit',
            true,
            $totalCustomers
        );

        return $paginatedCollection;
    }

    /**
     * @Post(
     *    path = "/api/customers",
     *    name = "app_customer_create"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function addCustomer(User $customer, ConstraintViolationList $violations, Request $request, UserPasswordEncoderInterface $passwordEncoder, SecurityManager $securityManager)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerContent = $request->request->All();

        $customerSociety = $securityManager->getSociety();
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
            case "administrateur":
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
     * @Delete("/api/customers/{id}", name="app_customer_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_ADMIN", message="Accès refusé, il faut être admin de la société afin d'accèder à ces informations")
     */
    public function DeleteCustomer(User $customer, SecurityManager $securityManager)
    {
        if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush();

            return $this->view("Le compte a bien été supprimé", Response::HTTP_OK);
        }

        return $this->view("Erreur: vous n'êtes pas autorisé à faire cette action", Response::HTTP_UNAUTHORIZED);
    }
}
