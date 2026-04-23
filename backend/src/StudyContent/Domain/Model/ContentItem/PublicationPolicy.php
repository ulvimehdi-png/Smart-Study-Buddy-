<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

final readonly class PublicationPolicy
{
    public function __construct(
        private bool $requiresApproval,
        private bool $allowPersonal,
        private bool $allowSharedGroup,
        private bool $allowInstitutionManaged,
    )
    {
    }

    public static function standard(): self
    {
        return new self(requiresApproval: true, allowPersonal: true,
            allowSharedGroup: true, allowInstitutionManaged: false,);
    }

    public function requiresApproval(): bool
    {
        return $this->requiresApproval;
    }

    public function allowsScope(SharingScope $scope): bool
    {
        return match ($scope) {
            SharingScope::PERSONAL => $this->allowPersonal,
            SharingScope::SHARED_GROUP => $this->allowSharedGroup,
            SharingScope::INSTITUTION_MANAGED => $this->allowInstitutionManaged,
        };
    }
}
