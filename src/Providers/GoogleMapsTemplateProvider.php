<?php

namespace Goldfinch\GoogleMaps\Providers;

use Goldfinch\GoogleMaps\Views\GoogleMaps;
use SilverStripe\View\TemplateGlobalProvider;

class GoogleMapsTemplateProvider implements TemplateGlobalProvider
{
    /**
     * @return array|void
     */
    public static function get_template_global_variables(): array
    {
        return ['GoogleMaps'];
    }

    public static function GoogleMaps(): GoogleMaps
    {
        return GoogleMaps::create();
    }
}
