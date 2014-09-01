#Wordpress Database

##SimpulSections

Creates a Wordpress Post Types with some extra features for Meta Data and Column Sorting. Include it from functions.php. This plugin is meant to be hard coded into your template, there is no admin panel. 

SimpulSections by Alexander Conroy
Copyright 2014 @geilt - Alexander Conroy
MIT License  
http://opensource.org/licenses/MIT
Version 2.0

##Configuration

* **$args** (array) all values get dropped into the constructor
* **post_type** (string)
  * Name of Post Type
* **post_type_singular** (string)
  * Singular version of post type
* **post_type_slug** (string)
  * Allows you to choose a custom slug including the use of wordpress params such as %author% ex: mypostype/%author%
  * Uses method filterPostTypeSlugAuthor when %author% to allow you to nest content based on author
* **custom_capabilities** (mixed)
  * When set to true, creates a new capability based off of the post type. If string will set the post type to use the permissions selected for any permission set.
*  **fields** (array)
  * An array of custom meta fields and their types. fieldname => fieldtype. Valid Types: text, image, file, textarea, checkbox, date, datetime, select
*  **fields_values** (array)
  * Values used for multi value fields such as select menus. value => label
*  **fields_private** (array)
  * Separate custom meta section for data not meant for front end. Purely organizational. fieldname => fieldtype
* **heirarchical** (string)
  * makes post type heirarchcal parents/child capabale 
* **backend_only**
  * Makes the post type a backend only post type by setting publicly_queryable to false and ,  exclude_from_search, show_ui, show_ui to true.
*  **fields_list** (array)
  * Fields that will in Post Type List. Taxonomies automatically show. fieldname => fieldtype
* **fields_location** (string)
  * Where the custom post meta box gets located.'normal', 'advanced', or 'side'
* **fields_priority** (string)
  * What order the custom post meta box shows by default 'high', 'core', 'default' or 'low'
*  **taxonomies** (array)
  * Any taxonomy that becomes a key with an array will have custom taxonomy meta set. ex: 'mytax' => array(fieldname => fieldtype). Valid Types: text, image, file, textarea, checkbox, date, datetime, select
  * uses simpul.meta.upload.js but it is now included in a function now and no longer required to include the .js file.

  ##Field Types

  SimpulSections offers some preconfigured meta boxes for inputting data into posts and taxonomies. The following field types are valid, all others will be considered text.

  *text*, *image*, *file*, *textarea*, *checkbox*, *date*, *datetime*, *select*

*File and Image sections use a custom form of the Wordpress Uploaded provided and autoloaded into the Wordpress Admin backend when dealing with the registered post type.*

##Helper functions

* 