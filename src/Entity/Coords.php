<?php

namespace App\Entity;

use App\Repository\CoordsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CoordsRepository::class)
 */
class Coords
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", scale=20)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", scale=20)
     */
    private $longitude;

    /**
     * @ORM\OneToOne(targetEntity=Shop::class, inversedBy="coords", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $shop;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
