<?php

namespace Goldfinch\Component\Maps\Models;

use SilverStripe\Assets\File;
use BetterBrief\GoogleMapField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\AssetAdmin\Forms\UploadField;
use Goldfinch\Component\Maps\Models\MapSegment;
use Goldfinch\JSONEditor\Forms\JSONEditorField;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;

class MapMarker extends DataObject
{
    private static $table_name = 'MapMarker';
    private static $singular_name = 'marker';
    private static $plural_name = 'markers';

    private static $db = [
        'Title' => 'Varchar',
        'Latitude' => 'Varchar',
        'Longitude' => 'Varchar',
        'Zoom' => 'Int',
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

    private static $owns = [
        'Icon',
    ];

    // private static $belongs_to = [];
    // private static $has_many = [];
    // private static $many_many = [];
    // private static $many_many_extraFields = [];
    // private static $belongs_many_many = [];
    // private static $default_sort = null;
    // private static $indexes = null;
    // private static $owns = [];
    // private static $casting = [];
    // private static $defaults = [];

    // private static $summary_fields = [];
    // private static $field_labels = [];
    // private static $searchable_fields = [];

    // private static $cascade_deletes = [];
    // private static $cascade_duplicates = [];

    // * goldfinch/helpers
    // private static $field_descriptions = [];
    // private static $required_fields = [];

    public function infoWindow()
    {
        $path = 'Components/Maps/InfoWindows/' . $this->InfoWindowTemplate;

        if (!ss_theme_template_file_exists($path))
        {
            return null;
        }

        // ! important check: this mthod will be called within the admin for summary_fields data, therefore `renderWith` will through an error. To avoid this, the check below determins the raugh difference between the admin and the frontend call.
        if (array_key_exists('', $this->sourceQueryParams) && $this->sourceQueryParams[''] == null)
        {
            return null;
        }

        return $this->renderWith($path);
    }

    public function MapThumbnail()
    {
        $url = google_maps_preview($this->Latitude, $this->Longitude, $this->Zoom, '260x140');

        $html = DBHTMLText::create();
        $html->setValue('<img src="' . $url . '" alt="Preview image"/>');

        return $html;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields = $fields->makeReadonly();

        $fields->removeByName([
            'SegmentID',
            'Parameters',
        ]);

        $infoWindowTemplates = [
            '' => '-',
        ];

        // scan for template files
        $dir = THEMES_PATH . '/' . ss_theme() . '/templates/Components/Maps/InfoWindows/';

        if (is_dir($dir))
        {
            $files = scandir($dir);

            if (count($files))
            {
                foreach($files as $file)
                {
                    if (substr($file, -3) == '.ss')
                    {
                        $name = substr($file, 0, -3);

                        $infoWindowTemplates[$name] = $name;
                    }
                }
            }
        }

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('Title', 'Title'),
                GoogleMapField::create($this, 'Location'),
                UploadField::create('Icon', 'Icon'),
                DropdownField::create('InfoWindowTemplate', 'Info Window Template', $infoWindowTemplates)->setDescription('Info Window option in Settings needs  to be enabled for this to work.<br>Place your template in `/themes/{theme}/templates/Components/Maps/InfoWindows/you_template_name.ss`'),
            ],
        );

        if ($this->ID)
        {
            $schemaParamsPath = BASE_PATH . '/vendor/goldfinch/component-maps/_schema/marker.json';

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

        return $fields;
    }

    // public function validate()
    // {
    //     $result = parent::validate();

    //     // $result->addError('Error message');

    //     return $result;
    // }

    // public function onBeforeWrite()
    // {
    //     // ..

    //     parent::onBeforeWrite();
    // }

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
