<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MemberRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
 */
class Member implements UserInterface
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $money;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="member")
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="member1")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity=Duty::class, mappedBy="asker")
     */
    private $dutyAsAsker;

    /**
     * @ORM\OneToMany(targetEntity=Duty::class, mappedBy="offerer")
     */
    private $dutyAsOfferer;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->dutyAsAsker = new ArrayCollection();
        $this->dutyAsOfferer = new ArrayCollection();
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    //Provi. les roles sont tous members
    public function getRoles(){
        // return gettype(['ROLE_MEMBER']);
        // $toReturn = '';
        // foreach ($$this->roles as $value) {
        //     $toReturn .= $value.', ';
        // }
        // return substr($toReturn, 0, strlen($toReturn));
        return $this->roles;
    }

    // public function getRolesAsArray(): ?array
    // {
    //     return $this->roles;
    // }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setMember($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getMember() === $this) {
                $notification->setMember(null);
            }
        }

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
            $conversation->setMember1($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            // set the owning side to null (unless already changed)
            if ($conversation->getMember1() === $this) {
                $conversation->setMember1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Duty[]
     */
    public function getDutyAsAsker(): Collection
    {
        return $this->dutyAsAsker;
    }

    public function addDutyAsAsker(Duty $dutyAsAsker): self
    {
        if (!$this->dutyAsAsker->contains($dutyAsAsker)) {
            $this->dutyAsAsker[] = $dutyAsAsker;
            $dutyAsAsker->setAsker($this);
        }

        return $this;
    }

    public function removeDutyAsAsker(Duty $dutyAsAsker): self
    {
        if ($this->dutyAsAsker->contains($dutyAsAsker)) {
            $this->dutyAsAsker->removeElement($dutyAsAsker);
            // set the owning side to null (unless already changed)
            if ($dutyAsAsker->getAsker() === $this) {
                $dutyAsAsker->setAsker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Duty[]
     */
    public function getDutyAsOfferer(): Collection
    {
        return $this->dutyAsOfferer;
    }

    public function addDutyAsOfferer(Duty $dutyAsOfferer): self
    {
        if (!$this->dutyAsOfferer->contains($dutyAsOfferer)) {
            $this->dutyAsOfferer[] = $dutyAsOfferer;
            $dutyAsOfferer->setOfferer($this);
        }

        return $this;
    }

    public function removeDutyAsOfferer(Duty $dutyAsOfferer): self
    {
        if ($this->dutyAsOfferer->contains($dutyAsOfferer)) {
            $this->dutyAsOfferer->removeElement($dutyAsOfferer);
            // set the owning side to null (unless already changed)
            if ($dutyAsOfferer->getOfferer() === $this) {
                $dutyAsOfferer->setOfferer(null);
            }
        }

        return $this;
    }

    public function getUsername(){
        return $this->email;
    }

    public function eraseCredentials() {}

    public function getSalt() {}
    
    public function __toString(){
        return $this->firstname." ".$this->name;
    }
}
