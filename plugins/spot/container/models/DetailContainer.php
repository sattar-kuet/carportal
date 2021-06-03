<?php namespace spot\Container\Models;

use Model;

/**
 * Model
 */
class DetailContainer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'spot_container_detail';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
