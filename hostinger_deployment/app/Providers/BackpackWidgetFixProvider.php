<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BackpackWidgetFixProvider extends ServiceProvider
{
    public function boot()
    {
        // Fix Backpack widget scalar value errors by ensuring widget configurations are arrays
        if (class_exists('\Backpack\CRUD\app\Library\Widget')) {
            $this->fixBackpackWidgets();
        }
    }

    protected function fixBackpackWidgets()
    {
        // Override the Widget class to handle scalar values properly
        $originalAdd = \Backpack\CRUD\app\Library\Widget::class . '::add';
        
        // Create a macro to safely add widgets
        if (method_exists('\Backpack\CRUD\app\Library\Widget', 'macro')) {
            \Backpack\CRUD\app\Library\Widget::macro('addSafe', function ($content) {
                // Ensure content is always an array
                if (!is_array($content)) {
                    $content = ['content' => $content];
                }
                
                return static::add($content);
            });
        }
    }

    public function register()
    {
        //
    }
}