<?php

namespace App\Entity;

use App\Repository\DutyTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DutyTypeRepository::class)
 */
class DutyType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $hourlyPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $noVote = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $yesVote = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $voteCommentary = [];

    /**
     * @ORM\OneToMany(targetEntity=Duty::class, mappedBy="dutyType")
     */
    private $duties;

    public function __construct()
    {
        $this->duties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getHourlyPrice(): ?int
    {
        return $this->hourlyPrice;
    }

    public function setHourlyPrice(int $hourlyPrice): self
    {
        $this->hourlyPrice = $hourlyPrice;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNoVote(): ?array
    {
        return $this->noVote;
    }

    public function setNoVote(?array $noVote): self
    {
        $this->noVote = $noVote;

        return $this;
    }

    public function getYesVote(): ?array
    {
        return $this->yesVote;
    }

    public function setYesVote(?array $yesVote): self
    {
        $this->yesVote = $yesVote;

        return $this;
    }

    public function getVoteCommentary(): ?array
    {
        return $this->voteCommentary;
    }

    public function setVoteCommentary(?array $voteCommentary): self
    {
        $this->voteCommentary = $voteCommentary;

        return $this;
    }

    /**
     * @return Collection|Duty[]
     */
    public function getDuties(): Collection
    {
        return $this->duties;
    }

    public function addDuty(Duty $duty): self
    {
        if (!$this->duties->contains($duty)) {
            $this->duties[] = $duty;
            $duty->setDutyType($this);
        }

        return $this;
    }

    public function removeDuty(Duty $duty): self
    {
        if ($this->duties->contains($duty)) {
            $this->duties->removeElement($duty);
            // set the owning side to null (unless already changed)
            if ($duty->getDutyType() === $this) {
                $duty->setDutyType(null);
            }
        }

        return $this;
    }
}
