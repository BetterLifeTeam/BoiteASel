<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 */
class Conversation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="conversation")
     */
    private $messages;

    /**
     * @ORM\ManyToOne(targetEntity=Duty::class, inversedBy="conversations")
     */
    private $duty;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="conversations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member1;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="conversations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member2;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function getDuty(): ?Duty
    {
        return $this->duty;
    }

    public function setDuty(?Duty $duty): self
    {
        $this->duty = $duty;

        return $this;
    }

    public function getMember1(): ?Member
    {
        return $this->member1;
    }

    public function setMember1(?Member $member1): self
    {
        $this->member1 = $member1;

        return $this;
    }

    public function getMember2(): ?Member
    {
        return $this->member2;
    }

    public function setMember2(?Member $member2): self
    {
        $this->member2 = $member2;

        return $this;
    }
}
