<?php

namespace Goldfinch\GoogleMaps\Admin;

use Goldfinch\GoogleMaps\Blocks\MapBlock;
use Goldfinch\GoogleMaps\Configs\MapConfig;
use Goldfinch\GoogleMaps\Models\MapMarker;
use Goldfinch\GoogleMaps\Models\MapSegment;
use JonoM\SomeConfig\SomeConfigAdmin;
use SilverStripe\Admin\ModelAdmin;

class MapsAdmin extends ModelAdmin
{
    use SomeConfigAdmin;

    private static $url_segment = 'maps';

    private static $menu_title = 'Maps';

    private static $menu_icon_class = 'font-icon-p-map';
    // private static $menu_priority = -0.5;

    private static $managed_models = [
        MapSegment::class => [
            'title' => 'Maps',
        ],
        MapMarker::class => [
            'title' => 'Markers',
        ],
        MapBlock::class => [
            'title' => 'Blocks',
        ],
        MapConfig::class => [
            'title' => 'Settings',
        ],
    ];

    public function getManagedModels()
    {
        $models = parent::getManagedModels();

        $cfg = MapConfig::current_config();

        if (! class_exists('DNADesign\Elemental\Models\BaseElement')) {
            unset($models[MapBlock::class]);
        }

        if (empty($cfg->config('db')->db)) {
            unset($models[MapConfig::class]);
        }

        return $models;
    }
}
