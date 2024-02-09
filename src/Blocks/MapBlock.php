<?php

namespace Goldfinch\GoogleMaps\Blocks;

use Goldfinch\GoogleMaps\Models\MapSegment;
use DNADesign\Elemental\Models\BaseElement;
use SilverShop\HasOneField\HasOneButtonField;

class MapBlock extends BaseElement
{
    private static $table_name = 'MapBlock';
    private static $singular_name = 'Map';
    private static $plural_name = 'Map';

    private static $inline_editable = false;
    private static $description = '';
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

        return $fields;
    }
}
