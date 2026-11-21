<?php

namespace App\Tests\Unit\DataTransformer;

use App\DataTransformer\TagDataTransformer;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagDataTransformerTest extends TestCase
{
    /** @var ObjectRepository<Tag>&MockObject */
    private ObjectRepository&MockObject $tagRepository;
    private TagDataTransformer $transformer;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tagRepository = $this->createMock(ObjectRepository::class);

        $entityManager->method('getRepository')
            ->with(Tag::class)
            ->willReturn($this->tagRepository);

        $this->transformer = new TagDataTransformer($entityManager);
    }

    public function testTransformWithTags(): void
    {
        $tag1 = new Tag();
        $tag1->setName('PHP');

        $tag2 = new Tag();
        $tag2->setName('Symfony');

        $collection = new ArrayCollection([$tag1, $tag2]);

        $result = $this->transformer->transform($collection);

        $this->assertEquals('PHP, Symfony', $result);
    }

    public function testTransformWithEmptyValue(): void
    {
        $result = $this->transformer->transform('');
        $this->assertEquals('', $result);
    }

    public function testReverseTransformWithEmptyString(): void
    {
        $result = $this->transformer->reverseTransform('');
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testReverseTransformWithExistingAndNewTags(): void
    {
        $existingTag = new Tag();
        $existingTag->setName('PHP');

        $this->tagRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['PHP', 'Symfony']])
            ->willReturn([$existingTag]);

        $result = $this->transformer->reverseTransform(' PHP, Symfony, PHP ');

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);

        $this->assertSame($existingTag, $result[0]);
        $this->assertEquals('Symfony', $result[1]->getName());
        $this->assertInstanceOf(Tag::class, $result[1]);
    }
}
