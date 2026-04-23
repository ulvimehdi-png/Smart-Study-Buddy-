<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Repository;

use App\StudyContent\Domain\Model\Subject\Subject;
use App\StudyContent\Domain\Model\Subject\SubjectId;

interface SubjectRepository
{
    public function save(Subject $subject): void;

    public function get(SubjectId $id): Subject;

    public function find(SubjectId $id): ?Subject;
}
