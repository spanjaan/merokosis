<?php

namespace Spanjaan\Workspace\Models;

use Model;
use Illuminate\Support\Facades\Validator;
use Winter\Storm\Database\Models\ValidationException;

/**
 * record Model
 */
class Record extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'spanjaan_workspace_records';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'c_name'     => ['required', 'regex:/^[A-Za-z\s]+$/'],
        'c_address'  => ['required', 'custom_alpha_num_dash'],
        'c_mobile'   => ['required', 'regex:/^\d{10}$/'],
        'c_email'    => ['required', 'email', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/'],
        'c_document' => 'required'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = ['c_document' => ['System\Models\File', 'public' => false]];
    public $attachMany = [];

    public $customMessages = [
        'c_name.required' => 'The name field is required.',
        'c_name.regex' => 'The name field must contain only letters and spaces.',
        'c_address.required' => 'The address field is required.',
        'c_address.custom_alpha_num_dash' => 'The address field must contain letters, numbers, spaces, commas, and dashes.',
        'c_mobile.required' => 'The mobile field is required.',
        'c_mobile.regex' => 'The mobile field must be a 10-digit number.',
        'c_email.required' => 'The email field is required.',
        'c_email.email' => 'The email field must be a valid email address.',
        'c_email.regex' => 'The email field must be a valid email address.',
        'c_document.required' => 'The document field is required.'
    ];

    public function beforeValidate()
    {
        $this->extendValidationRules();
    }

    protected function extendValidationRules()
    {
        Validator::extend('custom_alpha_num_dash', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[\pL\pN\s,-]+$/u', $value);
        });
    }
}
