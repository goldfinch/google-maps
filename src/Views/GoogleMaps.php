<?php

namespace Goldfinch\GoogleMaps\Views;

use SilverStripe\View\ViewableData;
use Goldfinch\GoogleMaps\Models\MapSegment;

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
