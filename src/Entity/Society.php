<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SocietyRepository")
 * @UniqueEntity("name",
 * message="Le nom de la société a déjà été utilisé")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_society_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_admin_society", "list_society"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_society_create",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = "detail_admin_society")
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_society_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = "detail_admin_society")
 * )
 */
class Society
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list_society", "detail_society", "detail_customer", "list_customer", "detail_admin_society", "detail_admin_customer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(),
     * @Assert\Length( min=3,    
     * max=50, 
     * minMessage="Le nom de la société doit contenir au minimun {{ limit }} caractères",
     * maxMessage="Le nom de la société contenir moins de {{ limit }} caractères")
     * @Groups({"list_society", "detail_society", "detail_customer", "list_customer", "detail_admin_society", "detail_admin_customer", "addsociety", "addusersuperadmin"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="society", orphanRemoval=true)
     * @Groups({"detail", "detail_society", "detail_admin_society"})
     */
    private $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setSociety($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->contains($user)) {
            $this->user->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSociety() === $this) {
                $user->setSociety(null);
            }
        }

        return $this;
    }
}
