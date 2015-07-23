<?php

add_action( 'load-edit.php', function()
{

  global $cfx_forms;

  if(isset($_REQUEST['cfx_export_csv'])){

    $post_type = $_REQUEST['cfx_export_csv'];

    $fields = array();
    $fieldsSlug = array();

    foreach($cfx_forms[$post_type]['fields'] as $slug => $field){
      $fields[] = $field['label'];
      $fieldsSlug[] = $slug;
    }

    $csvArray = array(
      array_merge($fields, array('Date')),
      //array_merge($fieldsSlug, array('submit-date')),
    );

    $entries = get_posts(array(
      'post_type' => $post_type,
      'orderby'   => 'date',
      'order'     => 'DESC',
      'post_status' => array('publish', 'pending'),
    ));

    foreach($entries as $post){
      $postFields = array();
      $postMetas = get_post_meta($post->ID, '', true);
      foreach ($fieldsSlug as $slug) {
        if(array_key_exists('cfx_'.$slug, $postMetas)){
          $postFields[] = $postMetas['cfx_'.$slug][0];
        }else{
          $postFields[] = '';
        }
      }
      $postFields[] = $post->post_date;
      $csvArray[] = $postFields;
    }

    cfx_outputCSV($csvArray, 'export-' . $cfx_forms[$post_type]['name']);
  }
});

function cfx_outputCSV($dataArray, $filename = 'export') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename={$filename}.csv");
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: 0"); // Proxies  

    $output = fopen("php://output", "w");
    foreach ($dataArray as $row) {
        fputcsv($output, $row); // here you can change delimiter/enclosure
    }
    fclose($output);
    exit();
}