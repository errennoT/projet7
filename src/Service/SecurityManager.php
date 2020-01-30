<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityManager extends AbstractController
{
    public function actionSecurity($dataId)
    {
        $user = $this->getUser();

        if ($user->getSociety()->getId() === $dataId) {
            return true;
        }
    }

    public function getSociety()
    {
        $user = $this->getUser();
        return $user->getSociety();
    }
}