<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'vendor:google-maps:ext:config')]
class MapConfigExtensionCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:ext:config';

    protected $description = 'Create MapConfig extension';

    protected $path = '[psr4]/Extensions';

    protected $type = 'extension';

    protected $stub = './stubs/mapconfig-extension.stub';

    protected $suffix = 'Extension';
}
