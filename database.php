<?php
$sample_args = array('post_type' => 'samples',
              'post_type_singular'  => 'sample',
              'fields'              => array( 
                                        'sample1' => 'select',
                                        'sample2' => 'text'
                                        ),
              'fields_private'      => array(
                                        'sample2' => 'text'
                                        ),
              'fields_list'         => array(
                                        'sample2' => 'text'
                                        ),
              'fields_values'  => array('sample1' 
                                => array('value1' => 'label1', 
                                         'value2' => 'label2'
                                         )
                                ),
              'public'				=> true,
              'fields_location'     => 'side',
              'fields_priority'     => 'high',
              'custom_capabilities' => false,
              'taxonomies'          => array('type' 
                                        => array('featured_image' =>'image'))
              );              
$sample = new SimpulDatabase($sample_args);
  
class SimpulDatabase {
     /**
     * SimpulDatabase by Alexander Conroy
     * Copyright 2014 @geilt - Alexander Conroy
     * MIT License
     * http://opensource.org/licenses/MIT
     * https://github.com/geilt/Wordpress-Database
     * Version 2.0
     * @package Simpul
     */
    
    public function __construct( $args ) {
        $this->post_type             = $args['post_type']; //Set the Post Type (Singular)
        $this->post_type_plural      = $args['post_type_plural']; //Set the Post Type (Plural)
        $this->post_type_slug        = $args['post_type_slug'];
        $this->heirarchical          = !empty($args['heirarchical']) ? true : false;
        $this->custom_capabilities   = $args['custom_capabilities'];
        $this->public                = isset($args['public']) ? $args['public'] : true;       
        
        $this->fields                = $args['fields']; //Set any Meta Fields for the Post Type
        $this->fields_private        = $args['fields_private'];  //Set any Meta Fields for the Post Type that will be Private
        $this->fields_list           = $args['fields_list'];//Set any meta fields you want to show in the list. If it's the same as your fields, then just set the variable to fields/
        $this->fields_values         = $args['fields_values'];  //Used to store key value pair options for select menus and any other multi option menus
        $this->fields_location       = !empty($args['fields_location']) ? $args['fields_location'] : 'side'; 
        $this->fields_priority       = !empty($args['fields_priority']) ? $args['fields_priority'] : 'high'; 
 
        $this->taxonomies            = $args['taxonomies'];

        $this->invalid_tax_types     = array('category', 'tag'); // Default for Wordpress. We do not allow these. Other plugins may conflict.
        
        $this->text_domain = 'simpul';
        
        add_action( 'init', array($this, 'run') );
        
        self::loadMetaScripts();
        self::addColumns();
        self::setMetaFields();
        self::addMetaBoxes();
    }
    //Add Post Types and Taxonomies
    public function run() {
        if(!empty($this->taxonomies)):
            self::createTaxonomies(); // Register Taxonomy First, Because we want Rewrite Rules for Taxonomy to come Before Post Type.
        endif; 
        if(!empty($this->post_type)):
            self::createPostType();
        endif;
    }
    //TYPE AND TAX
    public function createPostType() {
        //Programs  
        $args = array(
                    'labels' => array(
                        'name' => __( self::getLabel($this->post_type_plural) ),
                        'singular_name' => _x(self::getLabel($this->post_type), 'post type singular name'),
                        'add_new' => _x('Add New', self::getLabel( $this->post_type ) ),
                        'add_new_item' => __('Add New ' . self::getLabel( $this->post_type ) ),
                        'edit_item' => __('Edit ' . self::getLabel($this->post_type) ),
                        'new_item' => __('New ' . self::getLabel($this->post_type)),
                        'all_items' => __('All ' . self::getLabel($this->post_type_plural)),
                        'view_item' => __('View ' . self::getLabel($this->post_type)),
                        'search_items' => __('Search ' . self::getLabel($this->post_type_plural)),
                        'not_found' =>  __('No ' . $this->post_type_plural .' found'),
                        'not_found_in_trash' => __('No ' . $this->post_type_plural .' found in Trash'), 
                        'parent_item_colon' => '',
                        'menu_name' => self::getLabel( $this->post_type_plural )
                    ),
        'hierarchical' => $this->heirarchical,
        'public' => $this->public,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'page-attributes', 'revisions', 'excerpt' ));
        if(!empty($this->custom_capabilities)):
            if(is_string($this->custom_capabilities) && is_array($this->custom_capabilities)):
                $args['capability_type'] = $this->custom_capabilities;
            else: 
                $args['map_meta_cap'] = true;
                $args['capability_type'] = array($this->post_type, $this->post_type_singular);
                $args['capabilities'] = array( 
                    'edit_post'              => 'edit_' . $this->post_type_singular,
                    'read_post'              => 'read_' . $this->post_type_singular,
                    'delete_post'            => 'delete_' . $this->post_type_singular,
                    'create_posts'           => 'edit_' . $this->post_type,
                    'edit_posts'             => 'edit_' . $this->post_type,
                    'edit_others_posts'      => 'edit_others_' . $this->post_type,
                    'publish_posts'          => 'publish_' . $this->post_type,
                    'read_private_posts'     => 'read_private_' . $this->post_type,
                        'delete_posts'           => 'delete_' . $this->post_type,
                        'delete_private_posts'   => 'delete_private_' . $this->post_type,
                        'delete_published_posts' => 'delete_published_' . $this->post_type,
                        'delete_others_posts'    => 'delete_others_' . $this->post_type,
                        'edit_private_posts'     => 'edit_private_' . $this->post_type,
                        'edit_published_posts'   => 'edit_published_' . $this->post_type,
                );
            endif;
            add_filter('wp_dropdown_users', array($this, 'themePostAuthorOverride'));
        endif;
        if(!empty($this->backend_only)):
              $args['publicly_queryable']   = false;
              $args['exclude_from_search']  = true;
              $args['show_ui']              = true;
              $args['show_in_menu']         = true;
        endif;
        if(!empty($this->post_type_slug)):
            $args['rewrite'] = array('slug' => $this->post_type_slug );
        endif;

