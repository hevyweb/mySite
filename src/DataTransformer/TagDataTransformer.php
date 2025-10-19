<?php

namespace App\DataTransformer;

use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @template-implements DataTransformerInterface<ArrayCollection, string>
 */
readonly class TagDataTransformer implements DataTransformerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param mixed|Collection<int, Tag> $value
     */
    #[\Override]
    public function transform(mixed $value): string
    {
        if (!empty($value)) {
            return implode(', ', array_map(fn (Tag $tag) => $tag->getName(), $value->getValues()));
        }

        return '';
    }

    /**
     * @return ArrayCollection<int, Tag>
     */
    #[\Override]
    public function reverseTransform(mixed $value): ArrayCollection
    {
        if (!empty($value)) {
            $newTagNames = $this->cleanInputData($value);
            $existingTags = $this->entityManager->getRepository(Tag::class)->findBy(['name' => $newTagNames]);

            return $this->extendTagCollection($existingTags, $newTagNames);
        }

        return $this->createEmptyCollection();
    }

    /**
     * @return array<string>
     */
    private function cleanInputData(string $tagString): array
    {
        return array_unique(array_filter(array_map('trim', explode(',', $tagString))));
    }

    /**
     * @param Tag[]         $existingTags
     * @param array<string> $names
     *
     * @return ArrayCollection<int, Tag>
     */
    private function extendTagCollection(array $existingTags, array $names): ArrayCollection
    {
        $tags = $this->createEmptyCollection();

        foreach ($names as $name) {
            $tag = $this->findInCollection($existingTags, $name);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($name);
            }
            $tags->add($tag);
        }

        return $tags;
    }

    /**
     * This is a crutch for psalm, because it does not line generic collections.
     *
     * @return ArrayCollection<int, Tag>
     */
    private function createEmptyCollection(): ArrayCollection
    {
        return new ArrayCollection();
    }

    /**
     * @param Tag[] $existingTags
     */
    private function findInCollection(array $existingTags, string $name): ?Tag
    {
        foreach ($existingTags as $tag) {
            if ($tag->getName() === $name) {
                return $tag;
            }
        }

        return null;
    }
}
