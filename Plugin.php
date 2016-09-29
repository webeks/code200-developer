<?php namespace Code200\Developer;

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

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }


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
                }
            ]
        ];
    }



}
