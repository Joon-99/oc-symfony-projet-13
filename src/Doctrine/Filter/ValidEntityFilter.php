<?php

namespace App\Doctrine\Filter;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/** 
 * Injects a `valid = true` condition into the generated SQL for every ORM query
 * on entities extending BaseEntity.
 */
final class ValidEntityFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->getReflectionClass()->isSubclassOf(BaseEntity::class)) {
            return '';
        }

        return sprintf('%s.valid = true', $targetTableAlias);
    }
}
