<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'vendor:google-maps:ext:config')]
class MapConfigExtensionCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:ext:config';

    protected $description = 'Create MapConfig extension';

    protected $path = '[psr4]/Extensions';

    protected $type = 'extension';

    protected $stub = './stubs/mapconfig-extension.stub';

    protected $prefix = 'Extension';

    protected function execute($input, $output): int
    {
        parent::execute($input, $output);

        return Command::SUCCESS;
    }
}
