<?php

namespace Goldfinch\GoogleMaps\Views;

use Goldfinch\GoogleMaps\Models\MapSegment;
use SilverStripe\View\ViewableData;

class GoogleMaps extends ViewableData
{
    public function bySegment($type)
    {
        $segment = MapSegment::get()->filter('Type', $type)->first();

        if ($segment) {
            return $segment;
        }
    }

    public function byID($id)
    {
        $segment = MapSegment::get()->byID($id);

        if ($segment) {
            return $segment;
        }
    }
}
