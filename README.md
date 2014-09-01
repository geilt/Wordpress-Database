#Wordpress Database

##SimpulSections

Creates a Wordpress Post Types with some extra features for Meta Data and Column Sorting. Include it from functions.php. This plugin is meant to be hard coded into your template, there is no admin panel. 

SimpulDatabase by Alexander Conroy
Copyright 2014 @geilt - Alexander Conroy
MIT License  
http://opensource.org/licenses/MIT
Version 2.0

##Configuration

* **$args** array all values get dropped into the constructor
* **post_type** (string)
  * Name of Post Type
* **post_type_singular** (string)
  * Singular version of post type
* **post_type_slug** (string)
  * Allows you to choose a custom slug including the use of wordpress params such as %author% ex: mypostype/%author%
  * Uses method filterPostTypeSlugAuthor when %author% to allow you to nest content based on author
* mixed custom_capabilities 
  * When set to true, creates a new capability based off of the post type. If string will set the post type to use the permissions selected for any permission set.
* array fields 
  * An array of custom meta fields and their types. fieldname => fieldtype. Valid Types: text, image, file, textarea, checkbox, date, datetime, select
* array fields_values 
  * Values used for multi value fields such as select menus. value => label
* array fields_private. 
  * Separate custom meta section for data not meant for front end. Purely organizational. fieldname => fieldtype
* **heirarchical** makes post type heirarchcal (parents)(string)
* array fields_list 
  * Fields that will in Post Type List. Taxonomies automatically show. fieldname => fieldtype
* **fields_location** (string)
  * Where the custom post meta box gets located.'normal', 'advanced', or 'side'
* **fields_priority** (string)
  * What order the custom post meta box shows by default 'high', 'core', 'default' or 'low'
* array taxonomies 
  * Any taxonomy that becomes a key with an array will have custom taxonomy meta set. ex: 'mytax' => array(fieldname => fieldtype). Valid Types: text, image, file, textarea, checkbox, date, datetime, select
  * uses simpul.meta.upload.js but it is now included in a function now and no longer required to include the .js file.