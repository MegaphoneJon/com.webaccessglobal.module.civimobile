<?php

require_once 'civimobile.civix.php';
// phpcs:disable
use CRM_Civimobile_ExtensionUtil as E;
// phpcs:enable

require_once 'code/civimobile.php';
function civimobile_civicrm_config( &$config ) {
  $tabRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  // fix php include path
  $include_path = $tabRoot .'code'. PATH_SEPARATOR . get_include_path( );
  set_include_path( $include_path );

  $template =& CRM_Core_Smarty::singleton();

  $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'templates';

  if ( is_array( $template->template_dir ) ) {
      array_unshift( $template->template_dir, $extDir );
  } else {
      $template->template_dir = array( $extDir, $template->template_dir );
  }
}

function civimobile_civicrm_xmlMenu( &$files ) {
  $files[] = dirname(__FILE__)."/civimobile.xml";
}

function civimobile_civicrm_navigationMenu( &$params ) {
  _civimobile_civix_insert_navigation_menu($params, 'Administer/Customize Data and Screens', [
    'label' => E::ts('CiviMobile'),
    'name' => 'CiviMobile Options',
    'url' => 'civicrm/admin/setting/mobile',
    'permission' => 'administer CiviCRM',
  ]);
  // get the maximum key of $params
  $maxKey = ( max( array_keys($params) ) );
  $params[$maxKey+1] =  array (
    'attributes' => array (
      'label'      => 'CiviMobile',
      'name'       => 'CiviMobile',
      'url'        => 'civicrm/mobile',
      'permission' => 'administer CiviCRM',
      'operator'   => null,
      'separator'  => null,
      'parentID'   => 2,
      'navID'      => $maxKey+1,
      'active'     => 1
    )
  );
}
