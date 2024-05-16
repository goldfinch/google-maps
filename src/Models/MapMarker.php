<?php

namespace Goldfinch\GoogleMaps\Models;

use Goldfinch\GoogleFields\Forms\MapField;
use Goldfinch\JSONEditor\Forms\JSONEditorField;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

class MapMarker extends DataObject
{
    private static $table_name = 'MapMarker';

    private static $singular_name = 'marker';

    private static $plural_name = 'markers';

    private static $db = [
        'Title' => 'Varchar',
        'Map' => 'Map',
        'InfoWindowTemplate' => 'Varchar',

        'Parameters' => DBJSONText::class,
    ];

    private static $summary_fields = [
        'MapThumbnail' => 'Map',
        'Title' => 'Title',
        'Created' => 'Received at',
        'Segments.First.Type' => 'Type',
    ];

    private static $belongs_many_many = [
        'Segments' => MapSegment::class,
    ];

    private static $has_one = [
        'Icon' => File::class,
    ];

    private static $owns = ['Icon'];

    private static $required_fields = ['Title'];

    public function infoWindow()
    {
        $path = 'Components/Maps/InfoWindows/'.$this->InfoWindowTemplate;

        if (! ss_theme_template_file_exists($path)) {
            return null;
        }

        return $this->renderWith([$path, 'Goldfinch/GoogleMaps/Models/MapSegment']);
    }

    public function MapThumbnail()
    {
        $url = google_maps_preview(
            $this->Map->Latitude,
            $this->Map->Longitude,
            $this->Map->Zoom,
            '260x140',
        );

        $html = DBHTMLText::create();
        $html->setValue('<img src="'.$url.'" alt="Preview image"/>');

        return $html;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields()->initFielder($this);

        $fielder = $fields->getFielder();

        $fields = $fields->makeReadonly();

        $fields->removeByName(['SegmentID', 'Parameters']);

        $infoWindowTemplates = [
            '' => '-',
        ];

        // scan for template files
        $dir =
            THEMES_PATH.
            '/'.
            ss_theme().
            '/templates/Components/Maps/InfoWindows/';

        if (is_dir($dir)) {
            $files = scandir($dir);

            if (count($files)) {
                foreach ($files as $file) {
                    if (substr($file, -3) == '.ss') {
                        $name = substr($file, 0, -3);

                        $infoWindowTemplates[$name] = $name;
                    }
                }
            }
        }

        $mainFields = [
            TextField::create('Title', 'Title'),
            UploadField::create('Icon', 'Icon')->setFolderName('maps'),
            DropdownField::create(
                'InfoWindowTemplate',
                'Info Window Template',
                $infoWindowTemplates,
            )->setDescription(
                'The <strong>Info Window</strong> option in Settings needs  to be enabled for this to work.<br>Use Taz command to create a new template: <i>php taz make:map-infowindow:template</i>',
            ),
            MapField::create('Map'),
        ];

        $fields->addFieldsToTab('Root.Main', $mainFields);

        if ($this->ID) {
            $schemaParamsPath =
                BASE_PATH.'/vendor/goldfinch/google-maps/_schema/marker.json';

            if (file_exists($schemaParamsPath)) {
                $schemaParams = file_get_contents($schemaParamsPath);

                $fields->addFieldsToTab('Root.Settings', [
                    JSONEditorField::create(
                        'Parameters',
                        'Parameters',
                        $this,
                        [],
                        '{}',
                        null,
                        $schemaParams,
                    ),
                ]);
            }
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (! $this->Parameters) {
            $this->Parameters = '{"marker_type":{"markerType":"Marker","markerScaleWidth":"","markerScaleHeight":"","markerAnchorX":"","markerAnchorY":"","markerAnimation":"","markerFont":false,"markerFontFamily":"","markerFontCode":"","markerFontColor":"","markerFontSize":""},"info_window":{"infoWindow":false}}';
        }
    }
}
