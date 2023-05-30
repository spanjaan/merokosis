<?php

namespace Spanjaan\Workspace;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * workspace Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name' => 'workspace',
            'description' => 'This is a dynamic workspace plugin for government offices',
            'author' => 'spanjaan',
            'icon' => 'icon-pencil'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {

    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate

        return [
            'Spanjaan\Workspace\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'spanjaan.workspace.some_permission' => [
                'tab' => 'spanjaan.workspace::lang.plugin.name',
                'label' => 'spanjaan.workspace::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'workspace' => [
                'label' => 'Work Space',
                'url' => Backend::url('spanjaan/workspace/records'),
                'icon' => 'icon-pencil',
                'iconSvg'     => 'plugins/spanjaan/workspace/assets/images/editor-icon.svg',
                'permissions' => ['spanjaan.workspace.*'],
                'order' => 400,
                'sideMenu' => [
                    'new_record' => [
                        'label'       => 'New Record',
                        'icon'        => 'icon-plus',
                        'url'         => Backend::url('spanjaan/workspace/records/create'),
                        'permissions' => ['spanjaan.document.*']
                    ],
                    'records' => [
                        'label'       => 'Record Lists',
                        'icon'        => 'icon-copy',
                        'url'         => Backend::url('spanjaan/workspace/records'),
                        'permissions' => ['spanjaan.document.*']
                    ]
                ]
            ]
        ];
    }

        /**
    *	Custom list types
    */
    public function registerListColumnTypes()
    {
        return [
            'strong' => function ($value) { return '<strong>'. $value . '</strong>'; },
            'c_text_preview' => function ($value) {
                $content = mb_substr(strip_tags($value), 0, 30);
                if(mb_strlen($content) > 30) {
                    return ($content . '...');
                } else {
                    return $content;
                }
            },
            'c_attached_images_count' => function ($value) { return (count($value) ? count($value) : null); },
            'c_image_preview' => function ($value) {
                if ($value !== null) {
                    return "<img src='" . $value->getThumb(70, 40, ['mode' => 'crop', 'quality' => 80]) . "' style='border-radius: 5px;'>";
                } else {
                    return "<p>No image available</p>";
                }
            },



            'c_document_link' => function ($value) {
                if(!empty($value)) {
                    $output = [];
                    foreach($value as $file) {
                        $output[] = "<div><a class='btn btn-primary' href='".$file->getPath()."'>Open file</a></div>";
                    }
                    return implode('', $output);
                }
            },
        ];
    }
}
