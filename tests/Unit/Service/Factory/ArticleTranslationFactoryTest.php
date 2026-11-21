<?php

namespace App\Tests\Unit\Service\Factory;

use App\Entity\User;
use App\Exception\IncorrectUserException;
use App\Service\Factory\ArticleTranslationFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class ArticleTranslationFactoryTest extends TestCase
{
    private Security&MockObject $security;
    private Request&MockObject $request;
    private ArticleTranslationFactory $factory;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->request = $this->createMock(Request::class);
        $this->factory = new ArticleTranslationFactory($this->security, $this->request);
    }

    public function testBuildThrowsExceptionWhenUserIsNotInstanceOfUser(): void
    {
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->expectException(IncorrectUserException::class);
        $this->expectExceptionMessage('User is not instance of User');

        $this->factory->build();
    }

    public function testBuildCreatesArticleTranslationSuccessfully(): void
    {
        $user = $this->createMock(User::class);
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->request->expects($this->once())
            ->method('getLocale')
            ->willReturn('uk');

        $translation = $this->factory->build();

        $this->assertSame('uk', $translation->getLocale());
        $this->assertSame($user, $translation->getCreatedBy());
        $this->assertSame($user, $translation->getUpdatedBy());
        $this->assertInstanceOf(\DateTimeImmutable::class, $translation->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $translation->getUpdatedAt());
        $this->assertNull($translation->getArticle()->getId());
    }
}
