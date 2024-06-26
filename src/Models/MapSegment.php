<?php

namespace Goldfinch\GoogleMaps\Models;

use Goldfinch\GoogleMaps\Blocks\MapBlock;
use Goldfinch\Helpers\Forms\GridField\GridFieldManyManyConfig;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;

class MapSegment extends DataObject
{
    private static $table_name = 'MapSegment';

    private static $singular_name = 'map';

    private static $plural_name = 'maps';

    private static $db = [
        'Title' => 'Varchar',
        'Type' => 'Varchar',
        'Disabled' => 'Boolean',
        'Map' => 'Map',
        'LazyLoading' => 'Boolean',

        'Parameters' => DBJSONText::class,
    ];

    private static $has_many = [
        'Blocks' => MapBlock::class,
    ];

    private static $many_many = [
        'Markers' => MapMarker::class,
    ];

    private static $many_many_extraFields = [
        'Markers' => [
            'SortExtra' => 'Int',
        ],
    ];

    private static $summary_fields = [
        'MapThumbnail' => 'Map',
        'Title' => 'Title',
        'Type' => 'Type',
        'MarkersCounter' => 'Markers',
        'Disabled.NiceAsBoolean' => 'Disabled',
    ];

    private static $required_fields = ['Title', 'Type'];

    public function RenderSegmentMap()
    {
        if ($this->Disabled) {
            return;
        }

        $partialFile = 'Components/Maps/'.$this->Type;

        if (ss_theme_template_file_exists($partialFile)) {
            return $this->Type ? $this->renderWith($partialFile) : null;
        } else {
            return $this->renderWith('Goldfinch/GoogleMaps/Models/MapSegment');
        }

        return null;
    }

    public function MapElement()
    {
        $parameters = json_decode($this->Parameters ?? '{}');

        if (! $parameters) {
            return;
        }

        $map_height = '';
        $map_dynamic_str = '';
        $map_inset_overview = '';

        if (
            property_exists($parameters, 'map_height') &&
            $parameters->map_height
        ) {
            $map_height = 'style="height: '.$parameters->map_height.'px"';
        }

        if (
            property_exists($parameters, 'map_dynamic') &&
            $parameters->map_dynamic
        ) {
            $map_dynamic = $parameters->map_dynamic;

            if (
                property_exists($map_dynamic, 'enabled') &&
                $map_dynamic->enabled
            ) {
                $map_dynamic_str = '<div id="wrapper" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-size: cover;">
                <button class="btn btn-primary">Load Dynamic Map</button></div>';
            }
        }

        if (
            property_exists($parameters, 'map_inset_overview') &&
            $parameters->map_inset_overview
        ) {
            $map_inset_overview =
                '<div
              style="display: '.
                ($map_dynamic_str == '' ? 'block' : 'none').
                '; position: absolute; left: 40px; height: 175px; width: 175px; bottom: 50px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);"
              class="map-overview-'.
                $this->Type.
                '"
              data-map-overview="'.
                $this->ID.
                '"
            ></div>
            ';
        }

        $html =
            '<div class="map-segment" style="position: relative"><div
          class="map-segment-'.
            $this->Type.
            '"
          data-map-segment="'.
            $this->ID.
            '"
          data-lazy-loading=\''.$this->LazyLoading.'\'
          data-segment=\''.
            $this->SegmentData().
            '\'
          data-parameters=\''.
            $this->Parameters.
            '\'
          '.
            $map_height.
            '
        >'.
            $map_dynamic_str.
            '</div>'.
            $map_inset_overview.
            '</div>';

        $return = DBHTMLText::create();
        $return->setValue($html);

        return $return;
    }

    public function MapThumbnail()
    {
        $data = json_decode($this->SegmentData());

        $url = google_maps_preview(
            $data->Latitude,
            $data->Longitude,
            $data->Zoom,
            '260x140',
        );

        $html = DBHTMLText::create();
        $html->setValue('<img src="'.$url.'" alt="Preview image"/>');

        return $html;
    }

    public function MarkersCounter()
    {
        if ($this->getSegmentTypeConfig('markers')) {
            return $this->Markers()->Count();
        }

        return '-';
    }

    public function getSegmentListOfTypes($key = 'label')
    {
        $types = $this->config()->get('segment_types');

        if ($types && count($types)) {
            return array_map(function ($n) use ($key) {
                return $n[$key];
            }, $types);
        }

        return null;
    }

