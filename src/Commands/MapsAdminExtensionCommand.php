<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'vendor:google-maps:ext:admin')]
class MapsAdminExtensionCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:ext:admin';

    protected $description = 'Create MapsAdmin extension';

    protected $path = '[psr4]/Extensions';

    protected $type = 'extension';

    protected $stub = './stubs/mapsadmin-extension.stub';

    protected $prefix = 'Extension';

    protected function execute($input, $output): int
    {
        parent::execute($input, $output);

        return Command::SUCCESS;
    }
}