        register_post_type( $this->post_type,
            $args
        );
        add_filter('post_type_link', array($this, 'filterPostTypeSlugAuthor'), 1, 3);
    }
    public function createTaxonomies() {
        foreach($this->taxonomies as $key => $taxonomy):
            if(is_array( $taxonomy )) $taxonomy = $key;
            if(in_array( $taxonomy, $this->invalid_tax_types ) ): // Check if trying to use Category or Tag, rename base name
                $taxonomy_register_name = $taxonomy . "_" . $this->post_type;
            else:
                $taxonomy_register_name = $taxonomy;
            endif;
            $args = array(
                    'labels' => array(
                        'name' => __( self::getLabel($taxonomy)),
                        'singular_name' => __( self::getLabel($taxonomy)),
                        'search_items' =>  __( 'Search ' . self::getLabel($taxonomy) ),
                        'all_items' => __( 'All ' . self::getLabel($taxonomy) ),
                        'parent_item' => __( 'Parent ' . self::getLabel($taxonomy) ),
                        'parent_item_colon' => __( 'Parent ' . self::getLabel($taxonomy) . ':' ),
                        'edit_item' => __( 'Edit ' . self::getLabel($taxonomy) ), 
                        'update_item' => __( 'Update ' . self::getLabel($taxonomy)),
                        'add_new_item' => __( 'Add New ' . self::getLabel($taxonomy) ),
                        'new_item_name' => __( 'New ' . self::getLabel($taxonomy) . ' Name' ),
                        'menu_name' => __(  self::getLabel($taxonomy) ) ),
                    'sort' => true,
                    'public' => isset($this->public) ? $this->public : true,
                    'show_admin_column' => true,
                    'hierarchical' => true,
                    'show_ui' => true,
                    'query_var' => true,
                    'args' => array( 'orderby' => 'term_order' ),
                    'rewrite' => array(
                                    'slug' => $this->post_type . "/" .  str_replace( "_", "-", $taxonomy ), 
                                    'with_front' => true,
                                    'heirarchical' => true )
            );
            if(!empty($this->custom_capabilities)):
            $args['capabilities'] = array(
                        'manage_terms' => 'manage_' . $taxonomy_register_name,
                        'edit_terms' => 'edit_' . $taxonomy_register_name,
                        'delete_terms' => 'delete_' . $taxonomy_register_name,
                        'assign_terms' => 'assign_' . $taxonomy_register_name,
                    );
            endif;
            register_taxonomy(
                $taxonomy_register_name,
                $this->post_type,
                $args
            );
            if(!empty($this->taxonomy_meta_fields[$taxonomy_register_name])):
                add_action( $taxonomy_register_name . '_add_form_fields', array($this, 'AddTaxonomyMeta'), 10, 2 );
                add_action( $taxonomy_register_name . '_edit_form_fields', array($this, 'editTaxonomyMeta'), 10, 2 );
                add_action( 'edit_' . $taxonomy_register_name, array($this, 'saveTaxonomyMeta'), 10, 2 );  
                add_action( 'create_' . $taxonomy_register_name, array($this, 'saveTaxonomyMeta'), 10, 2 );
            endif;
        endforeach;
    }
    /*
     * Allows you to use %author% in your post_type slug which will nest content under it based on author. For example: /myposttpye/theauthor/anarticle. Then you can restrict a user to their own posts in the section to add edit and publish. No idea how this works with taxonomies though! Probably does nest under the user as well since my method forces taxonomies under the main post type...Fixed 5/9 had strpos backwards so was always loading...sigh
     */
    public function filterPostTypeSlugAuthor( $post_link, $id = 0, $leavename = FALSE ) {
        if ( strpos( $post_link, '%author%') !== FALSE ):
            $post = get_post($id);
            $author = get_userdata($post->post_author);
            return str_replace('%author%', $author->user_nicename, $post_link);
        else:
            return $post_link;
        endif;
    }
    /*
     * This function lists all wordpress users regardless of their role. Allows for Custom Members plugin to be used and still select a proper user for a post, while preventing them from publishing their own. (These are set in the Members Plugin.)
     */
    public function themePostAuthorOverride($output){
      global $user_ID, $post;
      // return if this isn't the theme author override dropdown
      if (!preg_match('/post_author_override/', $output)) return $output;
    
      // return if we've already replaced the list (end recursion)
      if (preg_match ('/post_author_override_replaced/', $output)) return $output;
    
      // replacement call to wp_dropdown_users
        $output = wp_dropdown_users(array(
          'echo' => 0,
            'name' => 'post_author_override_replaced',
            'selected' => empty($post->ID) ? $user_ID : $post->post_author,
            'include_selected' => true
        ));
    
        // put the original name back
        $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);
    
      return $output;
    }
    // Add term page
    public function AddTaxonomyMeta() {
        foreach($this->taxonomy_meta_fields[$_GET['taxonomy']] as $field => $label):
            if($this->taxonomy_meta_fields_formats[$_GET['taxonomy']][$field] == 'textarea'):
                echo '<div>';
            else:
                echo '<div class="form-field">';
            endif;
            self::formatFields($post, $field, $label, $this->taxonomy_meta_fields_formats[$_GET['taxonomy']][$field], $_GET['taxonomy']);
            echo '</div>';
        endforeach;
        
    }
    function editTaxonomyMeta($term){ 
        foreach($this->taxonomy_meta_fields[$_GET['taxonomy']] as $field => $label):
            self::formatFields($term->term_id, $field, $label, $this->taxonomy_meta_fields_formats[$_GET['taxonomy']][$field], $_GET['taxonomy']);
        endforeach;
    }
    function saveTaxonomyMeta($term_id){
        if ( isset( $_POST['term_meta'] ) ):
            $term_meta = get_option( "taxonomy_" . $term_id );
            $meta_keys = array_keys( $_POST['term_meta'] );
            foreach ( $meta_keys as $key ):
                if ( isset ( $_POST['term_meta'][$key] ) ) :
                    $term_meta[$key] = $_POST['term_meta'][$key];
                endif;
            endforeach;
            update_option( "taxonomy_" . $term_id, $term_meta );
        endif;
    }

    //POST META
    //Set Meta Field Variables for use in the Script
    public function loadMetaScripts(){
        global $post;
        add_action( 'admin_head', array( $this, 'simpulMetaUpload' ), 11);
        if( $post->post_type == $this->post_type || $_GET['post_type'] == $this->post_type ): //These scripts interfere with regular post insertion =(
            add_action( 'admin_print_scripts', array( $this, 'registerScripts' ) );
            
            add_action( 'admin_print_styles' , array( $this, 'registerStyles' ) );
        endif;
    }
    public function setMetaFields()
    {
        if(!empty($this->fields) && is_array($this->fields)):
            foreach($this->fields as $key => $value):
                $this->meta_fields[$key] = $value;
            endforeach;
        endif;
        //Iterate and Create Label.
        if(!empty($this->meta_fields) && is_array($this->meta_fields)):
            foreach($this->meta_fields as $field => $format):
                    $this->meta_box_fields[$field] = self::getLabel($field);
                    $this->meta_box_fields_formats[$field] = $format;
            endforeach;
        endif;
        if(!empty($this->taxonomies) && is_array($this->taxonomies)):
            foreach($this->taxonomies as $key => $taxonomy):
                if(is_array($taxonomy) && !empty($taxonomy)):
                    if(in_array( $key, $this->invalid_tax_types ) ): // Check if trying to use Category or Tag, rename base name
                        $taxonomy_register_name = $key . "_" . $this->post_type;
                    else:
                        $taxonomy_register_name = $key;
                    endif;
                    foreach($taxonomy as $field => $format):
                        $this->taxonomy_meta_fields[$taxonomy_register_name][$field] = self::getLabel($field);
                        $this->taxonomy_meta_fields_formats[$taxonomy_register_name][$field] = $format;
                    endforeach;
                endif;
            endforeach;
        endif;
        
    }
    //Add or Remove Columns for Post Type
    public function addColumns()
    {
        add_filter( 'manage_edit-' . $this->post_type . '_columns', array($this, 'editColumns') ) ; // Add or Remove a Column
        add_action( 'manage_' . $this->post_type . '_posts_custom_column', array($this, 'manageColumns') ); //Show and Modify Column Data
        add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array($this, 'sortableColumns') ); // Flags sortable Columns
        add_action( 'load-edit.php', array($this, 'loadSortColumns') );
    }
    //Add Meta Boxes
    public function addMetaBoxes()
    {
        if(!empty($this->meta_fields)):
            add_action( 'add_meta_boxes', array($this, 'addCustomBox') ); // Add Meta Box
            add_action( 'save_post', array($this, 'savePostData') ); // Save Meta Box Info
        endif;
    }
    //Add the Custom Meta Box
    public function addCustomBox() {
        add_meta_box( 
            $this->post_type . '_meta',
            __( self::getLabel($this->post_type) . ' Meta', $this->post_type . '_meta' ),
            array($this, 'innerCustomBox'),
            $this->post_type,
            $this->fields_location, 
            $this->fields_priority
        ); 
    }
    //Add the Inner Custom Meta Box and the custom Meta Fields
    public function innerCustomBox( $post ) {
        global $post;
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );
        echo '<table class="widefat" cellpadding="0" cellspacing="0" border="0">';
        //Add this to work with Custom Javascript that needs image dir. 
        echo '<span id="bloginfo" stylesheet_dir_uri="' . get_template_directory_uri() . '" style="display: none">';
        // The actual fields for data entry
        //Iterate through the array. Will expand or contract as fields are added in the database (through scraper).
        foreach($this->meta_box_fields as $field => $label):
            echo self::formatFields($post, $field, $label, $this->meta_box_fields_formats[$field] );
        endforeach;
        echo "</table>";
      
    }
    //Saves the Meta Box Values
    public function savePostData( $post_id ) {
      global $post;
      // verify if this is an auto save routine. 
      // If it is our form has not been submitted, so we dont want to do anything
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
          return;
    
      // verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times
      
      // Check permissions
     
      if ( !current_user_can( 'edit_post', $post->ID) )
          return;
    
      // OK, we're authenticated: we need to find and save the data
      if(!empty($this->meta_box_fields_formats) && is_array($this->meta_box_fields_formats)):
        foreach($this->meta_box_fields_formats as $field => $format):
            if($_POST[$field]):
                switch( $format ):
                case "datetime":
                    update_post_meta($post->ID, $field, date("Y-m-d H:i:s", strtotime( $_POST[$field] ) ) );
                    break;
                case "date":
                    update_post_meta($post->ID, $field, date("Y-m-d", strtotime( $_POST[$field] ) ) );
                    break;
                case "section":
                    break;
                default:
                    update_post_meta($post->ID, $field, $_POST[$field]);
                    break;
                endswitch;
            else:
                if( get_post_meta($post->ID, $field, true) ):
                    delete_post_meta($post->ID, $field); // Useful for Checkboxes. Delete completely.
                endif;
            endif;
        endforeach;
      endif;
      // Do something with $mydata 
      // probably using add_post_meta(), update_post_meta(), or 
      // a custom table (see Further Reading section below)
    }
    //Show Columns in the Post Type List
    public function editColumns( $columns ) {
        
        unset($columns['author']); // Remove author, we normally dont need it
        unset($columns['date']);  // Remove date, we normally dont need it
        unset($columns['categories']); 
 
        foreach($this->fields_list as $field => $label):
                $columns[$field] = __( self::getLabel($field) );
        endforeach;
        return $columns;
    }
    //Show the Column Data in the Post Type List
    public function manageColumns( $column ){
        global $post; 
        if( $this->fields_list[$column] == 'checkbox' && get_post_meta( $post->ID, $column, true) == 1):
            echo "Yes";
        else:
            echo __( get_post_meta($post->ID, $column, true) );
        endif;
    }
    //Define which Columns are sortable
    public function sortableColumns( $columns )
    {
        foreach($this->fields_list as $field => $label):
            $columns[$field] = $field;
        endforeach; 
        
        return $columns;
    }
    //Load / Filter out the Sortable Columns
    public function loadSortColumns()
    {
        add_filter( 'request', array( $this, 'sortColumns' ) );
    }
    //Actually sort the columns, needed to be able to sort by Meta Data
    public function sortColumns( $vars )
    {
        /* Check if we're viewing the 'movie' post type. */
        if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
                
            /* Check if 'orderby' is set to 'duration'. */
            foreach($this->fields_list as $field => $label):
            
                if ( isset( $vars['orderby'] ) && $field == $vars['orderby'] ) {
        
                    /* Merge the query vars with our custom variables. */
                    $vars = array_merge(
                        $vars,
                        array(
                            'meta_key' => $field,
                            'orderby' => 'meta_value'
                        )
                    );
                }
            
            endforeach;
        }
    
        return $vars;
    }

    // HELPER FUNCTIONS
  
    //$post_id doubles for term_id if passed
    public function formatFields($post, $field_name, $label, $format, $term = false )
    {  
        if(is_array($format)):
            $type = array_keys($format)[0];
        else:
            $type = $format;
        endif;
      $id_selector = preg_replace('/[^\w\d_]/', '_', $field_name);
      if($term):
          if($format != 'textarea'):
            echo '<tr class="form-field">';
          else:
            echo "<tr>";
          endif;
          $term_meta = get_option( "taxonomy_" . $post );
          $value = $term_meta[$field_name];
          $field_name = 'term_meta[' . $field_name . ']';
      else:
          echo "<tr>";
          $value = get_post_meta($post->ID, $field_name ,true);
      endif;
      switch($type):
         case "datetime":
            echo '<th><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
            echo '</label> </th>';  
            echo '<td><input type="text" id="' . $id_selector . '" name="' . $field_name . '" class="hasDateTimePicker" value="' . $value . '"  /></td>';
            break;
        case "date":
            echo '<th><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
            echo '</label> </th>';  
            echo '<td><input type="text" id="' . $id_selector . '" name="' . $field_name . '" class="hasDatePicker" value="' . $value . '"  /></td>';
            break;
        case "image":
            if($term):
                echo '<th><label for="' . $id_selector . '">';
                 _e($label, $this->text_domain );
                echo '</label><br />';
                echo '</th><td style="vertical-align: top;">
                <input id="simpul_meta_upload_' . $id_selector . '" class="regular-text" type="text" name="' . $field_name . '" value="' . $value . '" style="width: 70%; margin: 0;"/>
                <button class="simpul_meta_upload button-secondary" data-input="simpul_meta_upload_' . $id_selector . '" type="button">Browse</button>';
                echo "</td>";
                 $image = $value;
                if($image):
                  echo '<td><img src="' . $image . '" style="max-width: 100px; margin-top: 10px;" /></td>';
                endif;  
            else:
                echo '<th colspan="2"><label for="' . $id_selector . '">';
                 _e($label, $this->text_domain );
                echo '</label><br>';  
                echo '
                <input id="simpul_meta_upload_' . $id_selector . '" class="regular-text" type="text" name="' . $field_name . '" value="' . $value . '" style="width: 70%; margin: 0;"/>
                <button class="simpul_meta_upload button-secondary" data-input="simpul_meta_upload_' . $id_selector . '" type="button">Browse</button>';
                $image = $value;
                if($image):
                  echo '<img src="' . $image . '" style="width: 100%; margin-top: 10px;" />';
                endif;
                echo "</th>";
            endif;
            break;
        case "file":
            echo '<th colspan="2"><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
            echo '</label><br>';    
            echo '
            <input id="simpul_meta_upload_' . $id_selector . '" class="regular-text" type="text" name="' . $field_name . '" value="' . $value . '" style="width: 70%; margin: 0;"/>
            <button class="simpul_meta_upload button-secondary" data-input="simpul_meta_upload_' . $id_selector . '" type="button">Browse</button>';
            echo "</th>";
            break;
        case "checkbox":
            if($value) $checked = "checked"; else $checked = '';
            echo '<th><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
            echo '</label><br>';    
            echo '<td><input type="checkbox" id="' . $id_selector . '" name="' . $field_name . '" value="1" ' . $checked . '/></td>';
            break;
        case "select":
            if(!empty($this->fields_values[$field_name])):
                echo '<th><label for="' . $id_selector . '">';
                _e($label, $this->text_domain );
                echo '</label><br>';    
                echo '<td><select id="' . $id_selector . '" name="' . $field_name . '" style="width: 100%;">';
                foreach($this->fields_values[$field_name] as $key => $option):
                    if($key == $value):
                        echo '<option value="' . $key . '" selected="selected">' . $option . '</option>';
                    else:
                        echo '<option value="' . $key . '">' . $option . '</option>';
                    endif;
                endforeach;
                echo '</select></td>';
            endif;
            break;
        case "textarea":
            if($term || $this->fields_location != 'side'):
                echo '<th colspan="2"><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
                echo '</label>';    
                wp_editor($value, $id_selector, array('textarea_name' => $field_name));
                echo '</th>';    
            else:
                echo '<th colspan="2"><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
                echo '</label><br>';    
                echo '<textarea rows="4" style="width: 100%;" id="' . $field_name . '" name="' . $field_name . '">' . $value . '</textarea></th>';
            endif;
            break;
        case "section":
            echo '<th colspan="2"><strong>';
            _e($label, $this->text_domain);
            echo '</strong></th>';
            break;
        case "taxonomy":
            $taxonomy = array_values($format)[0];
            $terms = get_terms($taxonomy, array('hide_empty' => false));
            echo '<th><label for="' . $id_selector . '">';
            _e($label, $this->text_domain );
            echo '</label><br>';    
            echo '<td><select id="' . $id_selector . '" name="' . $field_name . '" style="width: 100%;">';
            foreach($terms as $term):
                if($term->slug == $value):
                    echo '<option value="' . $term->slug . '" selected="selected">' . $term->name . '</option>';
                else:
                    echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                endif;
            endforeach;
            echo '</select></td>';
            break; 
        default:
            echo '<th><label for="' . $id_selector . '">';
             _e($label, $this->text_domain );
            echo '</label></th>';
            echo '<td><input type="text" id="' . $id_selector . '" name="' . $field_name . '" value="' . $value . '" style="width: 100%" /></td>';
            break;
      endswitch;
      echo "</tr>";
    }
    //Formats Meta Fields
    public function getLabel($key)
    {
        $glued = array();
        if( strpos( $key, "-" ) ) $pieces = explode( "-", $key );
        elseif( strpos( $key, "_" ) ) $pieces = explode( "_", $key );
        else $pieces = explode(" ", $key);
        
        foreach($pieces as $piece):
            if($piece == "id"):
                $glued[] = strtoupper($piece);
            else:
                $glued[] = ucfirst($piece);
            endif;
        endforeach;
            
        return implode(" ", $glued);
    }
    
    ///////////////////////////
    ///END HELPER FUNCTIONS////
    ///////////////////////////
    
    ///////////////////////////
    /////REGISTER SCRIPTS//////
    ///////////////////////////
    
    public function registerScripts()
    {
        if(!wp_script_is('media-upload')):
            wp_enqueue_script('media-upload');
        endif;
        if(!wp_script_is('thickbox')):
            wp_enqueue_script('thickbox');
        endif;
    }
    public function registerStyles()
    {
        wp_enqueue_style('thickbox');
        //wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css');
    }
    public function simpulMetaUpload(){
        global $post;
        if(empty($GLOBALS['simpul_meta_upload']) && ($post->post_type == $this->post_type || $_GET['post_type'] == $this->post_type)):
        $GLOBALS['simpul_meta_upload'] = true;
        ?>
<script type="text/javascript">
    var original_send_to_editor = "";  
    var modified_send_to_editor = "";
    var formfield = '';
    var hrefurl = '';
     
    jQuery(document).ready( function() {    
        
        original_send_to_editor = window.send_to_editor;
        
        modified_send_to_editor = function(html) {
                    hrefurl = jQuery('img',html).attr('src');
                    console.log(jQuery(html));
                    if(!hrefurl) {
                        hrefurl = jQuery(html).attr('href'); // We do this to get Links like PDF's
                    }
                    hrefurl = hrefurl.substr(hrefurl.indexOf('/',8)); // Skips "https://" and extracts after first instance of "/" for relative URL, ex. "/wp-content/themes/currentheme/images/etc.jpg"
                     console.log(hrefurl);
                    jQuery('#' + formfield).val(hrefurl);
                    tb_remove();
                    window.send_to_editor = original_send_to_editor;
                };          
        
        jQuery('.simpul_meta_upload').click(function() {
            window.send_to_editor = modified_send_to_editor;
            formfield = jQuery(this).attr('data-input');
            tb_show('Add File', 'media-upload.php?TB_iframe=true');
            console.log(formfield);
            return false;
        });
    } );
</script>
    
        <?php
        endif;
    }

}

if(!function_exists('get_term_meta')):
    function get_term_meta($term, $taxonomy, $key, $filter = false ){
        if(!$taxonomy && !$term):
            return false;
        endif;
        if(!is_numeric($term)):
            $term = get_term_by('slug', $term, $taxonomy);
            $term = $term->term_id;
        endif;
        $term = get_option('taxonomy_' . $term);
        if($key):
            
            if(!empty($term[$key])):
                $meta = $term[$key];
                if($filter):
                    $meta = apply_filters($filter, $meta);
                    $meta= str_replace(']]>', ']]>', $meta);
                endif;
                return $meta; 
            else: 
                return '';
            endif;
        endif;
            
        return $term;
    }
endif;
if(!function_exists('the_term_meta')):
    function the_term_meta($term, $taxonomy, $key, $filter = false  ){
        if(!$term && !$taxonomy && !$value):
            return false;
        endif;
        $meta = get_term_meta($term, $taxonomy, $key, $filter);
        
        echo $meta;
    }
endif;