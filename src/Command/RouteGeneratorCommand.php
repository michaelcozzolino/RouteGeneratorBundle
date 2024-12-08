<?php declare(strict_types=1);

namespace MichaelCozzolino\RouteGeneratorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use function array_keys;
use function array_map;
use function file_put_contents;
use function implode;
use function str_starts_with;
use const PHP_EOL;

/**
 * @psalm-api
 */
#[AsCommand(
    name: 'routes:generate',
    description: 'Generate routes for your frontend app',
)]
class RouteGeneratorCommand extends Command
{
    public const ROUTES_FILE_NAME     = 'routes.json';

    public const ROUTE_TYPE_NAME      = 'RouteName';

    public const ROUTE_TYPE_FILE_NAME = self::ROUTE_TYPE_NAME . '.ts';

    public function __construct(protected RouterInterface $router)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = [];

        foreach ($this->router->getRouteCollection()->all() as $routeName => $route) {
            if ($this->isInternalRoute($routeName)) {
                continue;
            }

            $routes[$routeName] = [
                'name' => $routeName,
                'path' => $route->getPath(),
            ];
        }

        if ($routes === []) {
            return Command::SUCCESS;
        }

        $jsonRoutes = json_encode($routes, JSON_PRETTY_PRINT);

        file_put_contents(self::ROUTES_FILE_NAME, $jsonRoutes);

        file_put_contents(self::ROUTE_TYPE_FILE_NAME, $this->generateTypescriptType(
            array_map(fn(string $route) => "'$route'", array_keys($routes)),
            self::ROUTE_TYPE_NAME
        ));

        return Command::SUCCESS;
    }

    protected function isInternalRoute(string $routeName): bool
    {
        $internalRoutePrefixes = [
            '_preview_error',
            '_wdt',
            '_profiler',
        ];

        foreach ($internalRoutePrefixes as $internalRoutePrefix) {
            if (str_starts_with($routeName, $internalRoutePrefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string>     $routeNames
     * @param non-empty-string $typeName
     *
     * @return non-empty-string
     */
    protected function generateTypescriptType(array $routeNames, string $typeName): string
    {
        $routeNamesUnionType = implode(' |' . PHP_EOL, $routeNames);

        return "type $typeName = $routeNamesUnionType;" . PHP_EOL . PHP_EOL . "export default $typeName;";
    }
}
