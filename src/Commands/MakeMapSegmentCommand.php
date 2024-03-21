<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Services\Templater;
use Goldfinch\Taz\Console\GeneratorCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

#[AsCommand(name: 'make:map-segment')]
class MakeMapSegmentCommand extends GeneratorCommand
{
    protected static $defaultName = 'make:map-segment';

    protected $description = 'Make new map segment';

    protected $no_arguments = true;

    protected function execute($input, $output): int
    {
        $segmentName = $this->askClassNameQuestion('Name of the segment (eg: Branch, Office)', $input, $output);

        if (!$segmentName) {
            return Command::FAILURE;
        }

        $segmentName = strtolower($segmentName);

        $fs = new Filesystem();

        $templater = Templater::create($input, $output, $this, 'goldfinch/google-maps');
        $theme = $templater->defineTheme();

        $fs->copy(
            BASE_PATH .
                '/vendor/goldfinch/google-maps/components/segment.json',
            'app/_schema/map-'.$segmentName.'.json',
        );

        $fs->copy(
            BASE_PATH .
                '/vendor/goldfinch/google-maps/templates/Goldfinch/GoogleMaps/Models/MapSegment.ss',
            'themes/'.$theme.'/templates/Components/Maps/'.$segmentName.'.ss',
        );

        // find config
        $config = $this->findYamlConfigFileByName('app-google-maps');

        // create new config if not exists
        if (!$config) {

            $command = $this->getApplication()->find('make:config');
            $command->run(new ArrayInput([
                'name' => 'google-maps',
                '--plain' => true,
                '--after' => 'goldfinch/google-maps',
                '--nameprefix' => 'app-',
            ]), $output);

            $config = $this->findYamlConfigFileByName('app-google-maps');
        }

        $ucfirst = ucfirst($segmentName);

        // update config
        $this->updateYamlConfig(
            $config,
            'Goldfinch\GoogleMaps\Models\MapSegment' . '.segment_types.' . $segmentName,
            [
                'label' => $ucfirst . ' map',
                'settings' => true,
                'markers' => true
            ],
        );

        return Command::SUCCESS;
    }
}
