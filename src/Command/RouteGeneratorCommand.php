<?php declare(strict_types=1);

namespace MichaelCozzolino\RouteGeneratorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'routes:generate',
    description: 'Generate routes for your frontend app',
)]
class RouteGeneratorCommand extends Command
{
    public function __construct(protected RouterInterface $router)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = [];
        foreach ($this->router->getRouteCollection()->all() as $routeName => $route) {
            $routes[$routeName] = [
                'name' => $routeName,
                'path' => $route->getPath(),
            ];
        }

        $jsonRoutes = json_encode($routes, JSON_PRETTY_PRINT);
        file_put_contents('routes.json', $jsonRoutes);

        return Command::SUCCESS;
    }
}
