<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

#[AsCommand(name: 'vendor:google-maps')]
class MapsSetCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps';

    protected $description = 'Set of all [goldfinch/google-maps] commands';

    protected $no_arguments = true;

    protected function execute($input, $output): int
    {
        $command = $this->getApplication()->find('vendor:google-maps:ext:admin');
        $command->run(new ArrayInput(['name' => 'MapsAdmin']), $output);

        $command = $this->getApplication()->find('vendor:google-maps:ext:config');
        $command->run(new ArrayInput(['name' => 'MapConfig']), $output);

        $command = $this->getApplication()->find('vendor:google-maps:ext:block');
        $command->run(new ArrayInput(['name' => 'MapBlock']), $output);

        $command = $this->getApplication()->find('vendor:google-maps:ext:segment');
        $command->run(new ArrayInput(['name' => 'MapSegment']), $output);

        $command = $this->getApplication()->find('vendor:google-maps:config');
        $command->run(new ArrayInput(['name' => 'google-maps']), $output);

        $command = $this->getApplication()->find('vendor:google-maps:templates');
        $command->run(new ArrayInput([]), $output);

        return Command::SUCCESS;
    }
}
