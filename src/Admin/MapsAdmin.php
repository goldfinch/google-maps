<?php

namespace Goldfinch\Component\Maps\Admin;

use SilverStripe\Admin\ModelAdmin;
use JonoM\SomeConfig\SomeConfigAdmin;
use Goldfinch\Component\Maps\Blocks\MapBlock;
use Goldfinch\Component\Maps\Models\MapMarker;
use Goldfinch\Component\Maps\Configs\MapConfig;
use Goldfinch\Component\Maps\Models\MapSegment;
use SilverStripe\Forms\GridField\GridFieldConfig;

class MapsAdmin extends ModelAdmin
{
    use SomeConfigAdmin;

    private static $url_segment = 'maps';
    private static $menu_title = 'Maps';
    private static $menu_icon_class = 'font-icon-p-map';
    // private static $menu_priority = -0.5;

    private static $managed_models = [
        MapSegment::class => [
            'title'=> 'Maps',
        ],
        MapMarker::class => [
            'title'=> 'Markers',
        ],
        MapBlock::class => [
            'title'=> 'Blocks',
        ],
        MapConfig::class => [
            'title'=> 'Settings',
        ],
    ];

    // public $showImportForm = true;
    // public $showSearchForm = true;
    // private static $page_length = 30;

    public function getList()
    {
        $list = parent::getList();

        // ..

        return $list;
    }

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();

        // ..

        return $config;
    }

    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        // ..

        return $context;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        // ..

        return $form;
    }

    // public function getExportFields()
    // {
    //     return [
    //         // 'Name' => 'Name',
    //         // 'Category.Title' => 'Category'
    //     ];
    // }
}
