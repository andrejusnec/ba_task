<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Services\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="Name must be at least {{ limit }} characters long",
     *     maxMessage="Name cannot be longer than {{ limit }} characters"
     * )
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=AddressBook::class, mappedBy="user", orphanRemoval=true)
     */
    private $addressBooks;

    /**
     * @ORM\OneToMany(targetEntity=QueryList::class, mappedBy="sender", orphanRemoval=true)
     */
    private $queryLists;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFileName;

    public function __construct()
    {
        $this->addressBooks = new ArrayCollection();
        $this->queryLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getAddressBooks(): Collection
    {
        return $this->addressBooks;
    }

    public function addAddressBook(AddressBook $addressBook): self
    {
        if (!$this->addressBooks->contains($addressBook)) {
            $this->addressBooks[] = $addressBook;
            $addressBook->setUser($this);
        }

        return $this;
    }

    public function removeAddressBook(AddressBook $addressBook): self
    {
        if ($this->addressBooks->removeElement($addressBook)) {
            // set the owning side to null (unless already changed)
            if ($addressBook->getUser() === $this) {
                $addressBook->setUser(null);
            }
        }

        return $this;
    }

    public function getQueryLists(): Collection
    {
        return $this->queryLists;
    }

    public function addQueryList(QueryList $queryList): self
    {
        if (!$this->queryLists->contains($queryList)) {
            $this->queryLists[] = $queryList;
            $queryList->setSender($this);
        }

        return $this;
    }

    public function removeQueryList(QueryList $queryList): self
    {
        if ($this->queryLists->removeElement($queryList)) {
            // set the owning side to null (unless already changed)
            if ($queryList->getSender() === $this) {
                $queryList->setSender(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->email;
    }

    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(?string $imageFileName): self
    {
        $this->imageFileName = $imageFileName;

        return $this;
    }

    public function getImagePath(): string
    {
        return UploaderHelper::USER_IMAGE.'/'.$this->getImageFileName();
    }
}
