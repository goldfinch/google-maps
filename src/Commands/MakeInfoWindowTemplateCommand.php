<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'make:map-infowindow:template')]
class MakeInfoWindowTemplateCommand extends GeneratorCommand
{
    protected static $defaultName = 'make:map-infowindow:template';

    protected $description = 'Create Map InfoWindow template';

    protected $path = 'themes/[theme]/templates/Components/Maps/InfoWindows';

    protected $type = 'infowindow template';

    protected $stub = 'infowindow-template.stub';

    protected $extension = '.ss';
}
