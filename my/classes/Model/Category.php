<?php defined('SYSPATH') or die('No direct script access.');
class Model_Category extends ORM_Nested_Sets{
 
    /**
     * Use or not scope for multi root's tree
     *
     * @var bool
     */
    protected $use_scope = FALSE;
 
    /**
     * Table columns
     *
     * Field name => Label
     *
     * @var array
     */
    protected  $_table_columns = array(
        'id'            => 'id',
        'lft'           => 'lft',
        'rgt'           => 'rgt',
        'level'         => 'level',
        'name'          => 'name',
        'description'   => 'description',
    );
 
} // End Model_Category