<?php declare(strict_types=1);

namespace MichaelCozzolino\SymfonyRouteGeneratorBundle\Tests\Command;

use MichaelCozzolino\SymfonyRouteGeneratorBundle\Command\RouteGeneratorCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use function file_get_contents;
use function json_encode;
use function unlink;
use const JSON_PRETTY_PRINT;

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

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(RouteGeneratorCommand::ROUTES_FILE_NAME);
        @unlink(RouteGeneratorCommand::ROUTE_TYPE_FILE_NAME);
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
            file_get_contents(RouteGeneratorCommand::ROUTES_FILE_NAME)
        );

        $expectedRouteTypeFileContent = <<<TS
type RouteName = 'route-1' |
'route-2';

export default RouteName;
TS;
        self::assertSame($expectedRouteTypeFileContent, file_get_contents(RouteGeneratorCommand::ROUTE_TYPE_FILE_NAME));
    }

    public function testExecuteWhenNoRouteExists(): void
    {
        $routeCollectionMock = $this->createMock(RouteCollection::class);

        $this->routerMock->expects(self::once())
                         ->method('getRouteCollection')
                         ->willReturn($routeCollectionMock);

        $routeCollectionMock->expects(self::once())
                            ->method('all')
                            ->willReturn([]);

        $this->commandTester->execute([]);

        self::assertFalse(@file_get_contents(RouteGeneratorCommand::ROUTES_FILE_NAME));
        self::assertFalse(@file_get_contents(RouteGeneratorCommand::ROUTE_TYPE_FILE_NAME));
    }

    public function testExecuteWhenAtLeastOneRouteIsASymfonyInternalRoute(): void
    {
        $routeCollectionMock = $this->createMock(RouteCollection::class);

        $this->routerMock->expects(self::once())
                         ->method('getRouteCollection')
                         ->willReturn($routeCollectionMock);

        $routeCollectionMock->expects(self::once())
                            ->method('all')
                            ->willReturn([
                                '_profiler_something' => new Route('/path-to-profiler'),
                            ]);

        $this->commandTester->execute([]);

        self::assertFalse(@file_get_contents(RouteGeneratorCommand::ROUTES_FILE_NAME));
        self::assertFalse(@file_get_contents(RouteGeneratorCommand::ROUTE_TYPE_FILE_NAME));
    }
}
