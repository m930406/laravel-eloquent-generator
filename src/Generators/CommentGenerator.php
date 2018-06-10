<?php

declare(strict_types=1);

namespace Corp104\Eloquent\Generator\Generators;

use function in_array;
use function preg_match;
use function strpos;

class CommentGenerator
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @param TypeGenerator $typeGenerator
     */
    public function __construct(TypeGenerator $typeGenerator)
    {
        $this->typeGenerator = $typeGenerator;
    }

    /**
     * @param array $fields
     * @return string
     */
    public function generate(array $fields): string
    {
        $comment = '/**' . PHP_EOL;

        foreach ($fields as $field => $property) {
            $propertyType = $this->typeGenerator->generate(
                $property['type'],
                $this->isNullable($property),
                $this->resolveComment($property)
            );

            $comment .= " * @property ${propertyType} " . $property['field'] . PHP_EOL;
        }

        $comment .= ' */';

        return $comment;
    }

    /**
     * @param array $property
     * @return bool
     */
    private function isNullable(array $property): bool
    {
        return isset($property['decorators']) && in_array('nullable', $property['decorators'], true);
    }

    /**
     * @param array $property
     * @return null|string
     */
    private function resolveComment(array $property): ?string
    {
        if (empty($property['decorators'])) {
            return null;
        }

        $key = collect($property['decorators'])->search(function ($item) {
            return false !== strpos($item, 'comment');
        });

        if (false === $key) {
            return null;
        }

        preg_match('/comment\(\'(.*)\'\)/', $property['decorators'][$key], $matches);

        return $matches[1];
    }
}
