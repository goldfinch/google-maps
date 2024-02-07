<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'vendor:google-maps:ext:segment')]
class MapsSegmentExtensionCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:ext:segment';

    protected $description = 'Create MapsSegment extension';

    protected $path = '[psr4]/Extensions';

    protected $type = 'extension';

    protected $stub = './stubs/mapsegment-extension.stub';

    protected $prefix = 'Extension';
}
