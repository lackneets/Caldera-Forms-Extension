<?php

add_action('init', 'cfx_create_post_type');
add_action('caldera_forms_save_form_register', 'cfx_migrate_post_type'); // when processor saved

function cfx_migrate_post_type($form_options){

  $processor_setting = $form_options['processors'][$form_options['cfx_submission_id']];
  $post_type_slug = $processor_setting['config']['slug'];
  $previous_slug  = $processor_setting['config']['previous_slug'];
  $slug_migrate   = $processor_setting['config']['slug_migrate'] == 'migrate';

  if($post_type_slug != $previous_slug and $slug_migrate){

    $post_ids = get_posts(array('post_per_page' => -1, 'post_type' => $previous_slug));
     foreach($post_ids as $p){
      $po = array();
      $po = get_post($p->ID,'ARRAY_A');
      $po['post_type'] = $post_type_slug;
      wp_update_post($po);
    }
    echo "Do migrate from $previous_slug to $post_type_slug"; exit();
  }
}

function cfx_create_post_type() {

  global $cfx_forms;

  $cfx_forms = array();

  if(! cfx_caldera_loaded() ){
    return false;
  }

  foreach(Caldera_Forms::get_forms() as $form_id => $form_detail){

    $list_columns = array();
    //$post_type = 'cfx_' . strtolower($form_id);

    $form_options = get_option($form_id);
    $processor_setting  = $form_options['processors'][$form_options['cfx_submission_id']];
    $post_type_slug     = $processor_setting['config']['slug'];
    $title_field        = preg_replace('/^%(.+)%$/', '$1', $processor_setting['config']['title_field']);
    $has_title_field    = false;

    $cfx_forms[$post_type_slug] = array(
      'id' => $form_id,
      'name'    => $form_detail['name'],
      'fields'  => array(),
    );

    foreach (Caldera_Forms::get_form($form_id)['fields'] as $fid => $field) {
      if($field['type'] == 'html') continue;
      if($field['type'] == 'button') continue;
      if($field['entry_list'] == 1) $list_columns[$field['slug']] = $field['label']; // 顯示在 list 上的欄位
      if($field['slug']== $title_field) $has_title_field = true;
      $cfx_forms[$post_type_slug]['fields'][$field['slug']] = array(
        'label' => $field['label'],
        'id'    => $fid,
      );
    }

    register_post_type( $post_type_slug,
      array(
        'labels'              => array(
          'name'                => $form_detail['name'],
          'singular_name'       => $form_detail['name'],
        ),
        'has_archive'         => true,
        'menu_position'       => 5,
        'public'              => true,
        'publicly_queryable'  => ($processor_setting['config']['privacy'] == 'public'),
        'query_var'           => true,
        'rewrite'             => array('slug' => ''),
        'show_in_menu'        => true,
        'show_ui'             => true,
        'supports'            => array('id', 'custom-fields'),
        //'taxonomies'          => array('category'),
      )
    );

    // Add function buttons to list view
    add_filter('views_edit-'.$post_type_slug, function($views) use ($post_type_slug){
      $views['export-all'] = '<a href="?cfx_export_csv='.$post_type_slug.'"><button type="button" title="Export all as spreadsheet" style="margin:5px">Export CSV</button></a>';
      return $views;
    });

    // Define Post Columns
    add_filter('manage_'.$post_type_slug.'_posts_columns', function($columns) use ($list_columns, $has_title_field, $title_field){ 

      $new_columns['cb'] = '<input type="checkbox" />';

      if($has_title_field){
        $new_columns['cfx_' . $title_field] = __('Title'); // this will be replaced with actual column name later
      }else{
        $new_columns['cfx_' . $title_field] = __('Edit');
      }

      foreach($list_columns as $slug => $title){
        $new_columns['cfx_' . $slug] = $title;
      }

      $new_columns['date'] = _x('Date', 'column name');

      return $new_columns; 
    });

    // Post Columns Data
    add_action('manage_'.$post_type_slug.'_posts_custom_column', function( $column, $post_id ) use ($list_columns, $has_title_field, $title_field){
      $meta = get_post_meta( $post_id , $column , true );
      $post = get_post($post_id);
      // Translate youtube link
      if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $meta, $match)){
        $meta = '<a href="'.$meta.'" target="_blank"><img src="//img.youtube.com/vi/'.$match[1].'/mqdefault.jpg" style="height:80px; width:auto;"></a>'; 
      }
      // If the columns is set as title
      if($has_title_field and 'cfx_' . $title_field == $column){
        $draftOrPending = in_array($post->post_status, array('pending', 'draft')) ? ' - <span class="post-state">'._x(ucfirst($post->post_status), 'post state').'</span>' : '';
        echo '<strong><a class="row-title" href="'.get_edit_post_link( $post->ID ).'">'.$meta.'</a>'.$draftOrPending.'</strong>' . cfx_row_actions($post);
        return;
      }
      // If no columns are title, just print row actions
      if(!$has_title_field and 'cfx_' . $title_field == $column){
        echo cfx_row_actions($post, true);
        return;
      }
      echo $meta;
    }, 10, 2);

  }
}

function cfx_row_actions($post, $always_show = false) {

  $edit_link = get_edit_post_link( $post->ID );
  $post_type_object = get_post_type_object( $post->post_type );
  $can_edit_post = current_user_can( 'edit_post', $post->ID );

  $actions = array();
  if ( $can_edit_post && 'trash' != $post->post_status ) {
    $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '" title="' . esc_attr__( 'Edit this item' ) . '">' . __( 'Edit' ) . '</a>';
    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr__( 'Edit this item inline' ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
  }
  if ( current_user_can( 'delete_post', $post->ID ) ) {
    if ( 'trash' == $post->post_status )
      $actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from the Trash' ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
    elseif ( EMPTY_TRASH_DAYS )
      $actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Move this item to the Trash' ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
    if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
      $actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Delete this item permanently' ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
  }
  if ( $post_type_object->public ) {
    if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
      if ( $can_edit_post ) {
        $preview_link = set_url_scheme( get_permalink( $post->ID ) );
        /** This filter is documented in wp-admin/includes/meta-boxes.php */
        $preview_link = apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ), $post );
        $actions['view'] = '<a href="' . esc_url( $preview_link ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'Preview' ) . '</a>';
      }
    } elseif ( 'trash' != $post->post_status ) {
      $actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
    }
  }

  // from WP_List_Table::row_actions (protected)
  $action_count = count( $actions );
  $i = 0;

  if ( !$action_count )
    return '';

  $out = '<div class="' . ( $always_show ? 'row-actions visible' : 'row-actions' ) . '">';
  foreach ( $actions as $action => $link ) {
    ++$i;
    ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
    $out .= "<span class='$action'>$link$sep</span>";
  }
  $out .= '</div>';

  return $out;
}