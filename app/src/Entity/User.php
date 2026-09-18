<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

// NOTE PEDAGOGIQUE : cette entité ne respecte pas les bonnes pratiques de sécurité
// (voir Cours OWASP - A02 Cryptographic Failures) : mot de passe hashé en MD5,
// réponse de récupération de mot de passe stockée en clair.
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    // Volontairement faible : MD5, sans sel. Voir OWASP A02.
    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private array $roles = [];

    // "Question de sécurité" en clair, devinable. Voir OWASP A04 / A07.
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $securityAnswer = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles ?: ['ROLE_USER'];
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function getSecurityAnswer(): ?string
    {
        return $this->securityAnswer;
    }

    public function setSecurityAnswer(?string $securityAnswer): static
    {
        $this->securityAnswer = $securityAnswer;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
