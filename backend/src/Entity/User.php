<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_.-]+$/', message: 'Username contains invalid characters.')]
    #[Groups(['read', 'user:read', 'user:write'])]
    private ?string $username = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[Groups(['read', 'user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(name: 'hashed_password', length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    #[Groups(['read', 'user:read', 'user:write'])]
    private ?string $displayName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['read', 'user:read'])]
    private ?string $profileImagePath = null;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['read', 'user:read'])]
    private ?bool $isActive;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['read', 'user:read'])]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['read', 'user:read'])]
    private ?int $points = 0;

    #[ORM\OneToOne(targetEntity: Settings::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Settings $settings = null;

    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'user')]
    private Collection $subjects;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'user')]
    private Collection $sessions;

    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'user')]
    private Collection $notes;

    #[ORM\OneToMany(targetEntity: Resource::class, mappedBy: 'user')]
    private Collection $resources;

    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'user')]
    private Collection $tags;

    public function __construct()
    {
        $this->isActive = true;
        $this->subjects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = trim($username);
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = null !== $email ? mb_strtolower(trim($email)) : null;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = null !== $displayName ? trim($displayName) : null;
        return $this;
    }

    public function getProfileImagePath(): ?string
    {
        return $this->profileImagePath;
    }

    public function setProfileImagePath(?string $profileImagePath): static
    {
        $this->profileImagePath = null !== $profileImagePath ? trim($profileImagePath) : null;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_unique($roles);
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;
        return $this;
    }

    public function addPoints(int $pointsToAdd): static
    {
        $this->points += $pointsToAdd;
        return $this;
    }

    public function getSettings(): ?Settings
    {
        return $this->settings;
    }

    public function setSettings(Settings $settings): static
    {
        if ($settings->getUser() !== $this) {
            $settings->setUser($this);
        }
        $this->settings = $settings;
        return $this;
    }

    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): static
    {
        if ($this->subjects->contains($subject)) {
            return $this;
        }

        $this->subjects->add($subject);
        $subject->setUser($this);
        return $this;
    }

    public function removeSubject(Subject $subject): static
    {
        if (!$this->subjects->removeElement($subject)) {
            return $this;
        }

        if ($subject->getUser() === $this) {
            $subject->setUser(null);
        }

        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if ($this->tasks->contains($task)) {
            return $this;
        }

        $this->tasks->add($task);
        $task->setUser($this);
        return $this;
    }

    public function removeTask(Task $task): static
    {
        if (!$this->tasks->removeElement($task)) {
            return $this;
        }

        if ($task->getUser() === $this) {
            $task->setUser(null);
        }

        return $this;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if ($this->sessions->contains($session)) {
            return $this;
        }

        $this->sessions->add($session);
        $session->setUser($this);
        return $this;
    }

    public function removeSession(Session $session): static
    {
        if (!$this->sessions->removeElement($session)) {
            return $this;
        }

        if ($session->getUser() === $this) {
            $session->setUser(null);
        }

        return $this;
    }

    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if ($this->notes->contains($note)) {
            return $this;
        }

        $this->notes->add($note);
        $note->setUser($this);
        return $this;
    }

    public function removeNote(Note $note): static
    {
        if (!$this->notes->removeElement($note)) {
            return $this;
        }

        if ($note->getUser() === $this) {
            $note->setUser(null);
        }

        return $this;
    }

    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): static
    {
        if ($this->resources->contains($resource)) {
            return $this;
        }

        $this->resources->add($resource);
        $resource->setUser($this);
        return $this;
    }

    public function removeResource(Resource $resource): static
    {
        if (!$this->resources->removeElement($resource)) {
            return $this;
        }

        if ($resource->getUser() === $this) {
            $resource->setUser(null);
        }

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if ($this->tags->contains($tag)) {
            return $this;
        }

        $this->tags->add($tag);
        $tag->setUser($this);
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if (!$this->tags->removeElement($tag)) {
            return $this;
        }

        if ($tag->getUser() === $this) {
            $tag->setUser(null);
        }

        return $this;
    }
}