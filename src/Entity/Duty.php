<?php

namespace App\Entity;

use App\Repository\DutyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DutyRepository::class)
 */
class Duty
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $checkedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $askerValidAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $offererValidAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $doneAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $setbackAt;

    /**
     * @ORM\Column(type="float")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $place;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $yesVote = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $noVote = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $voteCommentary = [];

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="duty")
     */
    private $conversations;

    /**
     * @ORM\ManyToOne(targetEntity=DutyType::class, inversedBy="duties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dutyType;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="dutyAsAsker")
     * @ORM\JoinColumn(nullable=false)
     */
    private $asker;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="dutyAsOfferer")
     */
    private $offerer;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCheckedAt(): ?\DateTimeInterface
    {
        return $this->checkedAt;
    }

    public function setCheckedAt(?\DateTimeInterface $checkedAt): self
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }

    public function getAskerValidAt(): ?\DateTimeInterface
    {
        return $this->askerValidAt;
    }

    public function setAskerValidAt(?\DateTimeInterface $askerValidAt): self
    {
        $this->askerValidAt = $askerValidAt;

        return $this;
    }

    public function getOffererValidAt(): ?\DateTimeInterface
    {
        return $this->offererValidAt;
    }

    public function setOffererValidAt(?\DateTimeInterface $offererValidAt): self
    {
        $this->offererValidAt = $offererValidAt;

        return $this;
    }

    public function getDoneAt(): ?\DateTimeInterface
    {
        return $this->doneAt;
    }

    public function setDoneAt(?\DateTimeInterface $doneAt): self
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    public function getSetbackAt(): ?\DateTimeInterface
    {
        return $this->setbackAt;
    }

    public function setSetbackAt(?\DateTimeInterface $setbackAt): self
    {
        $this->setbackAt = $setbackAt;

        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getYesVote(): ?array
    {
        return $this->yesVote;
    }

    public function setYesVote(?array $yesVote): self
    {
        $this->yesVote = $yesVote;

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
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->setDuty($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            // set the owning side to null (unless already changed)
            if ($conversation->getDuty() === $this) {
                $conversation->setDuty(null);
            }
        }

        return $this;
    }

    public function getDutyType(): ?DutyType
    {
        return $this->dutyType;
    }

    public function setDutyType(?DutyType $dutyType): self
    {
        $this->dutyType = $dutyType;

        return $this;
    }

    public function getAsker(): ?Member
    {
        return $this->asker;
    }

    public function setAsker(?Member $asker): self
    {
        $this->asker = $asker;

        return $this;
    }

    public function getOfferer(): ?Member
    {
        return $this->offerer;
    }

    public function setOfferer(?Member $offerer): self
    {
        $this->offerer = $offerer;

        return $this;
    }

    public function __toString(){
        return $this->title;
    }
}
