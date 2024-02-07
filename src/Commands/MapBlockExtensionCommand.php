<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'vendor:google-maps:ext:block')]
class MapBlockExtensionCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:ext:block';

    protected $description = 'Create MapBlock extension';

    protected $path = '[psr4]/Extensions';

    protected $type = 'extension';

    protected $stub = './stubs/mapblock-extension.stub';

    protected $prefix = 'Extension';
}
