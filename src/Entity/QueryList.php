<?php

namespace App\Entity;

use App\Repository\QueryListRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QueryListRepository::class)
 */
class QueryList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="queryLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $receiver;

    /**
     * @ORM\ManyToOne(targetEntity=AddressBook::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $addressRecord;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sendStatus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $receiveStatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

    public function setReceiver($receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getAddressRecord(): ?AddressBook
    {
        return $this->addressRecord;
    }

    public function setAddressRecord(?AddressBook $addressRecord): self
    {
        $this->addressRecord = $addressRecord;

        return $this;
    }

    public function getSendStatus(): ?bool
    {
        return $this->sendStatus;
    }

    public function setSendStatus(?bool $sendStatus): self
    {
        $this->sendStatus = $sendStatus;

        return $this;
    }

    public function getReceiveStatus(): ?bool
    {
        return $this->receiveStatus;
    }

    public function setReceiveStatus(?bool $receiveStatus): self
    {
        $this->receiveStatus = $receiveStatus;

        return $this;
    }
}
