<?php

namespace RatMD\BlogHub\Components;

use Cms\Classes\ComponentBase;
use RatMD\BlogHub\Classes\BlogHubBackendUser;

class AuthorInfo extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'AuthorInfo Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        // Get the current post
        $post = $this->page['post'];

        // Get the author information
        $author = new BlogHubBackendUser($post->user);

        // Pass the author information to the component's view
        $this->page['author'] = $author;
    }
}
