<?php
    class Viewlisting extends Listing {

        /**
         * Main filter search
         * @param criteria      Array of search criteria
         * @param table_name    Name of the table
         * @param field_name    Name of the field. e.g. ClosePrice, ListPrice
         */
        public function MainFilterSearch(array $criteria, string $table_name, string $field_name)
        {
            return $this -> filter_search_with_criteria($criteria, $table_name, $field_name);
        }

        /**
         * Retrieve the query id
         * @param conditions    Conditions that needed to retrieve query id
         * @param table_name    Name of the table
         */
        public function retrieveQueryId(array $conditions, string $table_name)
        {
            return $this -> retrieve_query_id($conditions, $table_name);
        }

        /**
         * Retrieve search
         * @param conditions    Condition to filter through the datas
         * @param request       Array of request data
         * @param table_name    Name of the table
         */
        public function retrieveSearch(array $conditions, array $request, string $table_name)
        {
            //  Get the query id
                $query_id = $this -> retrieve_query_id($conditions, $table_name);

            //  Using query id to look for the values, instead of joinning tables
                $res = [];
                foreach ($request as $look_for) {
                    $data = $this -> retrieve_value_by_query_id($look_for, $query_id);
                    $res[$look_for] = $data;
                }
                return $res;
        }

        /**
         * New listing filter search
         * @param criteria      Array of search criteria 1
         * @param criteria2     Array of search criteria 2
         * @param table_name    Name of the table
         * @param field_name    Name of the field. e.g. ClosePrice, ListPrice
         */
        public function NewListingFilterSearch(array $criteria, array $criteria2, string $table_name, string $field_name)
        {
            return $this -> new_listing_statistic($criteria, $criteria2, $table_name, $field_name);
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * User auth
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Retrieve account info
         * @param ip                Ip address of the device
         * @param user              User name
         * @param pass              Password 
         */
        public function RetrieveAccount(string $ip, string $user, string $pass)
        {
            return $this -> account_auth($ip, $user, $pass);
        }

        /**
         * Token verification
         * @param token             Token
         */
        public function tokenVerify(string $token)
        {
            return $this -> token_verification($token);
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * ACTIVE LISTING
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Get the active pricing statistic
         * @param table_name    Table name
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        public function generate_active_listing_statistic(string $table_name, string $look_for, array $option = NULL)
        {
            //  Get general pricing statistic
                $sfr = $this -> get_active_listing_statistic($table_name, ['SFR'], $look_for, $option);
                $twh = $this -> get_active_listing_statistic($table_name, ['CON', 'TWH'], $look_for, $option);

            //  return
                return array(
                    'SFR'=> $sfr,
                    'TWH'=> $twh
                );
        }

        /**
         * Get the general pricing statistic of the list of properties
         * @param table_name    Table name
         * @param subtype       Sub type array. e.g. ['sfr']
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        protected function get_active_listing_statistic(string $table_name, array $subtype, string $look_for, array $option = NULL)
        {
            //  Prepare filter
                $filters = array (
                    $look_for =>            array( 'query' => 'none' ),
                    'PropertyType' =>       'RES',
                    'PropertySubType' =>    $subtype
                );

            //  Append to filters
                if(isset($option)):
                    foreach ($option as $k => $v): 
                        if($k !== 'QueryPurpose'): $filters[$k] = $v; endif;
                    endforeach;
                endif;

            //  Get the result
                $result = $this -> MainFilterSearch($filters, $table_name, $look_for);
            
            return array(
                'count' =>      $result['count'],
                'average' =>    $result['average'],
                'median' =>     $result['median'],
                'total' =>      $result['total']
            );
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Price per sqlf statistic
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Generate sqft price statistic
         * @param date_range    Date range of the requested piror
         * @param table_name    Name of the table
         * @param look_for      Field name. e.g. ClosePrice, ListPrice
         * @param option        Optional
         */
        public function generate_sqft_price_statistic(array $date_range, string $table_name, string $look_for, array $option = NULL)
        {
            //  Get general pricing statistic
                $sfr = $this -> get_sqft_price_statistic($table_name, ['SFR'], $date_range, $look_for, $option);
                $twh = $this -> get_sqft_price_statistic($table_name, ['CON', 'TWH'], $date_range, $look_for, $option);

            return array(
                'SFR'=> $sfr,
                'TWH'=> $twh
            );
        }

        /**
         * Get sqft price statistic
         * @param table_name    Name of the table
         * @param subtype       Sub property type
         * @param date_range    Date range of the requested piror
         * @param look_for      Field name. e.g. ClosePrice, ListPrice
         * @param option        Optional
         */
        protected function get_sqft_price_statistic(string $table_name, array $subtype, array $date_range, string $look_for, array $option = NULL)
        {
            //  Get the date range
                if(isset($date_range)):
                    $start =    $date_range[0];
                    $to =       $date_range[1];
                endif;

            //  Prepare filter
                $filters = array (
                    $look_for =>            array( 'query' => 'none' ),
                    'SqFtTotal' =>          array( 'query' => 'none' ),
                    'PropertyType' =>       'RES',
                    'PropertySubType' =>    $subtype,
                    'StatusContractualSearchDate' => array(
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    )
                );

            //  Append to filters
                if(isset($option)):
                    foreach ($option as $k => $v): 
                        if($k !== 'QueryPurpose'): $filters[$k] = $v; endif;
                    endforeach;
                endif;

            //  Get the result
                $result = $this -> get_sqft_statistic($filters, $table_name, $look_for);
            
            return array(
                'count' =>      $result['count'],
                'average' =>    $result['average'],
                'median' =>     $result['median'],
                'total' =>      $result['total']
            );
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * NEW LISTING STATISTIC
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Generate new listing statistic
         * @param table_name    Table name
         * @param date_range    Date range of the requested piror
         * @param look_for      Look for
         */
        public function generate_new_listing_statistic(string $table_name, array $date_range, string $look_for)
        {
            //  Get general pricing statistic
                $sfr = $this -> get_new_listing_statistic($table_name, ['SFR'], $date_range, $look_for);
                $twh = $this -> get_new_listing_statistic($table_name, ['CON', 'TWH'], $date_range, $look_for);

            //  return
                return array(
                    'SFR'=> $sfr,
                    'TWH'=> $twh
                );
        }

        /**
         * Get new listing statistic
         * @param table_name    Table name
         * @param subtype       Sub type array. e.g. ['sfr']
         * @param date_range    Array of series of dates
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         */
        protected function get_new_listing_statistic(string $table_name, array $subtype, array $date_range, string $look_for)
        {
            //  Decode the date range
                $start = $date_range[0];
                $to =   $date_range[1];

            //  Prepare filter
                $s = array (
                    $look_for => [ 'query' => 'none' ],
                    'PropertySubType' => $subtype,
                    'PropertyType' => 'RES',
                    'ListingContractDate' => array(
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    )
                );

                $s2 = array (
                    $look_for => [ 'query' => 'none' ],
                    'PropertySubType' => $subtype,
                    'PropertyType' => 'RES',
                    'ListingContractDate' => array(
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    ),
                    'OnMarketDate' => array( 
                        'query' => 'coloumn_notequal_month',
                        'start' => 'OnMarketDate',
                        'to' => 'ListingContractDate'
                    )
                );

            //  Get the result
                $r = $this -> NewListingFilterSearch($s, $s2, $table_name, $look_for);
            
            return array(
                'count' => $r['count'],
                'average' => $r['average'],
                'median' => $r['median'],
                'total' => $r['total']
            );
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * NO OFFER ACTIVE LISTING
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Get the no offer pricing statistic by month
         * @param table_name    Table name
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Option
         */
        public function generate_no_offer_statistic(string $table_name, string $look_for, array $option = NULL)
        {
            //  Get general pricing statistic
                $sfr = $this -> get_no_offer_pricing_statistic($table_name, ['SFR'], $look_for, $option);
                $twh = $this -> get_no_offer_pricing_statistic($table_name, ['CON', 'TWH'], $look_for, $option);

            //  return
                return array(
                    'SFR'=> $sfr,
                    'TWH'=> $twh
                );
        }

        /**
         * Get the no offer pricing statistic of the list of properties
         * @param table_name    Table name
         * @param subtype       Subtype array. e.g. ['sfr']
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Option
         */
        protected function get_no_offer_pricing_statistic(string $table_name, array $subtype, string $look_for, array $option = NULL)
        {
            //  Prepare filter
                $filters = array (
                    $look_for =>            array( 'query' => 'none' ),
                    'PropertySubType' =>    $subtype,
                    'PropertyType' =>       'RES',
                    'StatusUpdate' =>       'NOOFFERS'
                );

            //  Append to option
                if(isset($option)): 
                    foreach ($option as $k => $v): 
                        if($k !== 'QueryPurpose'): $filters[$k] = $v; endif; 
                    endforeach; 
                endif;

            //  Get the result
                $result = $this -> MainFilterSearch($filters, $table_name, $look_for);
            
            return array(
                'count' =>      $result['count'],
                'average' =>    $result['average'],
                'median' =>     $result['median'],
                'total' =>      $result['total']
            );
        }
        
/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * UNITS SOLD LISTING
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Get the general pricing statistic by month
         * @param date_range    Array of series of dates
         * @param table_name    Table name
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        public function generate_general_statistic_by_month(array $date_range, string $table_name, string $look_for, array $option = NULL)
        {
            //  Get general pricing statistic
                $sfr = $this -> get_general_pricing_statistic($table_name, ['SFR'], $date_range, $look_for, $option);
                $twh = $this -> get_general_pricing_statistic($table_name, ['CON', 'TWH'], $date_range, $look_for, $option);

            //  return
                return array(
                    'SFR'=> $sfr,
                    'TWH'=> $twh
                );
        }

        /**
         * Get the general pricing statistic of the list of properties
         * @param table_name    Table name
         * @param subtype       Sub property type array. e.g. ['sfr']
         * @param date_range    Array of series of dates
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        protected function get_general_pricing_statistic(string $table_name, array $subtype, array $date_range, string $look_for, array $option = NULL)
        {
            //  Get the date range
                $start =    $date_range[0];
                $to =       $date_range[1];

            //  Prepare filter
                $filters = array (
                    $look_for => array( 'query' => 'none' ),
                    'PropertySubType' => $subtype,
                    'PropertyType' => 'RES',
                    'StatusContractualSearchDate' => array(
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    )
                );

            //  Append to option
                if(isset($option)): 
                    foreach ($option as $k => $v): 
                        if($k !== 'QueryPurpose'): $filters[$k] = $v; endif; 
                    endforeach; 
                endif;

            //  Get the result
                $result = $this -> MainFilterSearch($filters, $table_name, $look_for);

            return array(
                'count' =>      $result['count'],
                'average' =>    $result['average'],
                'median' =>     $result['median'],
                'total' =>      $result['total']
            );
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * DOM listing
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Generate a DOM report by given month
         * @param date_range    Array of series of dates
         * @param table_name    Name of the table
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        public function generate_dom_report_periodic(array $date_range, string $table_name, string $look_for, array $option = NULL) 
        {
            //  Get total SFR units
                $sfr_total_units =      $this -> get_total_units($date_range, ['SFR'], $table_name, $look_for, $option);
                $sfr_offset_dom =       $sfr_total_units['offset'];
                $sfr_without_dom =      $sfr_total_units['without_dom'];

            //  Get total SFR units
                $twh_total_units =      $this -> get_total_units($date_range, ['CON', 'TWH'], $table_name, $look_for, $option);
                $twh_offset_dom =       $twh_total_units['offset'];
                $twh_without_dom =      $twh_total_units['without_dom'];

            //  SFR DOM statistic
                $sfr_DOM_statistic =    $this -> get_dom_statistic($sfr_offset_dom, $sfr_without_dom, $date_range, ['SFR'], $table_name, $option);
                $twh_DOM_statistic =    $this -> get_dom_statistic($twh_offset_dom, $twh_without_dom, $date_range, ['CON', 'TWH'], $table_name, $option);

            return array(
                'SFR' => $sfr_DOM_statistic,
                'TWH' => $twh_DOM_statistic
            );
        }

        /**
         * Get DOM statistic
         * @param offset        Offset, the units different
         * @param total         Total units count
         * @param date_range    Array of series of dates
         * @param subtype       Sub property type array. e.g. ['sfr']
         * @param table_name    Name of the table
         * @param option        Options
         */
        protected function get_dom_statistic(int $offset, float $total, array $date_range, array $subtype, string $table_name, array $option = NULL)
        {
            //  Decode the date range
                $start =    $date_range[0];
                $to =       $date_range[1];

            //  Days on market option
                $DOMlist = [
                    [
                        'number_compare' => 'Between',
                        'start' => 0,
                        'to' => 30
                    ],
                    [
                        'number_compare' => 'Between',
                        'start' => 31,
                        'to' => 60
                    ],
                    [
                        'number_compare' => 'Between',
                        'start' => 61,
                        'to' => 90
                    ],
                    [
                        'number_compare' => 'Between',
                        'start' => 91,
                        'to' => 120
                    ],
                    [
                        'number_compare' => 'MoreEqualThan',
                        'start' => 121,
                    ]
                ];
            
            //  Get the statistic
                $res = [];
                $count = 0;
                foreach ($DOMlist as $sets) {

                    $filters = array (
                        'DOM' => $sets,
                        'ClosePrice' => array('query'=>'None'),
                        'PropertyType' => 'RES',
                        'PropertySubType' => $subtype,
                        'StatusContractualSearchDate' => [
                            'date_compare' => 'Between',
                            'start' => $start,
                            'to' => $to
                        ]
                    );        

                    //  Append to filter if there's any option
                    if(isset($option)): 
                        foreach ($option as $k => $v): 
                            if($k !== 'QueryPurpose'): $filters[$k] = $v; endif; 
                        endforeach; 
                    endif;

                    //  DOM result
                    $DOM_res =  $this -> MainFilterSearch($filters, $table_name, 'DOM');
                    $units =    $DOM_res['count'];
                    $median =   $DOM_res['median'];
                    $average =  $DOM_res['average'];

                    //  Modify the unit due to some of the units doesn't have dom
                    $units =    ($count == 0 ) ? $units + $offset : $units;

                    //  Calculate percentage
                    $ratio =    ($total) ? $units / $total : 0;
                    $percent =  ($ratio) ? round($ratio, 4) * 100 . "%" : 0;

                    $res[] = array(
                        'count'=>       $units,
                        'median'=>      $median,
                        'average'=>     $average,
                        'percent'=>     $percent
                    );

                    $count ++;
                }

            return $res;
        }

        /**
         * Get the total units
         * @param date_range    Array of series of dates
         * @param subtype       Sub property type array. e.g. ['sfr']
         * @param table_name    Table name
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        protected function get_total_units(array $date_range, array $subtype, string $table_name, string $look_for, array $option = NULL)
        {
            //  Decode the date range
                $start =    $date_range[0];
                $to =       $date_range[1];

            //  Prepare filter options
                $w_dom = array (
                    $look_for => array( 'query' => 'None' ),
                    'PropertySubType' => $subtype,
                    'PropertyType' => 'RES',
                    'DOM' => [ 'query' => 'None' ],
                    'StatusContractualSearchDate' => [
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    ]
                );   
                $wo_dom = array (
                    $look_for => array( 'query' => 'None' ),
                    'PropertySubType' => $subtype,
                    'PropertyType' => 'RES',
                    'StatusContractualSearchDate' => [
                        'date_compare' => 'Between',
                        'start' => $start,
                        'to' => $to
                    ]
                );  

                if(isset($option)): 
                    foreach ($option as $k => $v): 
                        if($k !== 'QueryPurpose'): $w_dom[$k] = $v; $wo_dom[$k] = $v; endif; 
                    endforeach; 
                endif;

            //  Get result
                $wdom_res =     $this -> MainFilterSearch($w_dom, $table_name, $look_for);          
                $wodom_res =    $this -> MainFilterSearch($wo_dom, $table_name, $look_for);
            
            //  Calculate the Dom offset
                $offset = $wodom_res['count'] - $wdom_res['count'];

            return [
                'offset'        => $offset,
                'with_dom'      => $wdom_res['count'],
                'without_dom'   => $wodom_res['count']
            ];
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * DOM Custom listing
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */
        /**
         * Generate a DOM report by given month
         * @param date_range    Array of series of dates
         * @param DOM_list      Custom DOM list
         * @param table_name    Name of the table
         * @param look_for      Look for the pice type. e.g. ListPrice, ClosePrice
         * @param option        Options
         */
        public function generate_dom_report_custom_periodic(array $date_range, array $DOM_list, string $table_name, string $look_for, array $option = NULL) 
        {
            //  Get total SFR units
                $sfr_total_units =      $this -> get_total_units($date_range, ['SFR'], $table_name, $look_for, $option);
                $sfr_offset_dom =       $sfr_total_units['offset'];
                $sfr_without_dom =      $sfr_total_units['without_dom'];

            //  Get total SFR units
                $twh_total_units =      $this -> get_total_units($date_range, ['CON', 'TWH'], $table_name, $look_for, $option);
                $twh_offset_dom =       $twh_total_units['offset'];
                $twh_without_dom =      $twh_total_units['without_dom'];

            //  SFR DOM statistic
                $sfr_DOM_statistic =    $this -> get_dom_custom_statistic($sfr_offset_dom, $sfr_without_dom, $date_range, ['SFR'], $table_name, $DOM_list, $option);
                $twh_DOM_statistic =    $this -> get_dom_custom_statistic($twh_offset_dom, $twh_without_dom, $date_range, ['CON', 'TWH'], $table_name, $DOM_list, $option);

            return array(
                'SFR' => $sfr_DOM_statistic,
                'TWH' => $twh_DOM_statistic
            );
        }

        /**
         * Get DOM statistic
         * @param offset        Offset, the units different
         * @param total         Total units count
         * @param date_range    Array of series of dates
         * @param subtype       Sub property type array. e.g. ['sfr']
         * @param table_name    Name of the table
         * @param DOM_list      Custom DOM list
         * @param option        Options
         */
        protected function get_dom_custom_statistic(int $offset, float $total, array $date_range, array $subtype, string $table_name, array $DOM_list, array $option = NULL)
        {
            //  Decode the date range
                $start =    $date_range[0];
                $to =       $date_range[1];
            
            //  Get the statistic
                $res = [];
                $count = 0;
                foreach ($DOM_list as $sets) {

                    $filters = array (
                        'DOM' => $sets,
                        'ClosePrice' => array('query'=>'None'),
                        'PropertyType' => 'RES',
                        'PropertySubType' => $subtype,
                        'StatusContractualSearchDate' => [
                            'date_compare' => 'Between',
                            'start' => $start,
                            'to' => $to
                        ]
                    );        

                    //  Append to filter if there's any option
                    if(isset($option)): 
                        foreach ($option as $k => $v): 
                            if($k !== 'QueryPurpose'): $filters[$k] = $v; endif; 
                        endforeach; 
                    endif;

                    //  DOM result
                    $DOM_res =  $this -> MainFilterSearch($filters, $table_name, 'DOM');
                    $units =    $DOM_res['count'];
                    $median =   $DOM_res['median'];
                    $average =  $DOM_res['average'];

                    //  Modify the unit due to some of the units doesn't have dom
                    $units =    ($count == 0 ) ? $units + $offset : $units;

                    //  Calculate percentage
                    $ratio =    ($total) ? $units / $total : 0;
                    $percent =  ($ratio) ? round($ratio, 4) * 100 . "%" : 0;

                    $res[] = array(
                        'count'=>       $units,
                        'median'=>      $median,
                        'average'=>     $average,
                        'percent'=>     $percent
                    );

                    $count ++;
                }

            return $res;
        }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Get data list
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */ 
        /**
         * Get the data list by given field name
         * @param table_name    Table name
         */
        public function getCityZipList(string $table_name)
        {
            return $this -> get_city_zip_list($table_name);
        }

        /**
         * Get the data list by field name
         * @param table_name Table name
         * @param field_name Field name
         */
        public function getDataListByField(string $field_name)
        {
            switch ($field_name) {
                case 'PostalCode':  return $this -> get_postal_code_list();     break;
                case 'City':        return $this -> generate_city_value_list(); break;
            }
        }

        /**
         * Get postal code list
         */
        public function get_postal_code_list()
        {
            return $this -> generate_postal_code_list();
        }

        /**
         * Get city name list
         */
        public function get_city_name_list()
        {
            return $this -> generate_city_name_list_by_type('name');
        }

        /**
         * Get city name list
         */
        public function getCityList()
        {
            return $this -> generate_city_list();
        }
    }
?>