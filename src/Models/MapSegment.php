<?php

namespace Goldfinch\Component\Maps\Models;

use BetterBrief\GoogleMapField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\GridField\GridField;
use Goldfinch\Component\Maps\Blocks\MapBlock;
use Goldfinch\Component\Maps\Models\MapMarker;
use Goldfinch\JSONEditor\Forms\JSONEditorField;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use Goldfinch\Helpers\Forms\GridField\GridFieldManyManyConfig;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;

class MapSegment extends DataObject
{
    private static $table_name = 'MapSegment';
    private static $singular_name = 'map';
    private static $plural_name = 'maps';

    private static $db = [
        'Title' => 'Varchar',
        'Type' => 'Varchar',
        'Disabled' => 'Boolean',
        'Latitude' => 'Varchar',
        'Longitude' => 'Varchar',
        'Zoom' => 'Int',

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
            'SortOrder' => 'Int',
        ]
    ];

    private static $summary_fields = [
        'MapThumbnail' => 'Map',
        'Title' => 'Title',
        'Type' => 'Type',
        'MarkersCounter' => 'Markers',
        'Disabled.NiceAsBoolean' => 'Disabled',
    ];

    // private static $belongs_to = [];
    // private static $belongs_many_many = [];

    // private static $default_sort = null;
    // private static $indexes = null;
    // private static $casting = [];
    // private static $defaults = [];

    // private static $field_labels = [];
    // private static $searchable_fields = [];

    // private static $cascade_deletes = [];
    // private static $cascade_duplicates = [];

    // * goldfinch/helpers
    // private static $field_descriptions = [];
    // private static $required_fields = [];

    public function RenderSegmentMap()
    {
        if ($this->Disabled)
        {
            return;
        }

        $partialFile = 'Components/Maps/' . $this->Type;

        if (ss_theme_template_file_exists($partialFile))
        {
            return $this->Type ? $this->renderWith($partialFile) : null;
        }
        else
        {
            return $this->renderWith('Goldfinch/Component/Maps/MapSegment');
        }

        return null;
    }

    public function MapElement()
    {
        $parameters = json_decode($this->Parameters);

        if (!$parameters)
        {
            return;
        }

        $map_height = '';
        $map_dynamic_str = '';
        $map_inset_overview = '';

        if (property_exists($parameters, 'map_height') && $parameters->map_height)
        {
            $map_height = 'style="height: '.$parameters->map_height.'px"';
        }

        if (property_exists($parameters, 'map_dynamic') && $parameters->map_dynamic)
        {
            $map_dynamic = $parameters->map_dynamic;

            if (property_exists($map_dynamic, 'enabled') && $map_dynamic->enabled)
            {
                $map_dynamic_str = '<div id="wrapper" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-size: cover;">
                <button class="btn btn-primary">Load Dynamic Map</button></div>';
            }
        }

        if (property_exists($parameters, 'map_inset_overview') && $parameters->map_inset_overview)
        {
            $map_inset_overview = '<div
              style="display: '.($map_dynamic_str == '' ? 'block' : 'none').'; position: absolute; left: 40px; height: 175px; width: 175px; bottom: 50px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);"
              class="map-overview-'.$this->Type.'"
              data-map-overview="'.$this->ID.'"
            ></div>
            ';
        }

        $html = '<div class="map-segment" style="position: relative"><div
          class="map-segment-'.$this->Type.'"
          data-map-segment="'.$this->ID.'"
          data-segment=\''.$this->SegmentData().'\'
          data-parameters=\''.$this->Parameters.'\'
          '.$map_height.'
        >' . $map_dynamic_str . '</div>' .  $map_inset_overview
        . '</div>';

        $return = DBHTMLText::create();
        $return->setValue($html);

        return $return;
    }

    public function MapThumbnail()
    {
        $data = json_decode($this->SegmentData());

        $url = google_maps_preview($data->Latitude, $data->Longitude, $data->Zoom, '260x140');

        $html = DBHTMLText::create();
        $html->setValue('<img src="' . $url . '" alt="Preview image"/>');

        return $html;
    }

    public function MarkersCounter()
    {
        if ($this->getSegmentTypeConfig('markers'))
        {
            return $this->Markers()->Count();
        }

        return '-';
    }

    public function getSegmentListOfTypes($key = 'label')
    {
        $types = $this->config()->get('segment_types');

        if ($types && count($types))
        {
            return array_map(function($n) use ($key) {
                return $n[$key];
            }, $types);
        }

        return null;
    }

    public function getSegmentTypeConfig($param = null)
    {
        $types = $this->config()->get('segment_types');

        if ($types && count($types) && $this->Type && isset($types[$this->Type]))
        {
            if ($param)
            {
                if (isset($types[$this->Type][$param]))
                {
                    return $types[$this->Type][$param];
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return $types[$this->Type];
            }
        }

        return null;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Title',
            'Type',
            'Disabled',
            'Parameters',
        ]);

        if ($this->getSegmentTypeConfig('markers'))
        {
            $markersGrid = $fields->dataFieldByName('Markers');
            $markersGrid->getConfig()
                ->removeComponentsByType(GridFieldDeleteAction::class)
                ->removeComponentsByType(GridFieldAddNewButton::class)
                ->removeComponentsByType(GridFieldPrintButton::class)
                ->removeComponentsByType(GridFieldExportButton::class)
                ->removeComponentsByType(GridFieldImportButton::class)
                ->removeComponentsByType(GridFieldAddExistingAutocompleter::class)
                // ->removeComponentsByType(GridFieldPaginator::class)
                // ->addComponent(GridFieldConfigurablePaginator::create())
            ;
        }
        else
        {
            $fields->removeByName('Markers');
        }

        $typesOptions = $this->getSegmentListOfTypes();

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create(
                    'Title',
                    'Title'
                ),
                CheckboxField::create('Disabled', 'Disabled')->setDescription('hide this map across the website'),
                DropdownField::create(
                    'Type',
                    'Type',
                    $typesOptions,
                ),
                GoogleMapField::create($this, 'Location'),
            ]
        );

        if ($this->ID && $this->Type)
        {
            $schemaParamsPath = BASE_PATH . '/vendor/goldfinch/component-maps/_schema/' . 'map.json';

            if (file_exists($schemaParamsPath))
            {
                $schemaParams = file_get_contents($schemaParamsPath);

                $fields->addFieldsToTab(
                    'Root.Settings',
                    [
                        JSONEditorField::create('Parameters', 'Parameters', $this, [], '{}', null, $schemaParams),
                    ]
                );
            }
        }

        if ($this->ID)
        {
            $fields->addFieldsToTab(
              'Root.Markers',
              [
                  GridField::create(
                    'Markers',
                    'Markers',
                    $this->Markers(),
                    $cfg = GridFieldManyManyConfig::create(),
                  )
              ]
            );

            $dataColumns = $cfg->getComponentByType(GridFieldDataColumns::class);

            $dataColumns->setDisplayFields([
                'MapThumbnail' => 'Map',
                'Title' => 'Title',
                'Created' => 'Received at'
            ]);
        }

        if ($this->getSegmentTypeConfig('settings'))
        {
            $fields->addFieldsToTab(
                'Root.Settings',
                []
            );
        }

        return $fields;
    }

    // public function validate()
    // {
    //     $result = parent::validate();

    //     // $result->addError('Error message');

    //     return $result;
    // }

    public function onBeforeWrite()
    {
        $changed = $this->getChangedFields();

        if (isset($changed['Type']))
        {
            if ($changed['Type']['before'] != $changed['Type']['after'])
            {
                $this->Parameters = '';
            }
        }

        parent::onBeforeWrite();
    }

    public function MarkersData()
    {
        $data = [];

        foreach ($this->Markers() as $marker)
        {
            $iw = $marker->infoWindow();

            $data[] = [
                'Title' => $marker->Title,
                'Icon' => $marker->Icon()->exists() ? $marker->Icon()->getURL() : null,
                'InfoWindow' => $iw ? $iw->RAW() : null,
                'Latitude' => (float) $marker->Latitude,
                'Longitude' => (float) $marker->Longitude,
                'Parameters' => $marker->Parameters,
            ];
        }

        return $data;
    }

    public function SegmentData()
    {
        $parameters = json_decode($this->Parameters);

        $theme = '';

        if ($parameters)
        {
            if ($parameters->map_theme->theme && $parameters->map_theme->theme != 'custom')
            {
                $theme = BASE_PATH . '/vendor/goldfinch/component-maps/_schema/map-styles/' . $parameters->map_theme->theme . '.json';

                if (file_exists($theme))
                {
                    $theme = file_get_contents($theme);
                }
            }
        }

        $data = [
            'Key' => Environment::getEnv('APP_GOOGLE_MAPS_KEY'),
            'Latitude' => (float) $this->Latitude,
            'Longitude' => (float) $this->Longitude,
            'Zoom' => (float) $this->Zoom,
            'Markers' => $this->MarkersData(),
            'Theme' => $theme,
        ];

        return json_encode($data);
    }

    // public function onBeforeDelete()
    // {
    //     // ..

    //     parent::onBeforeDelete();
    // }

    // public function canView($member = null)
    // {
    //     return Permission::check('CMS_ACCESS_Company\Website\MyAdmin', 'any', $member);
    // }

    // public function canEdit($member = null)
    // {
    //     return Permission::check('CMS_ACCESS_Company\Website\MyAdmin', 'any', $member);
    // }

    // public function canDelete($member = null)
    // {
    //     return Permission::check('CMS_ACCESS_Company\Website\MyAdmin', 'any', $member);
    // }

    // public function canCreate($member = null, $context = [])
    // {
    //     return Permission::check('CMS_ACCESS_Company\Website\MyAdmin', 'any', $member);
    // }
}
