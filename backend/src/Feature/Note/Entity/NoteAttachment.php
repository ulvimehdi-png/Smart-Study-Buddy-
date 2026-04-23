<?php

namespace App\Feature\Note\Entity;

use App\Feature\Note\Repository\NoteAttachmentRepository;
use App\General\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NoteAttachmentRepository::class)]
#[ORM\Table(name: 'note_attachments')]
#[ORM\Index(name: 'idx_note_attachments_note_id', columns: ['note_id'])]
#[ORM\HasLifecycleCallbacks]
class NoteAttachment
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Note::class, inversedBy: 'noteAttachments')]
    #[ORM\JoinColumn(name: 'note_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Note $note = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 512)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    private ?string $storagePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    private ?int $sizeBytes = null;

    #[ORM\Column(length: 64, nullable: true)]
    #[Assert\Length(min: 64, max: 64)]
    private ?string $checksumSha256 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getoriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setoriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = trim($originalFilename);

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = trim($storagePath);

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = null !== $mimeType ? trim($mimeType) : null;

        return $this;
    }

    public function getSizeBytes(): ?int
    {
        return $this->sizeBytes;
    }

    public function setSizeBytes(?int $sizeBytes): static
    {
        $this->sizeBytes = $sizeBytes;

        return $this;
    }

    public function getChecksumSha256(): ?string
    {
        return $this->checksumSha256;
    }

    public function setChecksumSha256(?string $checksumSha256): static
    {
        $this->checksumSha256 = null !== $checksumSha256 ? strtolower(trim($checksumSha256)) : null;

        return $this;
    }
}
