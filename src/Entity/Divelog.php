<?php

namespace App\Entity;

use App\Repository\DivelogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DivelogRepository::class)
 */
class Divelog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $divetype;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="divelogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDivetype(): ?string
    {
        return $this->divetype;
    }

    public function setDivetype(string $divetype): self
    {
        $this->divetype = $divetype;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
