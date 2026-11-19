<?php

namespace App\Tests\Unit\Twig;

use App\Entity\Page;
use App\Twig\PageExtension;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\TwigFunction;

class PageExtensionTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PageExtension $extension;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->extension = new PageExtension($this->entityManager);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertEquals('page_content', $functions[0]->getName());
        $this->assertTrue($functions[0]->needsContext());
        $this->assertTrue($functions[0]->needsEnvironment());
    }

    public function testGetPageContent(): void
    {
        $request = Request::create('/some-route');
        $request->setLocale('uk');
        $request->attributes->set('_route', 'homepage');

        $app = new class($request) {
            public function __construct(private Request $request)
            {
            }

            public function getRequest(): Request
            {
                return $this->request;
            }
        };

        $context = ['app' => $app];

        $page = new Page();
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(['route' => 'homepage', 'locale' => 'uk'])
            ->willReturn([$page]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Page::class)
            ->willReturn($repository);

        $environment = $this->createMock(Environment::class);
        $environment->expects($this->once())
            ->method('render')
            ->with('common/page.html.twig', [
                'pages' => [$page],
            ])
            ->willReturn('<div class="page-content">Content</div>');

        $result = $this->extension->getPageContent($environment, $context);

        $this->assertEquals('<div class="page-content">Content</div>', $result);
    }
}
