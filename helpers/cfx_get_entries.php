<?php

  function cfx_get_entries($form_id){
    // require(__DIR__ . '/caldera-forms/classes/admin.php');

    $form = Caldera_Forms::get_form('CF55ac7645e24a9');
    // $entries = Caldera_Forms_Admin::get_entries('CF55ac7645e24a9')['entries'];
    // $submission = Caldera_Forms::get_submission_data($form, 1);
    // $entry = Caldera_Forms::get_entry(1, $form);


    $structure = array();
    $field_types = apply_filters( 'caldera_forms_get_field_types', array());
    if(!empty($form['fields'])){
      $headers['date_submitted'] = 'Submitted';
      foreach($form['fields'] as $field_id=>$field){
        if(isset($field_types[$field['type']]['capture']) &&  false === $field_types[$field['type']]['capture']){
          continue;
        }
        $headers[$field['slug']] = $field['label'];
        $structure[$field['slug']] = $field_id;
      }
    }

    global $wpdb;
    $rawdata = $wpdb->get_results($wpdb->prepare("
    SELECT
      `entry`.`id` as `_entryid`,
      `entry`.`form_id` AS `_form_id`,
      `entry`.`datestamp` AS `_date_submitted`,
      `entry`.`user_id` AS `_user_id`

    FROM `" . $wpdb->prefix ."cf_form_entries` AS `entry`
    

    WHERE `entry`.`form_id` = %s
    AND `entry`.`status` = 'active'
    ORDER BY `entry`.`datestamp` DESC;", 'CF55ac7645e24a9'));

    $data = array();

    foreach( $rawdata as $entry){
      $submission = Caldera_Forms::get_entry( $entry->_entryid, $form);
      $data[$entry->_entryid]['date_submitted'] = $entry->_date_submitted;

      foreach ($structure as $slug => $field_id) {
        $data[$entry->_entryid][$slug] = ( isset( $submission['data'][$field_id]['value'] ) ? $submission['data'][$field_id]['value'] : null );
      }
    }

    return $data;

  }