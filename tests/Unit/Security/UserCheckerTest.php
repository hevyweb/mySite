<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Exception\UserBannedException;
use App\Exception\UserNotActiveException;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    private UserChecker $userChecker;

    protected function setUp(): void
    {
        $this->userChecker = new UserChecker();
    }

    public function testCheckPreAuthThrowsExceptionWhenUserIsNotInstanceOfUser(): void
    {
        $invalidUser = $this->createMock(UserInterface::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Wrong user instance provided.');

        $this->userChecker->checkPreAuth($invalidUser);
    }

    public function testCheckPreAuthThrowsUserNotActiveExceptionWhenNotActive(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getActive')
            ->willReturn(false);

        $this->expectException(UserNotActiveException::class);
        $this->expectExceptionMessage('Your user account is not active. Please confirm your email.');

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthThrowsUserBannedExceptionWhenNotEnabled(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('getEnabled')
            ->willReturn(false);

        $this->expectException(UserBannedException::class);
        $this->expectExceptionMessage('User account is blocked due to site rules violation.');

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthPassesWhenUserActiveAndEnabled(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('getEnabled')
            ->willReturn(true);

        $this->userChecker->checkPreAuth($user);

        $this->assertTrue(true); // Asserts that no exception was thrown
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->userChecker->checkPostAuth($user);

        $this->assertTrue(true); // Asserts method executes safely without side effects
    }
}
