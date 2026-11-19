<?php

namespace App\Tests\Unit\Security;

use App\Security\AccessDeniedHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AccessDeniedHandlerTest extends TestCase
{
    private Environment $twig;
    private TranslatorInterface $translator;
    private AccessDeniedHandler $accessDeniedHandler;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->accessDeniedHandler = new AccessDeniedHandler($this->twig, $this->translator);
    }

    public function testHandleReturnsForbiddenResponseWithRenderedTemplate(): void
    {
        $request = Request::create('/admin');
        $exception = new AccessDeniedException('Access denied.');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('Access Denied.', [], 'security')
            ->willReturn('Translated Access Denied.');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('security/access_denied.html.twig', [
                'title' => 'Translated Access Denied.',
            ])
            ->willReturn('<html>Access Denied Page</html>');

        $response = $this->accessDeniedHandler->handle($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame('<html>Access Denied Page</html>', $response->getContent());
    }
}
