<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\User;

class SecurityController extends AbstractController
{
    /**
     * @Route(name="/login", path="/api/login_check", methods={"POST"})
     * @SWG\Parameter(
     *     name="S'authentifier en rentrant l'username et le password",
     *     in="body",
     *     description="S'authentifier",
     *     @Model(type=User::class, groups={"login"})
     * )
     * @SWG\Response(
     *     response=200,
     *     description="S'authentifier"
     * )
     * @SWG\Tag(name="Login")
     * @return JsonResponse
     */
    public function api_login(): JsonResponse
    {
        $user = $this->getUser();

        return new Response([
            'login' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/api/logout", name="app_logout", methods="none")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
