<?php

namespace Goldfinch\GoogleMaps\Commands;

use Symfony\Component\Finder\Finder;
use Goldfinch\Taz\Services\Templater;
use Goldfinch\Taz\Services\InputOutput;
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
        $io = new InputOutput($input, $output);

        $segmentName = $io->question('Name of the segment (lowercase, dash, A-z0-9)', null, function ($answer) use ($io) {

            if (!is_string($answer) || $answer === null) {
                throw new \RuntimeException(
                    'Invalid name'
                );
            } else if (strlen($answer) < 2) {
                throw new \RuntimeException(
                    'Too short name'
                );
            } else if(!preg_match('/^([A-z0-9\-]+)$/', $answer)) {
                throw new \RuntimeException(
                    'Name can contains letter, numbers and dash'
                );
            }

            return $answer;
        });

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
                '/vendor/goldfinch/google-maps/components/segment.ss',
            'themes/'.$theme.'/templates/Components/Maps/'.$segmentName.'.ss',
        );

        if (!$this->setSegmentInConfig($segmentName)) {
            // create config

            $command = $this->getApplication()->find('vendor:google-maps:config');

            $arguments = [
                'name' => 'google-maps',
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, $output);

            $this->setSegmentInConfig($segmentName);
        }

        $io->right('Map segment has been added');

        return Command::SUCCESS;
    }

    private function setSegmentInConfig($segmentName)
    {
        $rewritten = false;

        $finder = new Finder();
        $files = $finder->in(BASE_PATH . '/app/_config')->files()->contains('Goldfinch\GoogleMaps\Models\MapSegment');

        foreach ($files as $file) {

            // stop after first replacement
            if ($rewritten) {
                break;
            }

            if (strpos($file->getContents(), 'segment_types') !== false) {

                $ucfirst = ucfirst($segmentName);

                $newContent = $this->addToLine(
                    $file->getPathname(),
                    'segment_types:','    '.$segmentName.':'.PHP_EOL.'      label: "'.$ucfirst.' map"'.PHP_EOL.'      settings: true'.PHP_EOL.'      markers: true',
                );

                file_put_contents($file->getPathname(), $newContent);

                $rewritten = true;
            }
        }

        return $rewritten;
    }
}
