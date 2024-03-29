<?php

namespace App\DataTransformer;

use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class TagDataTransformer implements DataTransformerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function transform(mixed $value): string
    {
        if (!empty($value)) {
            return implode(', ', array_map(fn (Tag $tag) => $tag->getName(), $value->getValues()));
        }

        return '';
    }

    public function reverseTransform(mixed $value): ArrayCollection
    {
        if (!empty($value)) {
            $names = array_unique(array_filter(array_map('trim', explode(',', $value))));

            $existingTags = $this->entityManager->getRepository(Tag::class)->findBy(['name' => $names]);

            return $this->fullFillCollection($existingTags, $names);
        }

        return new ArrayCollection();
    }

    private function fullFillCollection($existingTags, $names): ArrayCollection
    {
        $tags = new ArrayCollection();

        foreach ($names as $name) {
            if (!($tag = $this->findByName($existingTags, $name))) {
                $tag = new Tag();
                $tag->setName($name);
            }
            $tags->add($tag);
        }

        return $tags;
    }

    private function findByName($existingTags, $name): ?Tag
    {
        foreach ($existingTags as $tag) {
            if ($tag->getName() === $name) {
                return $tag;
            }
        }

        return null;
    }
}
