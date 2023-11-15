<?php

namespace Goldfinch\Component\Maps\Models;

use BetterBrief\GoogleMapField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Security\Permission;
use Goldfinch\Component\Maps\Blocks\MapBlock;
use Goldfinch\Component\Maps\Models\MapPoint;
use Goldfinch\JSONEditor\Forms\JSONEditorField;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
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
        'Points' => MapPoint::class,
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'Type' => 'Type',
        'PointsCounter' => 'Points',
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

    public function PointsCounter()
    {
        if ($this->getSegmentTypeConfig('points'))
        {
            return $this->Points()->Count();
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

        if ($this->getSegmentTypeConfig('points'))
        {
            $pointsGrid = $fields->dataFieldByName('Points');
            $pointsGrid->getConfig()
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
            $fields->removeByName('Points');
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
            $schemaParamsPath = BASE_PATH . '/app/_schema/' . 'map-' . $this->Type . '.json';

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
