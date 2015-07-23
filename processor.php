<?php

add_filter('caldera_forms_get_form_processors', 'cfx_register_processor');

function cfx_register_processor($processors){
  $processors['cfx_submission'] = array(
    "name"          =>  'Caldera Form Extension',
    "description"   =>  'Save Submission as post',
    "single"        =>  true,
    "processor"     =>  'cfx_processor',
    "template"      =>  plugin_dir_path(__FILE__) . "config.php",
    "icon"          =>  plugin_dir_url(__FILE__) . "processor.png",
    "conditionals"  =>  false,
  );
  return $processors;
}

function cfx_processor($config, $form){

  $data = array();
  $raw_data = Caldera_Forms::get_submission_data( $form );
  foreach( $raw_data as $field_id => $field_value ){
    if( in_array( $form[ 'fields' ][ $field_id ][ 'type' ], array( 'button', 'html' ) ) )
      continue;
    $data[ $form[ 'fields' ][ $field_id ][ 'slug' ] ] = $field_value;
  }

  $form_options = get_option($form['ID']);
  $processor_setting = $form_options['processors'][$form_options['cfx_submission_id']];
  $post_type_slug = $processor_setting['config']['slug'];

  //cfx_create_category($form['ID'], $form['name']);
  cfx_create_post($data, $post_type_slug);
}

function cfx_create_category($slug, $term){
  wp_insert_term($term, 'category', array(
    'slug' => $slug
  ));
}

function cfx_create_post($data, $category){
  $post = wp_insert_post( array(
    'title' => @$data['title'],
    'post_status' => 'pending',
    'post_type' => $category,
    //'post_category' => $category
  ), $wp_error );

  foreach($data as $key => $value){
    update_post_meta($post, 'cfx_'.$key, $value);
  }
  return $post;
}