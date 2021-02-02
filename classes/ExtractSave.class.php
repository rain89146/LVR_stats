<?php

    /**
     * The ExtractSave class extends Ultil class
     * The purpose of this class is mainly about extracting data from tables and store into the statistic_log table
     * 
     * the functions can be run weekly or monthly
     */
    class ExtractSave extends Ultil{

        //  Get the city name list
        protected function get_city_list()
        {
            $VIEW = new Viewlisting();
            return $VIEW -> get_city_name_list();
        }

        //  Get the zip list
        protected function get_zip_list()
        {
            $VIEW = new Viewlisting();
            return $VIEW -> get_postal_code_list();
        }

        //  Clear the table data after extracting success
        protected function destroyTable(string $table_name)
        {
            $CONTROL = new Controllisting();
            $CONTROL->DeleteTable($table_name);
        }

        //  Return weekly dom list
        protected function return_weekly_dom_list()
        {
            //  DOM segment param
                $weekly_res =       $this -> generate_number_pair(0, 16, 7);
                $weekly_pair =      $this -> generate_dom_list_pair($weekly_res);
                $weekly_dom_list =  $this -> generate_dom_list($weekly_res);
            
            return array(
                'pair' =>   $weekly_pair,
                'list' =>   $weekly_dom_list
            );
        }

        //  Return the date range
        protected function return_date_range(string $QueryDate, string $purpose)
        {
            if (strpos($purpose, 'weekly') !== false) : 
                return  $this->getDateRange($QueryDate, 'wholeWeek_h', 1);  endif;
            if (strpos($purpose, 'monthly') !== false) : 
                return  $this->getDateRange($QueryDate, 'wholeMonth_m', 1); endif;
        }

        //  Return today
        protected function return_today()
        {
            $AHORA = new DateTime();
            return $AHORA->format('Y-m-d');
        }

        /**
         * Unify the date of insertation:: weekly
         * @param date      The date of query
         */
        protected function union_insert_date_weekly($date)
        {
            return $this->get_sunday($date);
        }

        /**
         * Unify the date of insertation:: monthly
         * @param date      The date of query
         */
        protected function union_insert_date_monthly($date)
        {
            return $this->get_last_day_month($date);
        }


        /**
         * Extract table name
         * @param table_name    name of the table
         */
        protected function extract_table_name(string $table_name) {
            if (strpos($table_name, 'sold') !== false) : 
                return  'sold';  endif;
            if (strpos($table_name, 'active') !== false) : 
                return  'active'; endif;
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Save statistic to database
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Save query result
         * @param dataset   The result of populated data
         * @param options   The options needed to find the existing record
         */
        protected function SaveQueryResult(array $dataset, array $options)
        {
            $ERROR = 0;

            //  If has date range
                if(array_key_exists('DateRange', $options)){

                    // Append the date range to options
                        $DateRange = $options['DateRange'];
                        foreach($DateRange as $date):

                            $options['DateBegin'] = $date[0];
                            $options['DateEnd'] =   $date[1];

                            //  copy and remove DataRange
                                $n_options = $options;
                                unset($n_options['DateRange']);

                            //  Save record
                                $ERROR += $this->save_statistic_record($dataset, $n_options);

                        endforeach;

                }else{

                    //  Save record
                        $ERROR += $this->save_statistic_record($dataset, $options);
                }

            return ($ERROR === 0) ? true : false;
        }

        /**
         * Save the statistic record
         * @param dataset   The result of populated data
         * @param options   The options needed to find the existing record
         */
        protected function save_statistic_record(array $dataset, array $options)
        {
            $CONTROL =  new Controllisting();
            $VIEW =     new Viewlisting();
            $ERROR =    0;

            //  Get the query id, save the record if query id was found
                $QueryId = $VIEW->retrieveQueryId($options, 'statistic_log');
                if($QueryId === ''):

                    //  Save into statistic vault
                        foreach ($dataset as $k => $set):
                            
                            $QueryId = $set['QueryId'];
                            foreach ($set as $key => $value):

                                $res = $CONTROL->saveStatisticResult($QueryId, 'statistic_log', $key, $value);
                                if($res == false): $ERROR ++; endif;

                            endforeach;
                        endforeach;
                endif;

            return $ERROR;
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Extracting data
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Save sold listing statitic
         * @param QueryDate         Date of query
         * @param purpose           Purpose of this save e.g. sold_listing_summary_weekly, sold_listing_summary_monthly
         * @param field             Option field, e.g. City, PostalCode
         * @param value             Value of the field name
         */
        protected function save_solid_listing_statistic( array $date_range, string $purpose, string $field = NULL, string $value = NULL )
        {
                $PROCESS =      new Process();
                $QueryDate =    $date_range[0][1];

            //  Use the setting to query statistic result
                $setting = (isset($field)) 
                ? array(
                    $field =>           $value,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryPurpose' =>   $purpose
                );

            //  use filter to look check for the item if exists
                $filter = (isset($field))
                ? array(
                    $field =>           $value,
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                )
                : array(
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                );

            //  Returns associate array of necessary data
                $result = $PROCESS -> PopulateComparesionResult( $QueryDate, 'seriesBasicReport', function($dates, $option) {
                    $VIEW = new Viewlisting();
                    return $VIEW->generate_general_statistic_by_month($dates, 'sold_listing', 'ClosePrice', $option);
                }, $setting, $date_range );

            //  Save the result
                $saved = $this->SaveQueryResult( $result, $filter );
        }

        /**
         * Save DOM listing statistic
         * @param QueryDate         Date of query
         * @param purpose           Purpose of this save e.g. days_on_market_summary_monthly, days_on_market_weekly
         * @param field             Option field, e.g. City, PostalCode
         * @param value             Value of the field name
         */
        protected function save_dom_listing_statistic( array $date_range, string $purpose, string $field = NULL, string $value = NULL )
        {
                $PROCESS =      new Process();
                $QueryDate =    $date_range[0][1];

            //  Use the setting to query statistic result
                $setting = (isset($field)) 
                ? array(
                    $field =>           $value,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryPurpose' =>   $purpose
                );

            //  use filter to look check for the item if exists
                $filter = (isset($field))
                ? array(
                    $field =>           $value,
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                )
                : array(
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                );

            //  Returns associate array of necessary data
                $result = $PROCESS -> PopulateComparesionResult( $QueryDate, 'DomBasicReport', function ($dates, $info) {   
                    $VIEW = new Viewlisting();
                    return $VIEW->generate_dom_report_periodic($dates, 'sold_listing', 'ClosePrice', $info); 
                }, $setting, $date_range );

            //  Save query result
                $saved = $this->SaveQueryResult( $result, $filter );
        }

        /**
         * Save custom DOM listing statistic for custom list
         * @param QueryDate         Date of query
         * @param purpose           Purpose of this save e.g. days_on_market_custom_7_days_weekly, days_on_market_custom_7_days_monthly
         * @param DOM_list          DOM segment
         * @param DOM_list_string   DOM segment listing in string
         * @param field             Option field, e.g. City, PostalCode
         * @param value             Value of the field name
         */
        protected function save_custom_dom_listing_statistic( array $date_range, string $purpose, array $DOM_list, array $DOM_list_string, string $field = NULL, string $value = NULL)
        {
                $PROCESS =      new Process();
                $QueryDate =    $date_range[0][1];

            //  Use the setting to query statistic result
                $setting = (isset($field)) 
                ? array(
                    $field =>           $value,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryPurpose' =>   $purpose
                );

            //  use filter to look check for the item if exists
                $filter = (isset($field))
                ? array(
                    $field =>           $value,
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                )
                : array(
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose,
                    'DateRange' =>      $date_range
                );

            //  Returns associate array of necessary data
                $result = $PROCESS -> PopulateComparesionResult_custom_dom( $QueryDate, $DOM_list_string, $DOM_list, function ($dates, $info, $dom_list) {   
                    $VIEW = new Viewlisting();
                    return $VIEW->generate_dom_report_custom_periodic($dates, $dom_list, 'sold_listing', 'ClosePrice', $info); 
                }, $setting, $date_range );

            //  Save query result
                $saved = $this->SaveQueryResult( $result, $filter );
        }

        /**
         * Save active listing statistic
         * @param QueryDate         Date of query
         * @param purpose           Purpose of this save e.g. active_availability_city_monthly, active_availability_summary_weekly
         * @param field             Option field, e.g. City, PostalCode
         * @param value             Value of the field name
         */
        protected function save_active_listing_statistic( array $date_range, string $purpose, string $field = NULL, string $value = NULL)
        {   
            $PROCESS =      new Process();
            $QueryDate =    $date_range[0][1];

            //  Use the setting to query statistic result
                $setting = (isset($field)) 
                ? array(
                    $field =>           $value,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryPurpose' =>   $purpose
                );

            //  use filter to look check for the item if exists
                $filter = (isset($field))
                ? array(
                    $field =>           $value,
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose
                );

            //  Returns associate array of necessary data
                $result = $PROCESS -> PopulateComparesionResult( $QueryDate, 'basicReport', function($option) {
                    $VIEW = new Viewlisting();
                    return $VIEW->generate_active_listing_statistic('active_listing', 'ListPrice', $option);
                }, $setting );

            //  Save the result
                $saved = $this->SaveQueryResult( $result, $filter );
        }

    
        /**
         * Save active no offer listing statistic
         * @param QueryDate         Date of query
         * @param purpose           Purpose of this save e.g. active_availability_city_monthly, active_availability_summary_weekly
         * @param field             Option field, e.g. City, PostalCode
         * @param value             Value of the field name
         */
        protected function save_active_no_offer_listing_statistic( array $date_range, string $purpose, string $field = NULL, string $value = NULL)
        {
            $PROCESS =      new Process();
            $QueryDate =    $date_range[0][1];

            //  Use the setting to query statistic result
                $setting = (isset($field)) 
                ? array(
                    $field =>           $value,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryPurpose' =>   $purpose
                );

            //  use filter to look check for the item if exists
                $filter = (isset($field))
                ? array(
                    $field =>           $value,
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose
                )
                : array(
                    'QueryDate' =>      $QueryDate,
                    'QueryPurpose' =>   $purpose
                );

            //  Returns associate array of necessary data
                $result = $PROCESS -> PopulateComparesionResult( $QueryDate, 'basicReport', function($option){
                    $VIEW = new Viewlisting();
                    return $VIEW->generate_no_offer_statistic('active_listing', 'ListPrice', $option); 
                }, $setting );

            //  Save the result
                $saved = $this->SaveQueryResult( $result, $filter );
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Public calls
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Monthly extract data to statistic log
         * Run this every beginning of the month to query the pervious month data 
         * 
         * FOR INSTANCE: today is 1/11/2021, will complie result of 12/01/2020-12/31/2020; 
         */
        public function MONTHLY_RUN(string $table_name, array $date_range)
        {
            //  get table type
                $table_type =       $this->extract_table_name($table_name);

            //  Get the city and zip list
                $CITY_LIST =        $this->get_city_list();
                $ZIP_LIST =         $this->get_zip_list();

            //  Dom segment list
                $dom_segment =      $this->return_weekly_dom_list();
                $weekly_pair =      $dom_segment['pair'];
                $weekly_dom_list =  $dom_segment['list'];

            //  Summary
                if($table_type === 'active'):
                    $this->save_active_listing_statistic( $date_range, 'active_availability_summary_monthly');
                    $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_summary_monthly');
                    endif;
            
                if($table_type === 'sold'):
                    $this->save_solid_listing_statistic( $date_range, 'sold_listing_summary_monthly');
                    $this->save_dom_listing_statistic( $date_range, 'days_on_market_summary_monthly');
                    $this->save_custom_dom_listing_statistic( $date_range, 'days_on_market_custom_7_days_summary_monthly', $weekly_dom_list, $weekly_pair);
                    endif;

            //  City
                foreach ($CITY_LIST as $city) {
                    if($table_type === 'active'):
                        $this->save_active_listing_statistic( $date_range, 'active_availability_city_monthly', 'City', $city);
                        $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_city_monthly', 'City', $city);
                        endif;

                    if($table_type === 'sold'):
                        $this->save_solid_listing_statistic( $date_range, 'sold_listing_city_monthly', 'City', $city);
                        endif;
                }

            //  Zip
                foreach ($ZIP_LIST as $zip) {
                    if($table_type === 'active'):
                        $this->save_active_listing_statistic( $date_range, 'active_availability_zip_monthly', 'PostalCode', $zip);
                        $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_zip_monthly', 'PostalCode', $zip);
                        endif;
                    
                    if($table_type === 'sold'):
                        $this->save_solid_listing_statistic( $date_range, 'sold_listing_zip_monthly', 'PostalCode', $zip);
                        endif;
                }
        }

        /**
         * Weekly extract data to statistic log
         * Run this every beginning of the week to query the pervious week data
         *
         * FOR INSTANCE: today is 1/11/2021, will complie result of 01/04/2020-01/10/2020; 
         */
        public function WEEKLY_RUN(string $table_name, array $date_range)
        {
            //  get table type
                $table_type =       $this->extract_table_name($table_name);

            //  Get the city and zip list
                $CITY_LIST =        $this->get_city_list();
                $ZIP_LIST =         $this->get_zip_list();

            //  Dom segment list
                $dom_segment =      $this->return_weekly_dom_list();
                $weekly_pair =      $dom_segment['pair'];
                $weekly_dom_list =  $dom_segment['list'];

            //  Summary
            if($table_type === 'active'):
                $this->save_active_listing_statistic( $date_range, 'active_availability_summary_weekly');
                $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_summary_weekly');
                endif;
            
            if($table_type === 'sold'):
                $this->save_solid_listing_statistic( $date_range, 'sold_listing_summary_weekly');
                $this->save_dom_listing_statistic( $date_range, 'days_on_market_summary_weekly');
                $this->save_custom_dom_listing_statistic( $date_range, 'days_on_market_custom_7_days_summary_weekly', $weekly_dom_list, $weekly_pair);
                endif;

            //  City
                foreach ($CITY_LIST as $city) {
                    if($table_type === 'active'):
                        $this->save_active_listing_statistic( $date_range, 'active_availability_city_weekly', 'City', $city);
                        $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_city_weekly', 'City', $city);
                        endif;
                    
                    if($table_type === 'sold'):
                        $this->save_solid_listing_statistic( $date_range, 'sold_listing_city_weekly', 'City', $city);
                        endif;
                }

            //  Zip
                foreach ($ZIP_LIST as $zip) {
                    if($table_type === 'active'):
                        $this->save_active_listing_statistic( $date_range, 'active_availability_zip_weekly', 'PostalCode', $zip);
                        $this->save_active_no_offer_listing_statistic( $date_range, 'no_offer_active_availability_zip_weekly', 'PostalCode', $zip);
                        endif;
                    
                    if($table_type === 'sold'):
                        $this->save_solid_listing_statistic( $date_range, 'sold_listing_zip_weekly', 'PostalCode', $zip);
                        endif;
                }
        }

        /**
         * Import data in asset folder with table name
         * @param table_name    Name of the table. e.g. 'sold_listing', 'active_listing'
         */
        protected function import_data_in_asset(string $table_name)
        {
            $FOLDER_PATH = './asset/';
            $CONTROL =  new Controllisting();
            $listing =  $CONTROL->parseListingFile($FOLDER_PATH, $table_name);
                        $CONTROL->populateData($listing);
        }

        /**
         * Extract the data info from the file name
         * @param file_name     Name of the file
         */
        protected function extract_data_info(string $file_name)
        {
            //  Decode the file name
                $destruce = explode('.', $file_name)[0];        //  sold_weekly-20200112.txt
                $file_struct = explode('-', $destruce);         // [sold_weekly, 20200112]

            //  Table info
                $table_info = explode('_', $file_struct[0]);    //  sold_weekly
                $table_name = $table_info[0] . "_listing";      //  sold_listing 

            //  Query frequence
                $frequence  = $table_info[1];                   //  weekly 

            //  Get query date
                $query_date = DateTime::createFromFormat('Ymd', $file_struct[1]);
                $query_date = $query_date->format('Y-m-d');     //  2020-01-12
            
            return array(
                'table_name' => $table_name,
                'frequence'  => $frequence,
                'query_date' => $query_date 
            );
        }

        /**
         * Import automation
         * 1. import files to the data base,
         * 2. Process the data to the statistic log
         */
        public function import_automation($FOLDER_PATH)
        {   
            //  Get the files name
                $FILES = scandir($FOLDER_PATH);
                $FILES = array_diff(scandir($FOLDER_PATH), array('.', '..'));

            //  scan each file and store into contaner
                $total = count($FILES);
                $count = 0;
                foreach($FILES as $file):

                    //  extract data info
                        $data_info =    $this->extract_data_info($file);
                        $table_name =   $data_info['table_name'];
                        $insert_freq =  $data_info['frequence'];
                        $query_date =   $data_info['query_date'];
                        $date_range =   $this->return_date_range($query_date, $insert_freq);

                    //  Get the complete path
                        $COMPLETE_PATH = $FOLDER_PATH . $file;

                    //  When populate individal data finished, extract data to statistic log
                        $cntrl =        new Controllisting();
                        $listing =      $cntrl->parse_individual_file( $COMPLETE_PATH, $table_name );
                        $finished =     $cntrl->populate_individual_Data( $listing );
                        if($finished):

                            //  Populate data into statistic log  
                                switch ($insert_freq) {
                                    case 'weekly':      $this -> WEEKLY_RUN( $table_name, $date_range );     break;
                                    case 'monthly':     $this -> MONTHLY_RUN( $table_name, $date_range );    break;
                                }

                            //  Clear table
                                $this->destroyTable($table_name); 
                                $count ++;
                        endif;
                endforeach;            

                return ($total === $count) ? "All files imported" : "Something is missing, try again";
        }
    }
?>