    public function getSegmentTypeConfig($param = null)
    {
        $types = $this->config()->get('segment_types');

        if (
            $types &&
            count($types) &&
            $this->Type &&
            isset($types[$this->Type])
        ) {
            if ($param) {
                if (isset($types[$this->Type][$param])) {
                    return $types[$this->Type][$param];
                } else {
                    return null;
                }
            } else {
                return $types[$this->Type];
            }
        }

        return null;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields()->initFielder($this);

        $fielder = $fields->getFielder();

        $fielder->required(['Title', 'Type']);

        $fielder->remove(['Title', 'Type', 'Disabled', 'Parameters']);

        if ($this->getSegmentTypeConfig('markers')) {
            $markersGrid = $fielder->dataField('Markers');
            $markersGrid
                ->getConfig()
                ->removeComponentsByType(GridFieldDeleteAction::class)
                ->removeComponentsByType(GridFieldAddNewButton::class)
                ->removeComponentsByType(GridFieldPrintButton::class)
                ->removeComponentsByType(GridFieldExportButton::class)
                ->removeComponentsByType(GridFieldImportButton::class)
                ->removeComponentsByType(
                    GridFieldAddExistingAutocompleter::class,
                    // ->removeComponentsByType(GridFieldPaginator::class)
                    // ->addComponent(GridFieldConfigurablePaginator::create())
                );
        } else {
            $fielder->remove('Markers');
        }

        $typesOptions = $this->getSegmentListOfTypes() ?? [];

        $mainFields = [
            $fielder->string('Title'),
            $fielder->checkbox('Disabled')->setDescription(
                'hide this map across the website',
            ),
            $fielder->map('Map'),
            $fielder->checkbox('LazyLoading')->setDescription('Loads map only on first user action (scroll, click or window resizing). Improves site speed performance'),
        ];

        $fielder->toTab('Root.Main', $mainFields);

        if (empty($typesOptions)) {
            $fielder->addError('You need to create and register map segment first, please run <strong>php taz make:map-segment</strong>', 'warning');
        } else {
            $fielder->insertAfter('Disabled', $fielder->dropdown('Type', 'Type', $typesOptions));
        }

        if ($this->ID && $this->Type) {
            $schemaParamsPath =
                BASE_PATH.
                '/vendor/goldfinch/google-maps/_schema/'.
                'map.json';

            if (file_exists($schemaParamsPath)) {
                $schemaParams = file_get_contents($schemaParamsPath);

                $fielder->toTab('Root.Settings', [
                    $fielder->json(
                        'Parameters',
                        null,
                        [],
                        '{}',
                        null,
                        $schemaParams,
                    ),
                ]);
            }
        }

        if ($this->ID) {
            $fielder->toTab('Root.Markers', [
                GridField::create(
                    'Markers',
                    'Markers',
                    $this->Markers(),
                    $cfg = GridFieldManyManyConfig::create(null, 'SortExtra'),
                ),
            ]);

            $dataColumns = $cfg->getComponentByType(
                GridFieldDataColumns::class,
            );

            $dataColumns->setDisplayFields([
                'MapThumbnail' => 'Map',
                'Title' => 'Title',
                'Created' => 'Received at',
            ]);
        }

        if ($this->getSegmentTypeConfig('settings')) {
            $fields->addFieldsToTab('Root.Settings', []);
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $changed = $this->getChangedFields();

        if (isset($changed['Type'])) {
            if ($changed['Type']['before'] != $changed['Type']['after']) {
                $this->Parameters = '';
            }
        }

        if (! $this->Parameters) {
            $this->Parameters = '{"map_dynamic":{"enabled":false},"map_theme":{"theme":""},"map_inset_overview":false,"map_height":"400","mapId":"","mapTypeId":"roadmap","backgroundColor":"#ffffff","disableDoubleClickZoom":false,"clickableIcons":true,"zoomControl":true,"mapTypeControl":true,"scaleControl":false,"streetViewControl":true,"rotateControl":false,"fullscreenControl":true,"scrollwheel":"","isFractionalZoomEnabled":false,"keyboardShortcuts":true,"draggable":true,"noClear":false,"maxZoom":"","minZoom":"","controlSize":"","draggableCursor":"","draggingCursor":"","gestureHandling":"auto","heading":""}';
        }
    }

    public function MarkersData()
    {
        $data = [];

        foreach ($this->Markers() as $marker) {
            $iw = $marker->infoWindow();

            $data[] = [
                'Title' => $marker->Title,
                'Icon' => $marker->Icon()->exists()
                    ? $marker->Icon()->getURL()
                    : null,
                'InfoWindow' => $iw ? $iw->RAW() : null,
                'Latitude' => (float) $marker->Map->Latitude,
                'Longitude' => (float) $marker->Map->Longitude,
                'Parameters' => $marker->Parameters,
            ];
        }

        return $data;
    }

    public function SegmentData()
    {
        $parameters = json_decode($this->Parameters ?? '');

        $theme = '';

        if ($parameters) {
            if (
                $parameters->map_theme->theme &&
                $parameters->map_theme->theme != 'custom'
            ) {
                $theme =
                    BASE_PATH.
                    '/vendor/goldfinch/google-maps/_schema/map-styles/'.
                    $parameters->map_theme->theme.
                    '.json';

                if (file_exists($theme)) {
                    $theme = file_get_contents($theme);
                }
            }
        }

        if (Environment::hasEnv('APP_GOOGLE_MAPS_KEY')) {
            $key = Environment::getEnv('APP_GOOGLE_MAPS_KEY');
        } else {
            $cfg = SiteConfig::current_site_config();
            if ($cfg->GoogleCloud && $cfg->GoogleCloudAPIKey) {
                $key = $cfg->GoogleCloudAPIKey;
            } else {
                $key = '';
            }
        }

        $data = [
            'Key' => $key,
            'Latitude' => (float) $this->Map->Latitude,
            'Longitude' => (float) $this->Map->Longitude,
            'Zoom' => (float) $this->Map->Zoom,
            'Markers' => $this->MarkersData(),
            'Theme' => $theme,
        ];

        return json_encode($data);
    }
}
