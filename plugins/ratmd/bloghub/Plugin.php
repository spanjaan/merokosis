<?php

declare(strict_types=1);

namespace RatMD\BlogHub;

use Backend;
use Event;
use Exception;
use Backend\Controllers\Users as BackendUsers;
use Backend\Facades\BackendAuth;
use Backend\Models\User as BackendUser;
use Backend\Widgets\Lists;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Winter\Blog\Controllers\Posts;
use Winter\Blog\Models\Post;
use RatMD\BlogHub\Behaviors\BlogHubBackendUserModel;
use RatMD\BlogHub\Behaviors\BlogHubPostModel;
use RatMD\BlogHub\Models\Comment;
use RatMD\BlogHub\Models\Visitor;
use Symfony\Component\Yaml\Yaml;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * Required Extensions
     *
     * @var array
     */
    public $require = [
        'Winter.Blog'
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ratmd.bloghub::lang.plugin.name',
            'description' => 'ratmd.bloghub::lang.plugin.description',
            'author'      => 'RatMD <info@rat.md>',
            'icon'        => 'icon-tags',
            'homepage'    => 'https://github.com/RatMD/bloghub-plugin'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

        // Extend available sorting options
        Post::$allowedSortingOptions['ratmd_bloghub_views asc'] = 'ratmd.bloghub::lang.sorting.bloghub_views_asc';
        Post::$allowedSortingOptions['ratmd_bloghub_views desc'] = 'ratmd.bloghub::lang.sorting.bloghub_views_desc';
        Post::$allowedSortingOptions['ratmd_bloghub_unique_views asc'] = 'ratmd.bloghub::lang.sorting.bloghub_unique_views_asc';
        Post::$allowedSortingOptions['ratmd_bloghub_unique_views desc'] = 'ratmd.bloghub::lang.sorting.bloghub_unique_views_desc';
        Post::$allowedSortingOptions['ratmd_bloghub_comments_count asc'] = 'ratmd.bloghub::lang.sorting.bloghub_comments_count_asc';
        Post::$allowedSortingOptions['ratmd_bloghub_comments_count desc'] = 'ratmd.bloghub::lang.sorting.bloghub_comments_count_desc';
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        // Add side menuts to Winter.Blog
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('Winter.Blog', 'blog', [
                'ratmd_bloghub_tags' => [
                    'label'         => 'ratmd.bloghub::lang.model.tags.label',
                    'icon'          => 'icon-tags',
                    'code'          => 'ratmd-bloghub-tags',
                    'owner'         => 'RatMD.BlogHub',
                    'url'           => Backend::url('ratmd/bloghub/tags'),
                    'permissions'   => [
                        'ratmd.bloghub.tags'
                    ]
                ],

                'ratmd_bloghub_comments' => [
                    'label'         => 'ratmd.bloghub::lang.model.comments.label',
                    'icon'          => 'icon-comments-o',
                    'code'          => 'ratmd-bloghub-comments',
                    'owner'         => 'RatMD.BlogHub',
                    'url'           => Backend::url('ratmd/bloghub/comments'),
                    'counter'       => Comment::where('status', 'pending')->count(),
                    'permissions'   => [
                        'ratmd.bloghub.comments'
                    ]
                ]
            ]);
        });

        // Collect (Unique) Views
        Event::listen('cms.page.end', function (Controller $ctrl) {
            $pageObject = $ctrl->getPageObject();
            if (property_exists($pageObject, 'vars')) {
                $post = $pageObject->vars['post'] ?? null;
            } elseif (property_exists($pageObject, 'controller')) {
                $post = $pageObject->controller->vars['post'] ?? null;
            } else {
                $post = null;
            }
            if (empty($post)) {
                return;
            }

            $guest = BackendAuth::getUser() === null;
            $visitor = Visitor::currentUser();
            if (!$visitor->hasSeen($post)) {
                if ($guest) {
                    $post->ratmd_bloghub_unique_views = is_numeric($post->ratmd_bloghub_unique_views) ? $post->ratmd_bloghub_unique_views+1 : 1;
                }
                $visitor->markAsSeen($post);
            }

            if ($guest) {
                $post->ratmd_bloghub_views = is_numeric($post->ratmd_bloghub_views) ? $post->ratmd_bloghub_views+1 : 1;

                if (!empty($post->url)) {
                    $url = $post->url;
                    unset($post->url);
                }

                $post->save();

                if (isset($url)) {
                    $post->url = $url;
                }
            }
        });

        // Implement custom Models
        Post::extend(function ($model) {
            $model->implement[] = BlogHubPostModel::class;
        });

        BackendUser::extend(function ($model) {
            $model->implement[] = BlogHubBackendUserModel::class;
        });



        // Extend Form Fields on Posts Controller
        Posts::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof Post) {
                return;
            }

            // Add Comments Field
            $form->addSecondaryTabFields([
                'ratmd_bloghub_comment_visible' => [
                    'tab'           => 'ratmd.bloghub::lang.model.comments.label',
                    'type'          => 'switch',
                    'label'         => 'ratmd.bloghub::lang.model.comments.post_visibility.label',
                    'comment'       => 'ratmd.bloghub::lang.model.comments.post_visibility.comment',
                    'span'          => 'left',
                    'permissions'   => ['ratmd.bloghub.comments.access_comments_settings']
                ],
                'ratmd_bloghub_comment_mode' => [
                    'tab'           => 'ratmd.bloghub::lang.model.comments.label',
                    'type'          => 'dropdown',
                    'label'         => 'ratmd.bloghub::lang.model.comments.post_mode.label',
                    'comment'       => 'ratmd.bloghub::lang.model.comments.post_mode.comment',
                    'showSearch'    => false,
                    'span'          => 'left',
                    'options'       => [
                        'open' => 'ratmd.bloghub::lang.model.comments.post_mode.open',
                        'restricted' => 'ratmd.bloghub::lang.model.comments.post_mode.restricted',
                        'private' => 'ratmd.bloghub::lang.model.comments.post_mode.private',
                        'closed' => 'ratmd.bloghub::lang.model.comments.post_mode.closed',
                    ],
                    'permissions'   => ['ratmd.bloghub.comments.access_comments_settings']
                ],
            ]);
            // Add Tags Field
            $form->addSecondaryTabFields([
                'ratmd_bloghub_tags' => [
                    'label'         => 'ratmd.bloghub::lang.model.tags.label',
                    'mode'          => 'relation',
                    'tab'           => 'winter.blog::lang.post.tab_categories',
                    'type'          => 'taglist',
                    'nameFrom'      => 'slug',
                    'permissions'   => ['ratmd.bloghub.tags']
                ]
            ]);

        });

        // Extend List Columns on Posts Controller
        Posts::extendListColumns(function (Lists $list, $model) {
            if (!$model instanceof Post) {
                return;
            }

            $list->addColumns([
                'ratmd_bloghub_views' => [
                    'label' => 'ratmd.bloghub::lang.model.visitors.views',
                    'type' => 'number',
                    'select' => 'concat(winter_blog_posts.ratmd_bloghub_views, " / ", winter_blog_posts.ratmd_bloghub_unique_views)',
                    'align' => 'left'
                ]
            ]);
        });

        // Add Posts Filter Scope
        Posts::extendListFilterScopes(function ($filter) {
            $filter->addScopes([
                'ratmd_bloghub_tags' => [
                    'label' => 'ratmd.bloghub::lang.model.tags.label',
                    'modelClass' => 'RatMD\BlogHub\Models\Tag',
                    'nameFrom' => 'slug',
                    'scope' => 'FilterTags'
                ]
            ]);
        });

        // Extend Backend Users Controller
        BackendUsers::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof BackendUser) {
                return;
            }

            // Add Display Name
            $form->addTabFields([
                'ratmd_bloghub_display_name' => [
                    'label'         => 'ratmd.bloghub::lang.model.users.displayName',
                    'description'   => 'ratmd.bloghub::lang.model.users.displayNameDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'text',
                    'span'          => 'left'
                ],
                'ratmd_bloghub_author_slug' => [
                    'label'         => 'ratmd.bloghub::lang.model.users.authorSlug',
                    'description'   => 'ratmd.bloghub::lang.model.users.authorSlugDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'text',
                    'span'          => 'right'
                ],
                'ratmd_bloghub_about_me' => [
                    'label'         => 'ratmd.bloghub::lang.model.users.aboutMe',
                    'description'   => 'ratmd.bloghub::lang.model.users.aboutMeDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'textarea',
                ]
            ]);
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            \RatMD\BlogHub\Components\PostsByAuthor::class => 'bloghubPostsByAuthor',
            \RatMD\BlogHub\Components\PostsByCommentCount::class => 'bloghubPostsByCommentCount',
            \RatMD\BlogHub\Components\PostsByDate::class => 'bloghubPostsByDate',
            \RatMD\BlogHub\Components\PostsByTag::class => 'bloghubPostsByTag',
            \RatMD\BlogHub\Components\CommentList::class => 'bloghubCommentList',
            \RatMD\BlogHub\Components\CommentSection::class => 'bloghubCommentSection',
            \RatMD\BlogHub\Components\Tags::class => 'bloghubTags',
            \RatMD\BlogHub\Components\PopularPosts::class => 'popularPosts',
            \RatMD\BlogHub\Components\AuthorInfo::class => 'authorInfo',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'ratmd.bloghub.comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.access_comments',
                'comment' => 'ratmd.bloghub::lang.permissions.access_comments_comment',
            ],
            'ratmd.bloghub.comments.access_comments_settings' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.manage_post_settings'
            ],
            'ratmd.bloghub.comments.moderate_comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.moderate_comments'
            ],
            'ratmd.bloghub.comments.delete_comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.delete_commpents'
            ],
            'ratmd.bloghub.tags' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.access_tags',
                'comment' => 'ratmd.bloghub::lang.permissions.access_tags_comment',
            ],
            'ratmd.bloghub.tags.promoted' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'ratmd.bloghub::lang.permissions.promote_tags'
            ]
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }

    /**
     * Registers settings navigation items for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'ratmd_bloghub_config' => [
                'label'         => 'ratmd.bloghub::lang.settings.config.label',
                'description'   => 'ratmd.bloghub::lang.settings.config.description',
                'category'      => 'winter.blog::lang.blog.menu_label',
                'icon'          => 'icon-pencil-square-o',
                'class'         => 'RatMD\BlogHub\Models\BlogHubSettings',
                'order'         => 500,
                'keywords'      => 'blog post meta data',
                'permissions'   => ['winter.blog.manage_settings'],
                'size'          => 'adaptive'
            ]
        ];
    }

    /**
     * Registers any report widgets provided by this package.
     *
     * @return array
     */
    public function registerReportWidgets()
    {
        return [
            \RatMD\BlogHub\ReportWidgets\CommentsList::class => [
                'label' => 'ratmd.bloghub::lang.widgets.comments_list.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts',
                    'ratmd.bloghub.comments'
                ]
            ],
            \RatMD\BlogHub\ReportWidgets\PostsList::class => [
                'label' => 'ratmd.bloghub::lang.widgets.posts_list.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts'
                ]
            ],
            \RatMD\BlogHub\ReportWidgets\PostsStatistics::class => [
                'label' => 'ratmd.bloghub::lang.widgets.posts_statistics.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts'
                ]
            ],
        ];
    }

}
