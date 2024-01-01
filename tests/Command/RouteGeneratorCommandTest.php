<?php declare(strict_types=1);

namespace MichaelCozzolino\RouteGeneratorBundle\Tests\Command;

use MichaelCozzolino\RouteGeneratorBundle\Command\RouteGeneratorCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RouteGeneratorCommandTest extends TestCase
{
    protected RouterInterface & MockObject $routerMock;

    protected CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routerMock = $this->createMock(RouterInterface::class);

        $application = new Application($this->createMock(KernelInterface::class));
        $application->add(new RouteGeneratorCommand($this->routerMock));

        $command = $application->find(RouteGeneratorCommand::getDefaultName());

        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $routeCollectionMock = $this->createMock(RouteCollection::class);

        $this->routerMock->expects(self::once())
                         ->method('getRouteCollection')
                         ->willReturn($routeCollectionMock);

        $routeCollectionMock->expects(self::once())
                            ->method('all')
                            ->willReturn([
                                'route-1' => new Route('/path-to-route-1'),
                                'route-2' => new Route('/path-to-route-2/{id}'),
                            ]);

        $this->commandTester->execute([]);

        self::assertSame(
            json_encode([
                'route-1' => [
                    'name' => 'route-1',
                    'path' => '/path-to-route-1',
                ],
                'route-2' => [
                    'name' => 'route-2',
                    'path' => '/path-to-route-2/{id}',
                ],
            ], JSON_PRETTY_PRINT),
            file_get_contents('routes.json')
        );
    }
}
