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
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du client"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Renvoie le détail d'un client",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="Admin Customer")
     */
    public function showCustomer(User $customer = null, SecurityManager $securityManager, $id)
    {
        if ($customer) {
            if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
                return $customer;
            }
            return $this->view("Erreur: vous n'êtes pas autorisé à voir cet utilisateur", Response::HTTP_UNAUTHORIZED);
        }

        return $this->view("Aucun client trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
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
     *     description="Renvoie la liste des clients",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="Admin Customer")
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
            ceil($totalPages),
            'page',
            'limit',
            true,
            $totalCustomers
        );

        return $paginatedCollection;
    }

    /**
     * @Post(
     *    path = "/api/customers/user",
     *    name = "app_customer_create"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_ADMIN")
     * 
     * @SWG\Parameter(
     *     name="Créer un client utilisateur",
     *     in="body",
     *     description="Rentrer l'username et le password",
     *     @Model(type=User::class, groups={"adduser"})
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Créer un client utilisateur",
     * )
     * @SWG\Tag(name="Admin Customer")
     */
    public function addCustomerUser(User $customer, ConstraintViolationList $violations, UserPasswordEncoderInterface $passwordEncoder, SecurityManager $securityManager)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerSociety = $securityManager->getSociety();
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
     *    path = "/api/customers/admin",
     *    name = "app_customer_create_admin"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
     * @IsGranted("ROLE_ADMIN")
     * 
     * @SWG\Parameter(
     *     name="Créer un client administrateur",
     *     in="body",
     *     description="Rentrer l'username et le password",
     *     @Model(type=User::class, groups={"adduser"})
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Créer un client administrateur",
     *     )
     * )
     * @SWG\Tag(name="Admin Customer")
     */
    public function addCustomerAdmin(User $customer, ConstraintViolationList $violations, UserPasswordEncoderInterface $passwordEncoder, SecurityManager $securityManager)
    {
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $customerSociety = $securityManager->getSociety();
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
     * @Delete("/api/customers/{id}", name="app_customer_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     * @IsGranted("ROLE_ADMIN", message="Accès refusé, il faut être admin de la société afin d'accèder à ces informations")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="L'id du client"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Supprimer un client",
     * )
     * @SWG\Tag(name="Admin Customer")
     */
    public function DeleteCustomer(User $customer = null, SecurityManager $securityManager, $id)
    {
        if ($customer) {
            if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($customer);
                $em->flush();

                return $this->view("Le compte a bien été supprimé", Response::HTTP_OK);
            }

            return $this->view("Erreur: vous n'êtes pas autorisé à faire cette action", Response::HTTP_UNAUTHORIZED);
        }

        return $this->view("Aucun client trouvé avec l'id $id", Response::HTTP_NOT_FOUND);
    }
}
