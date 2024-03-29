<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'vendor:google-maps:config')]
class MapConfigCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:config';

    protected $description = 'Create Google Maps YML config';

    protected $path = 'app/_config';

    protected $type = 'config';

    protected $stub = './stubs/config.stub';

    protected $extension = '.yml';
}
