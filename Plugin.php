<?php namespace Code200\Developer;

use Illuminate\Support\Facades\Lang;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        ini_set('xdebug.var_display_max_depth', 3);
        ini_set('xdebug.var_display_max_children', 64);
        ini_set('xdebug.var_display_max_data', 64);
//        ini_set('xdebug.max_nesting_level', 64);


    }

    public function register()
    {
        parent::register();

        $this->registerConsoleCommand('developer.dbbackup', 'Code200\Developer\Console\Dbbackup');
        $this->registerConsoleCommand('developer.gradimtransfercategories', 'Code200\Developer\Console\GradimTransferCategories');
        $this->registerConsoleCommand('developer.gradimtransferposts', 'Code200\Developer\Console\GradimTransferPosts');

    }

//    public function registerComponents()
//    {
//    }
//
//    public function registerSettings()
//    {
//    }


    public function registerMarkupTags()
    {
        return [
//            'filters' => [
//                // A global function, i.e str_plural()
//                'plural' => 'str_plural',
//
//                // A local method, i.e $this->makeTextAllCaps()
//                'uppercase' => [$this, 'makeTextAllCaps']
//            ],
            'functions' => [
                // A static method call, i.e Form::open()
//                'printr' => ['October\Rain\Html\Form', 'open'],
                'php_var_dump' => function($object) {
                    var_dump($object);
                },
                'php_print_r' => function($object, $format = false) {
                    if($format) echo '<pre>';

                    print_r($object);

                    if($format) echo '</pre>';
                }
            ]
        ];
    }



}
