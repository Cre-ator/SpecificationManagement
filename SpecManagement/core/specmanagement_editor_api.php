<?php

class specmanagement_editor_api
{
   /**
    * @param $bug_id
    * @param $version_date
    * @return null
    */
   public function get_bug_additionalinformation ( $bug_id, $version_date )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $additional_information_value = null;
      $value_type = 3;
      $additional_information_value = $specmanagement_database_api->calculate_last_text_fields ( $bug_id, $version_date, $value_type );
      return $additional_information_value;
   }

   /**
    * @param $bug_id
    * @param $version_date
    * @return array
    */
   public function get_bug_stepstoreproduce ( $bug_id, $version_date )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $steps_to_reproduce_value = null;
      $value_type = 2;
      $steps_to_reproduce_value = $specmanagement_database_api->calculate_last_text_fields ( $bug_id, $version_date, $value_type );
      return $steps_to_reproduce_value;
   }

   /**
    * @param $bug_id
    * @param $version_date
    * @return array
    */
   function get_bug_description ( $bug_id, $version_date )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $description_value = null;
      $bug = bug_get ( $bug_id );
      $value_type = 1;
      $description_value = $specmanagement_database_api->calculate_last_text_fields ( $bug_id, $version_date, $value_type );
      if ( strlen ( $description_value ) == 0 )
      {
         $description_value = $bug->description;
      }
      return $description_value;
   }

   /**
    * @param $bug_id
    * @param $version_date
    * @return string
    */
   public function get_bug_summary ( $bug_id, $version_date )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $int_filter_string = 'summary';
      $summary_value = $specmanagement_database_api->calculate_last_change ( $bug_id, $version_date, $int_filter_string );
      if ( strlen ( $summary_value ) == 0 )
      {
         $summary_value = bug_get_field ( $bug_id, 'summary' );
         return $summary_value;
      }
      return $summary_value;
   }

   /**
    * Gets and returns relevant data for a given bug and date
    *
    * @param $bug_id
    * @param $version_date
    * @return array
    */
   public function calculate_bug_data ( $bug_id, $version_date )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      /** Initialize bug data array */
      $bug_data = array ();
      /** ID */
      $bug_data[ 0 ] = $bug_id;
      /** Summary */
      $bug_data[ 1 ] = $this->get_bug_summary ( $bug_id, $version_date );
      /** Description */
      $bug_data[ 2 ] = $this->get_bug_description ( $bug_id, $version_date );
      /** Steps to reproduce */
      $bug_data[ 3 ] = $this->get_bug_stepstoreproduce ( $bug_id, $version_date );
      /** Additional information */
      $bug_data[ 4 ] = $this->get_bug_additionalinformation ( $bug_id, $version_date );
      /** Attached files */
      $bug_data[ 5 ] = bug_get_attachments ( $bug_id );
      /** Notes */
      $bug_data[ 6 ] = $specmanagement_database_api->calculate_last_bugnotes ( $bug_id, $version_date );
      /** planned duration for each bug */
      $bug_data[ 7 ] = $specmanagement_database_api->get_ptime_row ( $bug_id )[ 2 ];

      return $bug_data;
   }

   /**
    * Calculate deepest path of all workpackages
    *
    * @param $work_packages
    * @return int
    */
   public function calculate_directory_depth ( $work_packages )
   {
      $directory_depth = 0;
      foreach ( $work_packages as $work_package )
      {
         $chapters = explode ( '/', $work_package );
         $chapter_depth = count ( $chapters );
         if ( $chapter_depth > $directory_depth )
         {
            $directory_depth = $chapter_depth;
         }
      }
      return $directory_depth;
   }

   /**
    * Generates an array with chapter counters for each depth-level
    *
    * @param $directory_depth
    * @return array
    */
   public function prepare_chapter_counter ( $directory_depth )
   {
      $chapter_counter_array = array ();
      for ( $depth_index = 0; $depth_index < $directory_depth; $depth_index++ )
      {
         $chapter_counter_array[ $depth_index ] = 1;
      }
      return $chapter_counter_array;
   }

   /**
    * @param $chapter_counter_array
    * @return mixed
    */
   public function reset_chapter_counter ( $chapter_counter_array )
   {
      $level_amount = count ( $chapter_counter_array );
      for ( $level_index = 1; $level_index < $level_amount; $level_index++ )
      {
         $chapter_counter_array[ $level_index ] = 1;
      }
      return $chapter_counter_array;
   }

   /**
    * @param $chapter_counter_array
    * @param $chapter_depth
    * @param $last_chapter_depth
    * @return array
    */
   public function generate_chapter_prefix ( $chapter_counter_array, $chapter_depth, $last_chapter_depth )
   {
      $chapter_prefix = '';
      $changed = false;
      for ( $depth_index = 0; $depth_index < $chapter_depth; $depth_index++ )
      {
         if ( ( $chapter_depth > 0 )
            && ( $chapter_depth <= $last_chapter_depth )
            && ( $changed == false )
         )
         {
            $chapter_counter_array[ $chapter_depth - 1 ]++;
            $changed = true;
         }
         $chapter_prefix .= $chapter_counter_array[ $depth_index ];
         if ( $depth_index < $chapter_depth - 1 )
         {
            $chapter_prefix .= '.';
         }
      }
      return array ( $chapter_counter_array, $chapter_prefix );
   }

   /**
    * @param $chapters
    * @param $chapter_depth
    * @return string
    */
   public function generate_chapter_suffix ( $chapters, $chapter_depth )
   {
      $chapter_suffix = '';
      if ( $chapter_depth > 0 )
      {
         $chapter_suffix = ' ' . $chapters[ $chapter_depth - 1 ];
      }

      return $chapter_suffix;
   }

   /**
    * @param $print_flag
    * @param $chapter_suffix
    * @param $chapter_prefix
    * @param $bug_counter
    * @param $bug_data
    * @param $is_chapter
    */
   public function print_chapter_directory ( $print_flag, $chapter_suffix, $chapter_prefix, $bug_counter, $bug_data, $is_chapter )
   {
      if ( $is_chapter )
      {
         echo '<tr><td class="directory_chapter_title">';
         if ( !$print_flag )
         {
            echo '<a href="#' . $chapter_suffix . '">';
            echo $chapter_prefix . ' ' . $chapter_suffix . '<br/>';
            echo '</a>';
         }
         else
         {
            echo $chapter_prefix . ' ' . $chapter_suffix . '<br/>';
         }
      }
      else
      {
         echo '<tr><td class="directory_chapter_item">';
         if ( !$print_flag )
         {
            echo '<a href="#' . string_display ( $bug_data[ 1 ] ) . '">';
            echo '/F' . $chapter_prefix . '-' . $bug_counter . '/ ' . string_display ( $bug_data[ 1 ] );
            echo '</a>';
            echo '&nbsp(';
            print_bug_link ( bug_format_id ( $bug_data[ 0 ] ) );
            echo ')';
         }
         else
         {
            echo '/F' . $chapter_prefix . '-' . $bug_counter . '/ ' . string_display ( $bug_data[ 1 ] );
            echo '&nbsp(' . bug_format_id ( $bug_data[ 0 ] ) . ')';
         }
      }
      echo '</td></tr>';
   }


   /**
    * Gets the managers of the current selected project
    *
    * @param $version_id
    * @return string
    */
   public function calculate_person_in_charge ( $version_id )
   {
      $person_in_charge = '';
      $project_id = helper_get_current_project ();
      if ( $project_id == 0 )
      {
         $project_id = version_get_field ( $version_id, 'project_id' );
      }
      $project_related_users = project_get_local_user_rows ( $project_id );
      $count = 0;
      foreach ( $project_related_users as $project_related_user )
      {
         if ( $project_related_user[ 'project_id' ] == $project_id
            && $project_related_user[ 'access_level' ] == 70
            && user_is_enabled ( $project_related_user[ 'user_id' ] )
         )
         {
            if ( $count > 0 )
            {
               $person_in_charge .= ', ';
            }
            $person_in_charge .= user_get_realname ( $project_related_user[ 'user_id' ] );
            $count++;
         }
      }
      return $person_in_charge;
   }

   /**
    * Gets the sum of each planned time for a bunch of issues
    *
    * @param $allRelevantBugs
    * @return array
    */
   public function calculate_pt_doc_progress ( $allRelevantBugs )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $sum_pt = array ();
      $sum_pt_all = 0;
      $sum_pt_bug = 0;
      foreach ( $allRelevantBugs as $bug_id )
      {
         $ptime_row = $specmanagement_database_api->get_ptime_row ( $bug_id );
         if ( !is_null ( $ptime_row[ 2 ] ) || 0 != $ptime_row[ 2 ] )
         {
            $sum_pt_all += $ptime_row[ 2 ];
            if ( bug_get_field ( $bug_id, 'status' ) == PLUGINS_SPECMANAGEMENT_STAT_RESOLVED
               || bug_get_field ( $bug_id, 'status' ) == PLUGINS_SPECMANAGEMENT_STAT_CLOSED
            )
            {
               $sum_pt_bug += $ptime_row[ 2 ];
            }
         }
      }
      array_push ( $sum_pt, $sum_pt_all );
      array_push ( $sum_pt, $sum_pt_bug );

      return $sum_pt;
   }

   /**
    * Prints a specific information of a bug
    *
    * @param $string
    */
   public function print_bug_infos ( $string )
   {
      if ( !is_null ( $string ) )
      {
         echo '<tr>';
         echo '<td />';
         echo '<td colspan="2">' . $string . '</td>';
         echo '</tr>';
      }
   }


   /**
    * Prints the information that there are notes
    *
    * @param $bugnote_count_value
    */
   public function print_bugnote_note ( $bugnote_count_value )
   {
      echo '<tr>';
      echo '<td />';
      echo '<td class="infohead" colspan="2">' . plugin_lang_get ( 'editor_bug_notes_note' ) . ' (' . $bugnote_count_value . ')</td>';
      echo '</tr>';
   }

   /**
    * Prints bug-specific attachments
    *
    * @param $bug_id
    */
   public function print_bug_attachments ( $bug_id )
   {
      $specmanagement_print_api = new specmanagement_print_api();
      $attachment_count = file_bug_attachment_count ( $bug_id );
      echo '<tr>';
      echo '<td />';
      echo '<td class="infohead" colspan="2">' . plugin_lang_get ( 'editor_bug_attachments' ) . ' (' . $attachment_count . ')</td>';
      echo '</tr>';

      echo '<tr id="attachments">';
      echo '<td />';
      echo '<td class="bug-attachments" colspan="2">';
      $specmanagement_print_api->print_bug_attachments_list ( $bug_id );
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Prints a bug into the document
    *
    * @param $chapter_index
    * @param $sub_chapter_index
    * @param $bug_data
    * @param $option_show_duration
    * @param $print_flag
    */
   public function print_bug ( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag )
   {
      $this->print_bug_head ( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
      $this->print_bug_infos ( string_display_links ( trim ( $bug_data[ 2 ] ) ) );
      $this->print_bug_infos ( string_display_links ( trim ( $bug_data[ 3 ] ) ) );
      $this->print_bug_infos ( string_display_links ( trim ( $bug_data[ 4 ] ) ) );
      if ( !empty( $bug_data[ 5 ] ) )
      {
         $this->print_bug_attachments ( $bug_data[ 0 ] );
      }
      if ( !is_null ( $bug_data[ 6 ] ) && $bug_data[ 6 ] != 0 )
      {
         $this->print_bugnote_note ( $bug_data[ 6 ] );
      }
   }

   /**
    * @param $chapter_index
    * @param $sub_chapter_index
    * @param $bug_data
    * @param $option_show_duration
    * @param $print_flag
    */
   public function print_bug_head ( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag )
   {
      echo '<tr><td class="content_chapter_item_spacer" colspan="3"></td></tr>';
      echo '<tr>';
      echo '<td class="content_chapter_item_number" id="' . string_display ( $bug_data[ 1 ] ) . '">/F' . $chapter_index . '-' . $sub_chapter_index . '/</td>';
      echo '<td class="content_chapter_item">' . string_display ( $bug_data[ 1 ] ) . ' (';
      if ( !$print_flag )
      {
         print_bug_link ( $bug_data[ 0 ], true );
      }
      else
      {
         echo bug_format_id ( $bug_data[ 0 ] );
      }
      echo ')';
      echo '</td>';
      echo '<td class="content_chapter_item_duration">';
      if ( $option_show_duration == '1' && !( $bug_data[ 7 ] == 0 || is_null ( $bug_data[ 7 ] ) ) )
      {
         echo plugin_lang_get ( 'editor_bug_duration' ) . ': ' . $bug_data[ 7 ] . ' ' . plugin_lang_get ( 'editor_duration_unit' );
      }
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Prints the header element of a document
    *
    * @param $type_string
    * @param $version_id
    * @param $allRelevantBugs
    * @param $print_flag
    */
   public function print_document_head ( $type_string, $version_id, $allRelevantBugs, $print_flag )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $project_id = helper_get_current_project ();
      $parent_project_id = $specmanagement_database_api->get_main_project_by_hierarchy ( $project_id );
      $versions = version_get_all_rows_with_subs ( $project_id, null, null );
      $act_version = version_get ( $version_id );
      $head_project_id = $project_id;
      if ( $parent_project_id == 0 )
      {
         $parent_project_id = version_get_field ( $version_id, 'project_id' );
         $head_project_id = version_get_field ( $version_id, 'project_id' );
      }
      $this->print_editor_table_head ( $print_flag );
      $this->print_editor_table_title ( $type_string, $version_id, $print_flag );
      $this->print_doc_head_row ( 'head_version', version_get_field ( $version_id, 'version' ) );
      $this->print_doc_head_row ( 'head_customer', project_get_name ( $parent_project_id ) );
      $this->print_doc_head_row ( 'head_project', project_get_name ( $head_project_id ) );
      $this->print_doc_head_row ( 'head_date', date ( 'd\.m\.Y' ) );
      $this->print_doc_head_row ( 'head_person_in_charge', $this->calculate_person_in_charge ( $version_id ) );
      if ( !is_null ( $allRelevantBugs ) )
      {
         $process = $this->get_process ( $allRelevantBugs );
         if ( is_array ( $process ) )
         {
            $sum_pt_all = $process[ 0 ];
            $sum_pt_bug = $process[ 1 ];
            $pt_process = 0;
            if ( $sum_pt_all != 0 )
            {
               $pt_process = round ( ( $sum_pt_bug * 100 / $sum_pt_all ), 2 );
            }
            $process_string
               = '<span class="bar" style="width: ' . $pt_process . '%;">'
               . $sum_pt_bug . '/' . $sum_pt_all
               . ' '
               . plugin_lang_get ( 'editor_duration_unit' )
               . ' (' . $pt_process . ' %)'
               . '</span>';
         }
         else
         {
            $process_string = '<span class="bar" style="width: ' . $process . '%;">' . round ( $process, 2 ) . ' %</span>';
         }
         //$this->print_doc_head_row ( 'head_process', $this->create_process_bar ( $process_string ) );
      }
      if ( !$print_flag )
      {
         $this->print_doc_head_versions ( $versions, $act_version );
      }
      echo '</table>';
      echo '<br />';
   }

   /**
    * @param $type_string
    * @param $version_id
    * @param $print_flag
    */
   public function print_editor_table_title ( $type_string, $version_id, $print_flag )
   {
      echo '<tr>';
      echo '<td class="document_title_label">' . plugin_lang_get ( 'head_title' ) . '</td>';
      echo '<td class="document_title" colspan="2">' . $type_string . ' - ' . version_get_field ( $version_id, 'version' ) . '</td>';
      if ( !$print_flag )
      {
         echo '<td class="document_title_controls">';
         echo '<form action="' . plugin_page ( 'editor' ) . '" method="post">';
         echo '<span class="input">';
         echo '<input type="hidden" name="version_id" value="' . $version_id . '" />';
         echo '<input type="submit" name="print" class="button" value="' . plugin_lang_get ( 'editor_printhtml' ) . '"/>';
         echo '</span>';
         echo '</form>';
         //echo '<form method="post" name="form_set_source" action="' . plugin_page ( 'editorpdf' ) . '">';
         //echo '<span class="input">';
         //echo '<input type="hidden" name="version_id" value="' . $version_id . '" />';
         //echo '&nbsp<input type="submit" name="printtopdf" class="button" value="' . plugin_lang_get ( 'editor_printpdf' ) . '"/>';
         //echo '</span>';
         //echo '</form>';
         echo '</td>';
      }
      echo '</tr>';
   }

   /**
    * Prints a new chapter title element in a document
    *
    * @param $chapter_index
    * @param $option_show_duration
    * @param $duration
    */
   public function print_simple_chapter_title ( $chapter_index, $option_show_duration, $duration )
   {
      echo '<tr><td colspan="3"><hr width="100%" align="center" /></td></tr>';
      echo '<tr>';
      echo '<td class="form-title">' . $chapter_index . '</td>';
      echo '<td class="form-title">' . plugin_lang_get ( 'editor_no_workpackage' );
      echo '</td>';
      echo '<td class="duration_title">';
      if ( $option_show_duration == '1' && !( $duration == 0 || is_null ( $duration ) ) )
      {
         echo '[' . plugin_lang_get ( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get ( 'editor_duration_unit' ) . ']';
      }
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Prints a new chapter title element in a document
    *
    * @param $chapter_index
    * @param $work_package
    * @param $option_show_duration
    * @param $duration
    */
   public function print_chapter_document ( $chapter_index, $work_package, $option_show_duration, $duration )
   {
      echo '<tr><td class="content_chapter_title_spacer" colspan="3"></td></tr>';
      echo '<tr>';
      echo '<td class="content_chapter_title_number" id="' . $work_package . '">' . $chapter_index . '</td>';
      echo '<td class="content_chapter_title">' . $work_package;
      echo '</td>';
      echo '<td class="content_chapter_title_duration">';
      if ( $option_show_duration == '1' && !( $duration == 0 || is_null ( $duration ) ) )
      {
         echo '[' . plugin_lang_get ( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get ( 'editor_duration_unit' ) . ']';
      }
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Prints a row into the document head
    *
    * @param $lang_string
    * @param $col_data
    */
   public function print_doc_head_row ( $lang_string, $col_data )
   {
      echo '<tr>';
      echo '<td class="document_header_label">' . plugin_lang_get ( $lang_string ) . '</td>';
      echo '<td class="document_header" colspan="3">' . $col_data . '</td>';
      echo '</tr>';
   }

   /**
    * @param $print_flag
    */
   public function print_editor_table_head ( $print_flag )
   {
      if ( !$print_flag )
      {
         echo '<table class="editor">';
      }
      else
      {
         echo '<table class="editorprint">';
      }
   }

   /**
    * Prints all available versions into the document head
    *
    * @param $versions
    * @param $act_version
    */
   public function print_doc_head_versions ( $versions, $act_version )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      foreach ( $versions as $version )
      {
         $type_string = $specmanagement_database_api->get_type_string ( $specmanagement_database_api->get_type_by_version ( $version[ 'id' ] ) );
         if ( strlen ( $type_string ) > 0 )
         {
            $same_version = $act_version->id == $version[ 'id' ];
            echo '<tr>';
            $this->print_doc_head_version_col ( $same_version, date_is_null ( $version[ 'date_order' ] ) ? '' : string_attribute ( date ( config_get ( 'calendar_date_format' ), $version[ 'date_order' ] ) ) );
            $this->print_doc_head_version_col ( $same_version, version_full_name ( $version[ 'id' ] ) );
            if ( $same_version )
            {
               $this->print_doc_head_version_col ( $same_version, '' );
            }
            else
            {
               $change_button_string = '<form method="post" name="form_set_source" action="' . plugin_page ( 'changes' ) . '">'
                  . '<input type="hidden" name="version_other" value="' . $version[ 'id' ] . '" />'
                  . '<input type="hidden" name="version_my" value="' . $act_version->id . '" />'
                  . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get ( 'head_changes' ) . '"/>'
                  . '</form>';
               $this->print_doc_head_version_col ( $same_version, $change_button_string );
            }
            $show_button_string = '<form method="post" name="form_set_source" action="' . plugin_page ( 'editor' ) . '">'
               . '<input type="hidden" name="version_id" value="' . $version[ 'id' ] . '" />'
               . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get ( 'head_view' ) . '"/>'
               . '</form>';
            $this->print_doc_head_version_col ( $same_version, $show_button_string );
            echo '</tr>';
         }
      }
   }

   /**
    * Prints a column for a version in the document head area
    *
    * @param $same_version
    * @param $data
    */
   public function print_doc_head_version_col ( $same_version, $data )
   {
      if ( $same_version )
      {
         echo '<td class="selected">';
      }
      else
      {
         echo '<td>';
      }
      echo $data;
      echo '</td>';
   }

   /**
    * @param $allRelevantBugs
    * @return string
    */
   public function get_process ( $allRelevantBugs )
   {
      $specmanagement_print_api = new specmanagement_print_api();
      $status_flag = $this->check_status_flag ( $allRelevantBugs );
      if ( $status_flag )
      {
         return round ( $specmanagement_print_api->calculate_status_doc_progress ( $allRelevantBugs ), 2 );;
      }
      else
      {
         return $this->calculate_pt_doc_progress ( $allRelevantBugs );
      }
   }

   /**
    * @param $process
    * @return string
    */
   public function create_process_bar ( $process )
   {
      return '<div class="progress400">' . $process . '</div>';
   }


   /**
    * @param $allRelevantBugs
    * @return bool
    */
   public function check_status_flag ( $allRelevantBugs )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $status_flag = false;
      foreach ( $allRelevantBugs as $bug_id )
      {
         $ptime_row = $specmanagement_database_api->get_ptime_row ( $bug_id );
         if ( is_null ( $ptime_row[ 2 ] ) || 0 == $ptime_row[ 2 ] )
         {
            $status_flag = true;
            break;
         }
      }
      return $status_flag;
   }

   /**
    * Prints the expenses overview area
    *
    * @param $work_packages
    * @param $p_version_id
    * @param $print_flag
    * @param $no_workpackage_bug_ids
    */
   public function print_expenses_overview ( $work_packages, $p_version_id, $print_flag, $no_workpackage_bug_ids )
   {
      echo '<br />';
      $this->print_editor_table_head ( $print_flag );
      $this->print_expenses_overview_head ();
      $this->print_expenses_overview_body ( $work_packages, $p_version_id, $no_workpackage_bug_ids );
      echo '</table>';
   }

   /**
    * Prints the head of the expenses overview area
    */
   public function print_expenses_overview_head ()
   {
      echo '<thead>';
      echo '<tr>';
      echo '<td class="document_eta_title" colspan="2">' . plugin_lang_get ( 'editor_expenses_overview' ) . '</td>';
      echo '</tr>';

      echo '<tr class="row-category">';
      echo '<th class="document_eta_label">' . plugin_lang_get ( 'bug_view_specification_wpg' ) . '</th>';
      echo '<th class="document_eta_duration">' . plugin_lang_get ( 'bug_view_planned_time' ) . ' (' . plugin_lang_get ( 'editor_duration_unit' ) . ')</th>';
      echo '</tr>';
      echo '</thead>';
   }

   /**
    * @param $work_packages
    * @param $p_version_id
    * @param $no_workpackage_bug_ids
    */
   public function print_expenses_overview_body ( $work_packages, $p_version_id, $no_workpackage_bug_ids )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $document_duration = 0;

      echo '<tbody>';
      if ( $work_packages != null )
      {
         $document_duration = 0;
         foreach ( $work_packages as $work_package )
         {
            /** go to next record, if work package is empty */
            if ( strlen ( $work_package ) == 0 )
            {
               continue;
            }
            $duration = $specmanagement_database_api->get_workpackage_duration ( $p_version_id, $work_package );
            if ( is_null ( $duration ) )
            {
               $duration = 0;
            }
            $document_duration += $duration;
            echo '<tr>';
            echo '<td>' . $work_package . '</td>';
            echo '<td class="duration">' . $duration . '</td>';
            echo '</tr>';
         }
      }

      if ( count ( $no_workpackage_bug_ids ) > 0 )
      {
         $sum_no_work_package_bug_duration = 0;

         foreach ( $no_workpackage_bug_ids as $no_workpackage_bug_id )
         {
            $no_work_package_bug_duration = $specmanagement_database_api->get_bug_duration ( $no_workpackage_bug_id );
            if ( !is_null ( $no_work_package_bug_duration ) )
            {
               $sum_no_work_package_bug_duration += $no_work_package_bug_duration;
            }
         }

         $document_duration += $sum_no_work_package_bug_duration;
         echo '<tr>';
         echo '<td>' . plugin_lang_get ( 'editor_no_workpackage' ) . '</td>';
         echo '<td class="duration">' . $sum_no_work_package_bug_duration . '</td>';
         echo '</tr>';
      }
      echo '<tr>';
      echo '<td colspan="2"><hr width="100%" align="center" /></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo plugin_lang_get ( 'editor_expenses_overview_sum' ) . ':';
      echo '</td>';
      echo '<td class="duration">' . $document_duration . '</td>';
      echo '</tr>';
      echo '</tbody>';
   }

   /**
    * @param $p_version_id
    * @param $work_packages
    * @param $no_work_package_bug_ids
    * @param $option_show_duration
    * @param $print_flag
    */
   public function print_directory ( $p_version_id, $work_packages, $no_work_package_bug_ids, $option_show_duration, $print_flag )
   {
      echo '<br />';
      $this->print_editor_table_head ( $print_flag );
      $this->print_directory_head ();
      echo '<tbody>';
      $this->generate_content ( $p_version_id, $work_packages, $no_work_package_bug_ids, $option_show_duration, false, $print_flag );
      echo '</tbody>';
      echo '</table>';
      echo '<br />';
   }

   /**
    * Print table head from directory
    */
   public function print_directory_head ()
   {
      echo '<thead>';
      echo '<tr>';
      echo '<td class="document_directory_title" colspan="2">' . plugin_lang_get ( 'editor_directory' ) . '</td>';
      echo '</tr>';
      echo '</thead>';
   }

   /**
    * Print table body from directory
    *
    * @param $p_version_id
    * @param $work_packages
    * @param $no_work_package_bug_ids
    * @param $option_show_duration
    * @param $detail_flag
    * @param $print_flag
    */
   public function generate_content ( $p_version_id, $work_packages, $no_work_package_bug_ids, $option_show_duration, $detail_flag, $print_flag )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $directory_depth = $this->calculate_directory_depth ( $work_packages );
      $chapter_counter_array = $this->prepare_chapter_counter ( $directory_depth );
      $last_chapter_depth = 0;
      $version_id = $_POST[ 'version_id' ];
      $version = version_get ( $version_id );
      $version_date = $version->date_order;

      /** Iterate through defined work packages */
      if ( !is_null ( $work_packages ) )
      {
         foreach ( $work_packages as $work_package )
         {
            if ( strlen ( $work_package ) > 0 )
            {
               $work_package_spec_bug_ids = $specmanagement_database_api->get_workpackage_spec_bugs ( $p_version_id, $work_package );
               $chapters = explode ( '/', $work_package );
               $chapter_depth = count ( $chapters );
               if ( $chapter_depth == 1 )
               {
                  $this->reset_chapter_counter ( $chapter_counter_array );
               }

               $chapter_prefix_data = $this->generate_chapter_prefix ( $chapter_counter_array, $chapter_depth, $last_chapter_depth );
               $chapter_counter_array = $chapter_prefix_data[ 0 ];
               $chapter_prefix = $chapter_prefix_data[ 1 ];
               $chapter_suffix = $this->generate_chapter_suffix ( $chapters, $chapter_depth );

               $this->process_chapter ( $p_version_id, $work_package, $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag );
               $this->process_content ( $work_package_spec_bug_ids, $version_date, $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag );
               $last_chapter_depth = $chapter_depth;
            }
         }
      }

      /** Iterate through issues without defined work package */
      $chapter_prefix = $chapter_counter_array[ 0 ] + 1;
      $chapter_suffix = plugin_lang_get ( 'editor_no_workpackage' );
      if ( count ( $no_work_package_bug_ids ) > 0 )
      {
         $this->process_chapter ( $p_version_id, '', $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag );
         $this->process_content ( $no_work_package_bug_ids, $version_date, $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag );
      }
   }

   /**
    * @param $p_version_id
    * @param $work_package
    * @param $chapter_prefix
    * @param $chapter_suffix
    * @param $option_show_duration
    * @param $detail_flag
    * @param $print_flag
    */
   public function process_chapter ( $p_version_id, $work_package, $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag )
   {
      $specmanagement_database_api = new specmanagement_database_api();
      if ( $detail_flag )
      {
         $chapter_duration = $specmanagement_database_api->get_workpackage_duration ( $p_version_id, $work_package );
         $this->print_chapter_document ( $chapter_prefix, $chapter_suffix, $option_show_duration, $chapter_duration );
      }
      else
      {
         $this->print_chapter_directory ( $print_flag, $chapter_suffix, $chapter_prefix, null, null, true );
      }
   }

   /**
    * @param $bug_ids
    * @param $version_date
    * @param $chapter_prefix
    * @param $chapter_suffix
    * @param $option_show_duration
    * @param $detail_flag
    * @param $print_flag
    */
   public function process_content ( $bug_ids, $version_date, $chapter_prefix, $chapter_suffix, $option_show_duration, $detail_flag, $print_flag )
   {
      $bug_counter = 10;
      foreach ( $bug_ids as $bug_id )
      {
         if ( bug_exists ( $bug_id ) )
         {
            $bug_data = $this->calculate_bug_data ( $bug_id, $version_date );
            if ( $detail_flag )
            {
               $this->print_bug ( $chapter_prefix, $bug_counter, $bug_data, $option_show_duration, $print_flag );
            }
            else
            {
               $this->print_chapter_directory ( $print_flag, $chapter_suffix, $chapter_prefix, $bug_counter, $bug_data, false );
            }
            $bug_counter += 10;
         }
      }
   }
}