<?php
auth_reauthenticate();

require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';

$specmanagement_database_api = new specmanagement_database_api();
$update = gpc_get_bool( 'update', false );
$addversion = gpc_get_bool( 'addversion', false );

/**
 * Submit new version
 */
if ( $addversion && isset( $_POST['new_version_name'] ) && isset( $_POST['new_version_date'] ) )
{
   $project_id = helper_get_current_project();
   $new_version_date = new DateTime( $_POST['new_version_date'] );
   $new_version_date_timestamp = date_timestamp_get( $new_version_date );
   $new_version_name = $_POST['new_version_name'];
   $new_version_name_trimmed = trim( preg_replace( '/\s+/', ' ', $new_version_name ) );

   if ( version_is_unique( $new_version_name_trimmed, $project_id ) && strlen( $new_version_name_trimmed ) > 0 )
   {
      version_add( $project_id, $new_version_name_trimmed, false, '', $new_version_date_timestamp );
   }
}

/**
 * Change all existing versions
 */
if ( $update && isset( $_POST['version_ids'] ) )
{
   $version_ids = $_POST['version_ids'];
   $versions = $_POST['version'];
   $date_order = $_POST['date_order'];
   $type = $_POST['type'];
   $description = $_POST['description'];

   for ( $version_index = 0; $version_index < count( $version_ids ); $version_index++ )
   {
      $version = version_get( $version_ids[$version_index] );
      $version_id = $version->id;
      $project_id = version_get_field( $version_id, 'project_id' );

      $released = null;
      $obsolete = null;

      if ( isset( $_POST['released' . $version_index] ) )
      {
         $released = $_POST['released' . $version_index];
      }
      if ( isset( $_POST['obsolete' . $version_index] ) )
      {
         $obsolete = $_POST['obsolete' . $version_index];
      }

      if ( !is_null( $versions ) )
      {
         $new_version = $versions[$version_index];
         $version->version = trim( $new_version );
      }

      if ( is_null( $released ) )
      {
         $version->released = false;
      }
      else if ( $released == 'on' )
      {
         $version->released = true;
      }

      if ( is_null( $obsolete ) )
      {
         $version->obsolete = false;
      }
      else if ( $obsolete == 'on' )
      {
         $version->obsolete = true;
      }

      if ( !is_null( $date_order ) )
      {
         $new_date_order = $date_order[$version_index];
         $version->date_order = $new_date_order;
      }

      if ( !is_null( $type ) )
      {
         $new_type = $type[$version_index];
         if ( strlen( $new_type ) > 0 )
         {
            $new_type_id = $specmanagement_database_api->get_type_id( $new_type );
            $specmanagement_database_api->update_version_associated_type( $project_id, $version_ids[$version_index], $new_type_id );
         }
         else
         {
            $specmanagement_database_api->update_version_associated_type( $project_id, $version_ids[$version_index], 9999 );
         }
      }

      if ( !is_null( $description ) )
      {
         $new_description = $description[$version_index];
         $version->description = $new_description;
      }

      version_update( $version );

      event_signal( 'EVENT_MANAGE_VERSION_UPDATE', array( $version_id ) );
   }
}

form_security_purge( 'plugin_SpecManagement_manage_versions_update' );

print_successful_redirect( plugin_page( 'manage_versions', true ) );