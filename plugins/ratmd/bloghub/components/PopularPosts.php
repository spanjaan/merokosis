<?php

declare(strict_types=1);

namespace RatMD\BlogHub\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Winter\Blog\Models\Post as PostModel;

class PopularPosts extends ComponentBase
{
    /**
     * A collection of popular posts to display
     *
     * @var Collection
     */
    public $posts;

    /**
     * @var string Reference to the page name for linking to posts.
     */
    public $postPage;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    /**
     * Component Details
     *
     * @return array
     */
    public function componentDetails(): array
    {
        return [
            'name'          => 'ratmd.bloghub::lang.components.popularPosts.label',
            'description'   => 'ratmd.bloghub::lang.components.popularPosts.description'
        ];
    }

    /**
     * Component Properties
     *
     * @return array
     */
    public function defineProperties(): array
    {
        return [
            'amount' => [
                'title'             => 'ratmd.bloghub::lang.components.popularPosts.amount',
                'description'       => 'ratmd.bloghub::lang.components.popularPosts.amount_comment',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'ratmd.bloghub::lang.components.popularPosts.amount_validation',
                'default'           => '3',
            ],
            'postPage' => [
                'title'             => 'ratmd.bloghub::lang.components.popularPosts.post_page',
                'description'       => 'ratmd.bloghub::lang.components.popularPosts.post_page_comment',
                'type'              => 'dropdown',
                'default'           => 'blog/post',
                'options'           => [$this, 'getPageOptions'],
            ],
            'categoryPage' => [
                'title'             => 'ratmd.bloghub::lang.components.popularPosts.category_page',
                'description'       => 'ratmd.bloghub::lang.components.popularPosts.category_page_comment',
                'type'              => 'dropdown',
                'default'           => 'blog/category',
                'options'           => [$this, 'getPageOptions'],
            ]
        ];
    }

    /**
     * Get Page Options for Posts and Categories
     *
     * @return array
     */
    public function getPageOptions(): array
    {
        $pages = Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');

        return $pages;
    }


    /**
     * Run
     *
     * @return void
     */
    public function onRun(): void
    {
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->posts = $this->page['posts'] = $this->listPopularPosts();
    }


    /**
     * Load popular posts
     *
     * @return mixed
     */
    protected function listPopularPosts()
    {
        $query = PostModel::with('categories')
            ->where('published', true)
            ->orderBy('ratmd_bloghub_views', 'desc');

        $amount = intval($this->property('amount'));
        $query->limit($amount);

        $posts = $query->get();

        $posts->each(function ($post) {
            $post->setUrl($this->postPage, $this->controller);
            $post->categories->each(function ($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $posts;
    }


}
