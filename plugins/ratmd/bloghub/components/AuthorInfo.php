<?php

namespace RatMD\BlogHub\Components;

use Cms\Classes\ComponentBase;
use BackendAuth;
use Winter\Storm\Auth\Models\User as AuthUser;
use RatMD\BlogHub\Classes\BlogHubBackendUser;

class AuthorInfo extends ComponentBase
{
    /**
     * Returns information about the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'AuthorInfo Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Defines the properties exposed by the component
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * Executed when the component is run
     */
    public function onRun(AuthUser $user = null)
    {
        // Get the current post
        $post = $this->page['post'];

        // Check if a user is provided or use the current user
        if ($user) {
            $author = new BlogHubBackendUser($user);
        } else {
            // Check if the post exists and retrieve the current user
            if ($post && BackendAuth::check()) {
                $currentUser = BackendAuth::getUser();
                $author = new BlogHubBackendUser($currentUser);
            } else {
                $author = null;
            }
        }

        // Pass the author information to the component's view
        $this->page['author'] = $author;
    }
}
