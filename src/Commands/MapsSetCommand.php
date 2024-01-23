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

    protected function execute($input, $output): int
    {
        $command = $this->getApplication()->find(
            'vendor:google-maps:ext:admin',
        );
        $input = new ArrayInput(['name' => 'MapsAdmin']);
        $command->run($input, $output);

        $command = $this->getApplication()->find(
            'vendor:google-maps:ext:config',
        );
        $input = new ArrayInput(['name' => 'MapConfig']);
        $command->run($input, $output);

        $command = $this->getApplication()->find(
            'vendor:google-maps:ext:block',
        );
        $input = new ArrayInput(['name' => 'MapBlock']);
        $command->run($input, $output);

        $command = $this->getApplication()->find(
            'vendor:google-maps:ext:segment',
        );
        $input = new ArrayInput(['name' => 'MapSegment']);
        $command->run($input, $output);

        $command = $this->getApplication()->find('vendor:google-maps:config');
        $input = new ArrayInput(['name' => 'google-maps']);
        $command->run($input, $output);

        $command = $this->getApplication()->find('vendor:google-maps:templates');
        $input = new ArrayInput([]);
        $command->run($input, $output);

        return Command::SUCCESS;
    }
}
