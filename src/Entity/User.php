<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username",
 * message="Le nom du client a déjà été utilisé")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_customer_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_society", "detail_customer"})
 * )
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "admin_app_customer_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_admin_customer"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_customer_create",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_customer"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "admin_app_customer_create",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_admin_customer"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_customer_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_customer"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "admin_app_customer_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_admin_customer"})
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list_customer", "detail_society", "detail_customer", "detail_admin_society", "detail_admin_customer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length( min=5,    
     * max=30, 
     * minMessage="Le nom du client doit contenir au minimun {{ limit }} caractères",
     * maxMessage="Le nom du client doit contenir moins de {{ limit }} caractères")
     * @Groups({"list_customer", "detail_society", "detail_customer", "detail_admin_society", "detail_admin_customer", "adduser", "login", "addusersuperadmin"})
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     * @Groups({"list_customer", "detail_society", "detail_customer", "detail_admin_society", "detail_admin_customer"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     * @Groups({"adduser", "login", "addusersuperadmin"})
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Society", inversedBy="customer", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"detail_customer", "list_customer", "detail_admin_customer", "detail_admin_customer", "addusersuperadmin"})
     */
    private $society;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getSociety(): ?Society
    {
        return $this->society;
    }

    public function setSociety(?Society $society): self
    {
        $this->society = $society;

        return $this;
    }
}
