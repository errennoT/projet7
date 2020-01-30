<?php

namespace App\Controller;

use App\Entity\Society;
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

class CustomerController extends AbstractFOSRestController
{
    /**
     * @Get(
     *     path = "/societe/{id}",
     *     name = "app_society_show",
     *     requirements = {"id"="\d+"}
     * )
     * @JMS\View(serializerGroups={"detail_society"})
     */
    public function showSociety(Society $society, SecurityManager $securityManager)
    {
        if ($securityManager->actionSecurity($society->getId())) {
            return $society;
        }
        return $this->view(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Get(
     *     path = "/client/{id}",
     *     name = "app_customer_show",
     *     requirements = {"id"="\d+"}
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     */
    public function showCustomer(User $customer, SecurityManager $securityManager)
    {
        if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
            return $customer;
        }
        return $this->view(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Post(
     *    path = "/creer-utilisateur",
     *    name = "app_customer_create"
     * )
     * @JMS\View(serializerGroups={"detail_customer"})
     * @ParamConverter(
     *     "customer",
     *     converter="fos_rest.request_body"
     * )
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
     * @Delete("/supprimer-utilisateur/{id}", name="app_customer_delete", requirements = {"id"="\d+"})
     * @View(StatusCode = 200)
     */
    public function DeleteCustomer(User $customer, SecurityManager $securityManager)
    {
        if ($securityManager->actionSecurity($customer->getSociety()->getId())) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush();

            return $this->view(null, Response::HTTP_OK);
        }

        return $this->view(null, Response::HTTP_UNAUTHORIZED);
    }

}
