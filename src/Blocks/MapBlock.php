<?php

namespace Goldfinch\GoogleMaps\Blocks;

use DNADesign\Elemental\Models\BaseElement;
use Goldfinch\GoogleMaps\Models\MapSegment;
use Goldfinch\Helpers\Traits\BaseElementTrait;
use SilverShop\HasOneField\HasOneButtonField;

class MapBlock extends BaseElement
{
    use BaseElementTrait;

    private static $table_name = 'MapBlock';

    private static $singular_name = 'Map';

    private static $plural_name = 'Map';

    private static $inline_editable = false;

    private static $description = 'Google Maps block handler';

    private static $icon = 'font-icon-p-map';

    private static $has_one = [
        'Segment' => MapSegment::class,
    ];

    private static $owns = ['Segment'];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['SegmentID']);

        $fields->addFieldsToTab('Root.Main', [
            HasOneButtonField::create($this, 'Segment'),
        ]);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }
}
