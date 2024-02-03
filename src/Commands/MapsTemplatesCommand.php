<?php

namespace Goldfinch\GoogleMaps\Commands;

use Goldfinch\Taz\Services\Templater;
use Goldfinch\Taz\Console\GeneratorCommand;

#[AsCommand(name: 'vendor:google-maps:templates')]
class MapsTemplatesCommand extends GeneratorCommand
{
    protected static $defaultName = 'vendor:google-maps:templates';

    protected $description = 'Publish [goldfinch/google-maps] templates';

    protected $no_arguments = true;

    protected function execute($input, $output): int
    {
        $templater = Templater::create($input, $output, $this, 'goldfinch/google-maps');

        $theme = $templater->defineTheme();

        if (is_string($theme)) {

            $componentPath = BASE_PATH . '/vendor/goldfinch/google-maps/templates/Goldfinch/GoogleMaps/Models/';
            $themePath = 'themes/' . $theme . '/templates/Goldfinch/GoogleMaps/Models/';

            $files = [
                // no need to customize MapBlock
                // [
                //     'from' => $componentPath . 'Blocks/MapBlock.ss',
                //     'to' => $themePath . 'Blocks/MapBlock.ss',
                // ],
                [
                    'from' => $componentPath . 'MapSegment.ss',
                    'to' => $themePath . 'MapSegment.ss',
                ],
            ];

            return $templater->copyFiles($files);
        } else {
            return $theme;
        }
    }
}
