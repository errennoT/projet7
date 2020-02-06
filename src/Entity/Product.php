<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity("model",
 * message="Le modèle a déjà été ajouté")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_product_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"list_products", "detail_product"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "admin_app_product_create",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"detail_product"})
 * )
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list_products", "detail_product"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(),
     * @Assert\Length( min=5,    
     * max=50, 
     * minMessage="Le modèle doit contenir au minimun {{ limit }} caractères",
     * maxMessage="Le modèle doit contenir moins de {{ limit }} caractères"
     * )
     * @Groups({"list_products", "detail_product"})
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"detail_product"})
     */
    private $size_screen;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(),
     * @Assert\Length( min=3,    
     * max=50, 
     * minMessage="La couleur doit contenir au minimun {{ limit }} caractères",
     * maxMessage="La couleur doit contenir moins de {{ limit }} caractères"
     * )
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Groups({"list_products", "detail_product"})
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Groups({"detail_product"})
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSizeScreen(): ?string
    {
        return $this->size_screen;
    }

    public function setSizeScreen(string $size_screen): self
    {
        $this->size_screen = $size_screen;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
