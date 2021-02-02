<?php 

    //  API Class
    class API extends Viewlisting {

    /*	==========================================================================================
        Security
        ==========================================================================================	*/

        /**
         * Generate auth token
         * @param ip                Ip address of the device
         * @param user              User name
         * @param pass              Password
         */
        public function generate_auth_token($data)
        {
            if(isset($data)) :
                $ip =   $data['ip'];
                $user = $data['user'];
                $pass = $data['pass'];
            endif;

            //  vaidate ip
            if(is_null($ip) || empty($ip) || !isset($ip)):
                return array('status' => false, 'msg' => 'unable to verify'); exit; endif;
                
            //  vaidate user
            if(is_null($user) || empty($user) || !isset($user)):
                return array('status' => false, 'msg' => 'unable to verify'); exit; endif;
                
            //  vaidate pass
            if(is_null($pass) || empty($pass) || !isset($pass)):
                return array('status' => false, 'msg' => 'unable to verify'); exit; endif;
                
            //  Verify
            $verified = $this -> verifying($ip, $user, $pass);
            if($verified['st'] === false)
            { 
                return array('status' => false, 'msg' => 'unable to verify'); exit; 
            }
            else
            {
                if($verified['tk'] !== ''){
                
                    $token = $verified['tk'];
                
                }else{
                    
                    $token = token_generator();
                    $cntrl = new Controllisting();
                    $cntrl -> insertToken($token, $verified['id']);
                }

                return array( 'status' => true, 'token' => $token );
            }            
        }

        /**
         * 
         * @param ip                Ip address of the device
         * @param user              User name
         * @param pass              Password
         */
        protected function verifying(string $ip, string $user, string $pass)
        {
            $account = $this -> RetrieveAccount($ip, $user, $pass);
            if($account === false ):        return array( 'st' => false); endif;
            if($account['id'] === ''):      return array( 'st' => false); endif;
            if($ip !== $account['ip']):     return array( 'st' => false); endif;
            if($user !== $account['us']):   return array( 'st' => false); endif;
            if($pass !== $account['ps']):   return array( 'st' => false); endif;
            return array( 'st' => true, 'tk' => $account['tk'], 'id' => $account['id'] );
        }

        /**
         * Token verification
         * @param token             Token
         */
        protected function token_verify(string $token)
        {
            return $this -> tokenVerify($token);
        }

    /*	==========================================================================================
        Date process functions
        ==========================================================================================	*/   

        /**
         * Unify the date of insertation:: weekly
         * @param date              The date of query
         */
        protected function union_insert_date_weekly($date)
        {
            $ULTIL = new Ultil();
            return $ULTIL -> get_sunday($date);
        }

        /**
         * Unify the date of insertation:: monthly
         * @param date              The date of query
         */
        protected function union_insert_date_monthly($date)
        {
            $ULTIL = new Ultil();
            return $ULTIL -> get_last_day_month($date);
        }

        /**
         * Return the date range
         * @param QueryDate         The date of query
         * @param purpose           The purpose of query
         */
        protected function return_date_range(string $QueryDate, string $purpose)
        {
            $ULTIL = new Ultil();
            if (strpos($purpose, 'weekly') !== false) : 
                return  $ULTIL -> getDateRange($QueryDate, 'wholeWeek_w', 1);  endif;
            if (strpos($purpose, 'monthly') !== false) : 
                return  $ULTIL -> getDateRange($QueryDate, 'wholeMonth_m', 1); endif;
        }

        /**
         * Get month ago range
         * @param date_range    
         */
        protected function get_month_ago_range(array $date_range)
        {
            //  Last day of pervious month
                $last_day_pervious_month = $this -> get_a_month_ago($date_range[0]);

            //  get the last date of this month
                $begin = date('Y-m-01', strtotime($last_day_pervious_month));
                $end = $last_day_pervious_month;

            return [$begin, $end];
        }

        /**
         * Get week ago date
         * @param date
         */
        protected function get_a_week_ago(string $date)
        {
            $sunday = $this -> union_insert_date_weekly($date);
            $res = date('Y-m-d', strtotime("-1 week", strtotime($sunday)));
            return $res;
        }

        /**
         * Get month ago date
         * @param date
         */
        protected function get_a_month_ago(string $date)
        {
            $date = strtotime("last day of previous month", strtotime($date));
            $last_day_pervious_month = date('Y-m-d', $date);
            return $last_day_pervious_month;
        }

    /*	==========================================================================================
        Public API
        ==========================================================================================	*/   

        /**
         * Get zip list
         */
        public function get_zip_list()
        {
            $res = $this -> get_postal_code_list();
            return (!empty($res)) 
                    ? array( 'status' => true, 'data' => $res)
                    : array( 'status' => false, 'msg' => 'Unable to get zip code list');
        }

        /**
         * Get city list
         */
        public function get_city_list()
        {
            $res = $this -> get_city_name_list();
            return (!empty($res)) 
                    ? array( 'status' => true, 'data' => $res)
                    : array( 'status' => false, 'msg' => 'Unable to get city list');
        }

        /**
         * Complete city list
         */
        public function complete_city_list()
        {
            $res = $this -> getCityList();
            return (!empty($res)) 
                ? array( 'status' => true, 'data' => $res)
                : array( 'status' => false, 'msg' => 'Unable to get city list');
        }

        /**
         * Get sold listing
         * @param data              Set of data parameters
         */
        public function get_sold_listing(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';    //  e.g. 'weekly', 'monthly'
                $purpose =      $data['purpose'];                                   //  e.g. sold_listing_city, sold_listing_zip, sold_listing_summary
                $date_range =   $data['date_range'];                                //  e.g. [[2020-11-01, 2020-11-30]]
                $city_name =    (isset($data['city'])) ? $data['city'] : '';        //  e.g. 'LASVEGAS'
                $zip_code =     (isset($data['zip']))  ? $data['zip'] : '';         //  e.g. '89031'
            } 

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                   return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;

            //  validate purpose
                $accept_purpose = ['sold_listing_city', 'sold_listing_zip', 'sold_listing_summary'];
                if(!in_array($purpose, $accept_purpose)): 
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  validate city
                if($purpose == 'sold_listing_city' && is_null($city_name)):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate zip
                if($purpose == 'sold_listing_zip' && is_null($zip_code)):
                    if(is_null($zip_code) || empty($zip_code)):
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):    $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
            
            //  Re-purpose
                $purpose = $purpose . "_" . $period;

            //  Returns sold listing
                $res = $this -> multi_dates_sold_listing_summary($date_range, $purpose, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * Get sold listing overview, which can be query from either city or postal code
         * @param data              Set of data parameters
         */
        public function get_sold_listing_overview(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';    //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                  //  e.g. sold_listing_city, sold_listing_zip
                $date_range =    $data['date_range'];                               //  e.g. [[2020-11-01, 2020-11-30]]
                $overview_type = ucfirst($data['overview_type']);                   //  e.g. PostalCode, City
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['sold_listing_city', 'sold_listing_zip'];
                if(!in_array($purpose, $accept_purpose)): 
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  vaidate overview type
                if(is_null($overview_type) || $overview_type == ""):
                    return array('status' => false, 'msg' => 'Overview type can not be empty'); exit; endif;
                    
            //  validate overview type
                $accept_type = ['PostalCode', 'City'];
                if(!in_array($overview_type, $accept_type)): 
                    return array('status' => false, 'msg' => 'Invalid overview type'); exit; endif;
    
            //  validate overview type
                switch ($overview_type) {
                    case 'PostalCode':
                        if($purpose !== 'sold_listing_zip'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                    case 'City':
                        if($purpose !== 'sold_listing_city'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                }
            
            //  validate date
                if(!is_array($date_range)):
                    return array('status' => false, 'msg' => 'Invalid date range'); exit; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;
    
            //  Generate the list
                if($overview_type === 'PostalCode'){
                    $List = $this -> getDataListByField($overview_type);
                    if(count($List) !== 0) {
                        $res_list = [];
                        foreach ($List as $value) {
                            $options = array( $overview_type => $value );                    
                            $res_list[$value] = $this -> multi_dates_sold_listing_summary($date_range, $purpose, $options);
                        }
                        return array('status' => true, 'data' => $res_list );
                    }else{
                        return array('status' => false, 'msg' => 'Unable to extract the list');
                    }
                }else{
                    $CityList = $this -> getCityList();
                    if(count($CityList) !== 0) {
                        $res_list = [];
                        foreach ($CityList as $city) {

                            //  My bad, it was flipped
                            $value = $city['name'];
                            $name = $city['value'];

                            $options = array( $overview_type => $value );   

                            $res_list[$value] = $this -> multi_dates_sold_listing_summary($date_range, $purpose, $options);
                            $res_list[$value]['city'] = $name;//array('name'=>$name);
                        }    
                        return array('status' => true, 'data' => $res_list );
                    
                    }else{
                        return array('status' => false, 'msg' => 'Unable to extract the list');
                    }
                }
        }

        /**
         * Get days on market report
         * @param data              Set of data parameters
         */
        public function get_days_on_market(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';        //  e.g. 'weekly', 'monthly'
                $purpose =      $data['purpose'];                                       //  e.g. days_on_market_city, days_on_market_zip, days_on_market_summary
                $date_range =   $data['date_range'];                                    //  e.g. [[2020-11-01, 2020-11-30], ['2020-10-01', '2020-10-31']]
                $city_name =    (isset($data['city'])) ? $data['city'] : '';            //  e.g. 'LASVEGAS'
                $zip_code =     (isset($data['zip']))  ? $data['zip'] : '';             //  e.g. '89031'
                $query_date =   $data['query_date'];                                    //  e.g. '2020-12-29'
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;    

            //  Validate purpose
                $accept_purpose = ['days_on_market_city', 'days_on_market_zip', 'days_on_market_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
            
            //  Validate date range
                if(!is_array($date_range)):
                    return array('status' => false, 'msg' => 'Invalid date range'); exit; endif;
    
            //  validate city
                if($purpose == 'days_on_market_city'):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate zip
                if($purpose == 'days_on_market_zip'):
                    if(is_null($zip_code) || empty($zip_code)):
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;            
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):    $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
    
            //  Returns sold listing
                $res = $this -> get_day_on_market_summary($date_range, $purpose, $query_date, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * (NOT IN USE) Get days on market report
         * @param data              Set of data parameters
         */
        public function get_days_on_market_overview(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';    //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                  //  e.g. days_on_market_city, days_on_market_zip
                $date_range =    $data['date_range'];                               //  e.g. [[2020-11-01, 2020-11-30]]
                $overview_type = ucfirst($data['overview_type']);                   //  e.g. PostalCode, City
                $query_date =    $data['query_date'];                               //  e.g. '2020-12-29'
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;   
    
            //  validate purpose
                $accept_purpose = ['days_on_market_city', 'days_on_market_zip'];
                if(!in_array($purpose, $accept_purpose)): 
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  vaidate overview type
                if(is_null($overview_type) || $overview_type == ""):
                    return array('status' => false, 'msg' => 'Overview type can not be empty'); exit; endif;
                    
            //  validate overview type
                $accept_type = ['PostalCode', 'City'];
                if(!in_array($overview_type, $accept_type)): 
                    return array('status' => false, 'msg' => 'Invalid overview type'); exit; endif;
    
            //  validate overview type
                switch ($overview_type) {
                    case 'PostalCode':
                        if($purpose !== 'days_on_market_zip'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                    case 'City':
                        if($purpose !== 'days_on_market_city'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                }
            
            //  validate date
                if(!is_array($date_range)):
                    return array('status' => false, 'msg' => 'Invalid date range'); exit; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;          
    
            //  Generate the list
                $List = $this -> getDataListByField($overview_type);
                if(count($List) !== 0) {
                    $res_list = [];
                    foreach ($List as $value) {
                        $options = array( $overview_type => $value );
                        $res_list[$value] = $this -> get_day_on_market_summary($date_range, $purpose, $query_date, $options);
                    }
                    return array('status' => true, 'data' => $res_list );
                }else{
                    return array('status' => false, 'msg' => 'Unable to extract the list');
                }
        }

        /**
         * Get days on market custom periodic report
         * @param data              Set of data parameters
         */
        public function get_days_on_market_custom_periodic(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';        //  e.g. 'weekly', 'monthly'
                $purpose =      $data['purpose'];                                       //  e.g. 'days_on_market_custom_7_days', 'days_on_market_custom_30_days'
                $date_range =   $data['date_range'];                                    //  e.g. [[2020-11-01, 2020-11-30], ['2020-10-01', '2020-10-31']]
                $city_name =    (isset($data['city'])) ? $data['city'] : '';            //  e.g. 'LASVEGAS'
                $zip_code =     (isset($data['zip']))  ? $data['zip'] : '';             //  e.g. '89031'
                $query_date =   $data['query_date'];                                    //  e.g. '2020-12-29'
                $periodic_type = $data['periodic_type'];                                //  e.g. '7', '30'
            }
    
            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;

            //  Validate purpose
                $accept_purpose = ['days_on_market_custom_7_days_summary', 'days_on_market_custom_30_days_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
            
            //  Validate date range
                if(!is_array($date_range)):
                    return array('status' => false, 'msg' => 'Invalid date range'); exit; endif;
    
            //  validate city
                if($purpose == 'days_on_market_city'):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate zip
                if($purpose == 'days_on_market_zip'):
                    if(is_null($zip_code) || empty($zip_code)):
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;      
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):    $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
    
            //  Returns sold listing
                $res = $this -> get_day_on_market_periodic_summary($date_range, $purpose, $query_date, $periodic_type, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * Get active listing
         * @param data              Set of data parameters
         */
        public function get_active_listing(Array $data)
        {
            if(is_array($data)){
                $period =       (isset($data['period'])) ? $data['period'] : '';        //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                      //  e.g. active_availability_city, active_availability_zip, active_availability_summary
                $query_date =    $data['query_date'];                                   //  e.g. '2020-12-29'
                $zip_code =      (isset($data['zip']))  ? $data['zip'] : '';            //  e.g. '89031'
                $city_name =     (isset($data['city'])) ? $data['city'] : '';           //  e.g. 'LASVEGAS' 
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['active_availability_city', 'active_availability_zip', 'active_availability_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  validate query date
                if(is_null($query_date)): 
                    return array('status' => false, 'msg' => 'Query date must not be null'); exit; endif;
    
            //  validate city
                if($purpose == 'active_availability_city'):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate zip
                if($purpose == 'active_availability_zip'):
                    if(is_null($zip_code) || empty($zip_code)):
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;
    
            //  Re-purpose
                $purpose = $purpose . "_" . $period;   
                
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):    $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
    
            //  Returns sold listing
                $res = $this -> get_active_listing_summary($purpose, $query_date, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * Get growth of active listing
         * @param data              Set of data parameters
         */
        public function get_active_listing_growth(Array $data)
        {
            if(is_array($data)){
                $period =        (isset($data['period'])) ? $data['period'] : '';       //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                      //  e.g. active_availability_city, active_availability_zip, active_availability_summary
                $date_sets =     (isset($data['date_sets'])) ? $data['date_sets'] : []; //  e.g. ['2020-12-31', '2020-11-30']
                $zip_code =      (isset($data['zip']))  ? $data['zip'] : '';            //  e.g. '89031'
                $city_name =     (isset($data['city'])) ? $data['city'] : '';           //  e.g. 'LASVEGAS' 
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['active_availability_city', 'active_availability_zip', 'active_availability_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  validate query date
                if(is_null($date_sets) || empty($date_sets)): 
                    return array('status' => false, 'msg' => 'Set of dates can not be empty'); exit; endif;

            //  validate the city
                if($purpose == 'active_availability_city'):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate the zip
                if($purpose == 'active_availability_zip'):
                    if(is_null($zip_code) || empty($zip_code)):
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;   
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):   $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
    
            //  Combine the result
                $res = $this -> multi_dates_active_listing_summary($date_sets, $purpose, $period, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * Get active listing overview, which can be query from either city or postal code
         * @param data              Set of data parameters
         */
        public function get_active_listing_overview(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';    //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                  //  e.g. 'active_availability_city', 'active_availability_zip'
                $overview_type = ucfirst($data['overview_type']);                   //  e.g. PostalCode, City
                $query_date =    $data['query_date'];                               //  e.g. '2020-12-29'
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['active_availability_city', 'active_availability_zip'];
                if(!in_array($purpose, $accept_purpose)): 
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  vaidate overview type
                if(is_null($overview_type) || empty($overview_type)):
                    return array('status' => false, 'msg' => 'Overview type can not be empty'); exit; endif;
                    
            //  validate overview type
                $accept_type = ['PostalCode', 'City'];
                if(!in_array($overview_type, $accept_type)): 
                    return array('status' => false, 'msg' => 'Invalid overview type'); exit; endif;
    
            //  validate overview type
                switch ($overview_type) {
                    case 'PostalCode':
                        if($purpose !== 'active_availability_zip'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                    case 'City':
                        if($purpose !== 'active_availability_city'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                }

            //  Re-purpose
                $purpose = $purpose . "_" . $period;   
            
            //  Generate the list
                $List = $this -> getDataListByField($overview_type);
                if(count($List) !== 0) {
                    $res_list = [];
                    foreach ($List as $value) {
                        $options = array( $overview_type => $value );
                        $res_list[$value] = $this -> get_active_listing_summary($purpose, $query_date, $options);
                    }
                    return array('status' => true, 'data' => $res_list );
                }else{
                    return array('status' => false, 'msg' => 'Unable to extract the list');
                }
        }

        /**
         * Get active listing with no offer
         * @param data              Set of data parameters
         */
        public function get_no_offer_active_listing(Array $data)
        {
            if(is_array($data)){
                $period =       (isset($data['period'])) ? $data['period'] : '';        //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                      //  e.g. 'no_offer_active_availability_city', 'no_offer_active_availability_zip', 'no_offer_active_availability_summary'
                $query_date =    $data['query_date'];                                   //  e.g. '2020-12-29'
                $zip_code =      (isset($data['zip']))  ? $data['zip'] : '';            //  e.g. '89031'
                $city_name =     (isset($data['city'])) ? $data['city'] : '';           //  e.g. 'LASVEGAS' 
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['no_offer_active_availability_city', 'no_offer_active_availability_zip', 'no_offer_active_availability_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  validate query date
                if(is_null($query_date) || empty($query_date)): 
                    return array('status' => false, 'msg' => 'Query date must not be null'); exit; endif;
    
            //  validate city name
                if($purpose == 'no_offer_active_availability_city'):
                    if(is_null($city_name) || empty($city_name)): 
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate zip code
                if($purpose == 'no_offer_active_availability_zip'):
                    if(is_null($zip_code) || empty($zip_code)): 
                        return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;   
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):   $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
    
            //  Returns sold listing
                $res = $this -> get_active_listing_summary($purpose, $query_date, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * Get growth of active listing with no offer
         * @param data              Set of data parameters
         */
        public function get_no_offer_active_listing_growth(Array $data)
        {
            if(is_array($data)){
                $period =        (isset($data['period'])) ? $data['period'] : '';       //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                      //  e.g. no_offer_active_availability_city, no_offer_active_availability_zip, no_offer_active_availability_summary
                $date_sets =     (isset($data['date_sets'])) ? $data['date_sets'] : []; //  e.g. ['2020-12-31', '2020-11-30']
                $zip_code =      (isset($data['zip']))  ? $data['zip'] : '';            //  e.g. '89031'
                $city_name =     (isset($data['city'])) ? $data['city'] : '';           //  e.g. 'LASVEGAS' 
            }
    
            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;

            //  validate purpose
                $accept_purpose = ['no_offer_active_availability_city', 'no_offer_active_availability_zip', 'no_offer_active_availability_summary'];
                if(!in_array($purpose, $accept_purpose)):
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  validate date sets
                if(is_null($date_sets) || empty($date_sets)): 
                    return array('status' => false, 'msg' => 'Set of dates can not be empty'); exit; endif;

            //  validate the city
                if($purpose == 'no_offer_active_availability_city'):
                    if(is_null($city_name) || empty($city_name)):
                        return array('status' => false, 'msg' => 'City must not be null'); exit; endif; endif;
    
            //  validate the zip
                if($purpose == 'no_offer_active_availability_zip'):
                    if(is_null($zip_code) || empty($zip_code)):
                    return array('status' => false, 'msg' => 'Postal code must not be null'); exit; endif; endif;

            //  Re-purpose
                $purpose = $purpose . "_" . $period;   
    
            //  Build options
                $options = [];
                    if(isset($zip_code) && $zip_code !== ''):    $options['PostalCode'] = $zip_code;     endif;
                    if(isset($city_name) && $city_name !== ''):  $options['City'] = $city_name;          endif;
            
            //  Combine the result
                $res =      $this -> multi_dates_active_listing_summary($date_sets, $purpose, $period, $options);
                return array('status' => true, 'data' => $res);
        }

        /**
         * (NOT IN USE) Get growth of active listing with no offer
         * @param data              Set of data parameters
         */
        public function get_no_offer_active_listing_overview(Array $data)
        {
            if (is_array($data)) {
                $period =       (isset($data['period'])) ? $data['period'] : '';    //  e.g. 'weekly', 'monthly'
                $purpose =       $data['purpose'];                                  //  e.g. 'no_offer_active_availability_city', 'no_offer_active_availability_zip'
                $overview_type = ucfirst($data['overview_type']);                   //  e.g. PostalCode, City
                $query_date =    $data['query_date'];                               //  e.g. '2020-12-29'
            }

            //  validate period
                $accept_period = ['weekly', 'monthly'];
                if(!in_array($period, $accept_period)):
                    return array( 'status' => false, 'msg' => 'invalid period'); exit; endif;
    
            //  validate purpose
                $accept_purpose = ['no_offer_active_availability_zip', 'no_offer_active_availability_city'];
                if(!in_array($purpose, $accept_purpose)): 
                    return array('status' => false, 'msg' => 'Invalid purpose'); exit; endif;
    
            //  vaidate overview type
                if(is_null($overview_type) || empty($overview_type)):
                    return array('status' => false, 'msg' => 'Overview type can not be empty'); exit; endif;
                    
            //  validate overview type
                $accept_type = ['PostalCode', 'City'];
                if(!in_array($overview_type, $accept_type)): 
                    return array('status' => false, 'msg' => 'Invalid overview type'); exit; endif;
    
            //  validate overview type
                switch ($overview_type) {
                    case 'PostalCode':
                        if($purpose !== 'no_offer_active_availability_zip'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                    case 'City':
                        if($purpose !== 'no_offer_active_availability_city'): 
                            return array('status' => false, 'msg' => 'Purpose and overview type does not match'); exit; endif; break;
                }

            //  Re-purpose
                $purpose = $purpose . "_" . $period;  
            
            //  Generate the list
                $List = $this -> getDataListByField($overview_type);
                if(count($List) !== 0) {
                    $res_list = [];
                    foreach ($List as $value) {
                        $options = array( $overview_type => $value );                    
                        $res_list[$value] = $this -> get_active_listing_summary($purpose, $query_date, $options);
                    }
                    return array('status' => true, 'data' => $res_list );
                }else{
                    return array('status' => false, 'msg' => 'Unable to extract the list');
                }
        }

    /*	==========================================================================================
        Data processors
        ==========================================================================================	*/   

        /**
         * Multidates active listing summary
         * @param date_sets         Set of dates
         * @param purpose           Purpose of query
         * @param period            Type of period
         * @param options           Optional
         */
        protected function multi_dates_active_listing_summary (Array $date_sets, String $purpose, String $period, Array $options = NULL) 
        {
            $arr = [];
            foreach ($date_sets as $k => $date) {

                //  get the comparison date, unify the date
                    switch ($period) {
                        case 'weekly':
                            $compare_date = date('Y-m-d', strtotime("-1 week", strtotime($date))); 
                            break;
                        case 'monthly': 
                            $compare_date = $this -> get_a_month_ago($date);  
                            break;
                    }

                //  Get two set of the result
                    $res =   $this -> get_active_listing_summary($purpose, $date, $options);
                    $pre =   $this -> get_active_listing_summary($purpose, $compare_date, $options);
                
                //  Combine the result
                    $res['Growth'] =    $this -> get_comparison_result($res, $pre);

                //  Use month name as object name            
                    $res['compare'] =   $date;
                    $res['with'] =      $compare_date;

                    $month =    date('M', strtotime($date));
                    $date =     date('d', strtotime($date));
                    $indexName = "$month-$date";

                    $arr[$indexName] = $res;
            }
            return $arr;
        }

        /**
         * Multidates sold listing summary by month range
         * @param month_range       Array of month range.   e.g. [[2020-11-01, 2020-11-30], [2020-10-01, 2020-10-31]]
         * @param purpose           Purpose of the request  e.g. 'sold_listing_city', 'sold_listing_zip', 'sold_listing_summary'
         * @param query_date        Date of the query
         * @param options           Additional filters
         */
        protected function multi_dates_sold_listing_summary (Array $month_ranges, String $purpose, Array $options = NULL)
        {
            $arr = [];
            foreach ($month_ranges as $date) {
    
                //  Dates
                    $begin_date =    $date[0];
                    $end_date =      $date[1];

                //  A month ago
                    $month_ago =     $this -> get_month_ago_range($date);

                //  Format the result
                    $res = $this -> get_sold_listing_statistic($purpose, $date, $options);
                    $pre = $this -> get_sold_listing_statistic($purpose, $month_ago, $options);

                //  Extract growth from comparison and append to $res
                    $res['Growth'] =    $this -> get_comparison_result($res, $pre);

                //  Use month name as object name            
                    $res['DateBegin'] = $begin_date;
                    $res['DateEnd'] =   $end_date;

                    $indexName = date("F", strtotime($begin_date));
                    $indexYear = date('Y', strtotime($begin_date));
                    $indexShor = shorten_month($indexName);
                    $indexName = "$indexYear-$indexShor";
                    $arr[$begin_date] = $res;
            }
            return $arr;
        }

        /**
         * Get sold listing summary
         * @param purpose           Purpose of the request
         * @param date_range        Array of month range.
         * @param options           Additional filters
         */
        protected function get_sold_listing_statistic(string $purpose, array $date_range, array $options = NULL)
        {
            //  Dates
                $begin_date =    $date_range[0];
                $end_date =      $date_range[1];

            //  Filter to look for
                $filter = array(
                    'QueryPurpose'  => $purpose,
                    'DateBegin'     => $begin_date,
                    'DateEnd'       => $end_date
                );
            
            //  Append options to filter, if any
                if(isset($options)): foreach ($options as $k => $v) : $filter[$k] = $v; endforeach; endif;

            //  Data need to display
                $request = [
                    'SFRCount',
                    'SFRMedian',
                    'SFRAverage',
                    'SFRTotal',
                    'TWHCount',
                    'TWHMedian',
                    'TWHAverage',
                    'TWHTotal',
                ];

            //  Result as associated array
                $res = $this -> retrieveSearch($filter, $request, 'statistic_log');
                $res = data_formatter('category', $res);

            return $res;
        }

        /**
         * Get active listing summary
         * @param query_date        Date of query           e.g. '2020-11-01'
         * @param purpose           Purpose of the request  e.g. 'active_availability_city', 'active_availability_zip', 'active_availability_summary'
         * @param purpose           The purpose of query
         * @param options           Additional filters
         */
        protected function get_active_listing_summary(String $purpose, String $query_date, Array $options = NULL)
        {
            //  Filter to look for
                $filter = array(
                    'QueryPurpose'  => $purpose,
                    'QueryDate'     => $query_date
                );

            //  Append options to filter, if any
                if(isset($options)): foreach ($options as $k => $v) : $filter[$k] = $v; endforeach; endif;

            //  Data need to display
                $request = [
                    'SFRCount',
                    'SFRMedian',
                    'SFRAverage',
                    'SFRTotal',
                    'TWHCount',
                    'TWHMedian',
                    'TWHAverage',
                    'TWHTotal'
                ];
            
            //  Result as associated array
                $res = $this -> retrieveSearch($filter, $request, 'statistic_log');
                $res = data_formatter('category', $res);

            return $res;
        }

        /**
         * Get days on market summary by month range
         * @param month_range       Array of month range.   e.g. [[2020-11-01, 2020-11-30], [2020-10-01, 2020-10-31]]
         * @param purpose           Purpose of the request  e.g. 'days_on_market_city', 'days_on_market_zip', 'days_on_market_summary'
         * @param query_date        Date of the query
         * @param options           Additional filters
         */
        protected function get_day_on_market_summary(Array $month_range, String $purpose, String $query_date, Array $options = NULL)
        {
            $arr = [];
            foreach ($month_range as $date) {
    
                //  Dates
                    $begin_date =    $date[0];
                    $end_date =      $date[1];
    
                //  Filter to look for
                    $filter = array(
                        'QueryPurpose'  => $purpose,
                        'DateBegin'     => $begin_date,
                        'DateEnd'       => $end_date,
                    );
                
                //  Append options to filter, if any
                    if(isset($options)): foreach ($options as $k => $v) : $filter[$k] = $v; endforeach; endif;
    
                //  Data need to display
                    $request = [
                        'SFRDomPercent_0-30',
                        'SFRDomPercent_31-60',
                        'SFRDomPercent_61-90',
                        'SFRDomPercent_91-120',
                        'SFRDomPercent_121',
                        'TWHDomPercent_0-30',
                        'TWHDomPercent_31-60',
                        'TWHDomPercent_61-90',
                        'TWHDomPercent_91-120',
                        'TWHDomPercent_121'
                    ];
                
                //  Result as associated array
                    $res = $this -> retrieveSearch($filter, $request, 'statistic_log');
                    $res = data_formatter('dom', $res);
                
                //  Use month name as object name
                    $indexName = date("F", strtotime($begin_date));
                    $arr[$indexName] = $res;
            }
            return $arr;
        }

        /**
         * Get day on market periodic summary
         * @param week_range        Array of month range.   e.g. [[2020-11-01, 2020-11-30], [2020-10-01, 2020-10-31]]
         * @param purpose           Purpose of the request
         * @param query_date        Date of the query
         * @param periodic_type     Periodic type   e.g. '7', '30'
         * @param options           Additional filters
         */
        protected function get_day_on_market_periodic_summary(Array $week_range, String $purpose, String $query_date, String $periodic_type, Array $options = NULL)
        {

            //  Get request array
            $request = get_request_array($periodic_type);

            $arr = [];
            foreach ($week_range as $date) {
    
                //  Dates
                    $begin_date =    $date[0];
                    $end_date =      $date[1];
    
                //  Filter to look for
                    $filter = array(
                        'QueryPurpose'  => $purpose,
                        'DateBegin'     => $begin_date,
                        'DateEnd'       => $end_date,
                        'QueryDate'     => $query_date
                    );
                
                //  Append options to filter, if any
                    if(isset($options)): foreach ($options as $k => $v) : $filter[$k] = $v; endforeach; endif;
                
                //  Result as associated array
                    $res = $this -> retrieveSearch($filter, $request, 'statistic_log');
                    $res = data_formatter('dom_detailed', $res, $date);
                
                //  Use month name as object name
                    $indexName = date("F", strtotime($begin_date));
                    $arr[$indexName] = $res;
            }

            return $arr;
        }

    /*	==========================================================================================
        Comparison processor, compare two data set, and return the growth result
        ==========================================================================================	*/   
    
        /**
         * Get comparison result
         * @param target_value      Compare this value set with something
         * @param original_result   Compare some data with this value
         */
        protected function get_comparison_result(Array $target_result, Array $original_result)
        {
            $ULTIL = new Ultil();

            $result = [];
            
            //  Generate comparison
                foreach ($original_result as $category => $values) {

                    $temp = [];
                    foreach ($values as $n => $original_value) {
                        
                        $target_value = $target_result[$category][$n];

                        //  Clean data
                            $target_value =     (isset($target_value) && !is_null($target_value) && !empty($target_value)) ? $target_value : 0;
                            $original_value =   (isset($original_value) && !is_null($original_value) && !empty($original_value)) ? $original_value : 0;

                        //  calculate the precentage different
                            $diff =         $ULTIL -> getPercentageChange($target_value, $original_value);
                            $formatted =    $ULTIL -> roundNumberToPlaces($diff, 2) . "%";
                            $temp[$n] =     $formatted;
                    }
                
                    $result[$category] = $temp;
                }

            return $result;
        }
    }

/*	==========================================================================================
	Public functions
    ==========================================================================================	*/   
        
    /**
     * Get request array
     * @param periodic_type     Type of periodic. e.g. '7' or '30'
     */
    function get_request_array(string $periodic_type)
    {
        $ULTIL = new Ultil();

        //  $irr is irritation, $spacer is days between every irritation 
            switch($periodic_type){
                case '7':   $irr = 16;  $spacer = 7;    break;
                case '30':  $irr = 4;   $spacer = 30;   break;
            }

        //  Process data base on the irritation and spacer  
            $res =  $ULTIL -> generate_number_pair(0, $irr, $spacer);
            $pair = $ULTIL -> generate_dom_list_pair($res);

        $array = [];
        foreach ($pair as $k => $v) :
            $array[] = "SFRDomPercent_$v";
            $array[] = "TWHDomPercent_$v";
        endforeach;
        return $array;
    }

    /**
     * Data formatter
     * @param type          Type of formatter
     * @param dataset       Array of data
     */
    function data_formatter(String $type, Array $dataset, Array $date_range = NULL)
    {
        switch($type){
            case 'dom':
                return dom_formatter($dataset); break;
            case 'growth':
                return growth_formatter($dataset); break;
            case 'category':
                return category_formatter($dataset); break;
            case 'dom_detailed':
                return dom_detailed_formatter($dataset, $date_range); break;
        }
    }

    /**
     * Days on market data formatter
     * @param dataset       Result of query
     */
    function dom_detailed_formatter(Array $dataset, Array $date_range)
    {
        //  Structual of the format
            $blueprint = array( 'Date_range' => $date_range, 'SFR' => [], 'TWH' => [] );

        //  name: e.g. SFRDomPercent_0-30, SFRDomCount_61-90, SFRDomAverage_61-90
            foreach ($dataset as $name => $value) {

                $nameArr = explode('_', $name);                 //  [ 'SFRDomPercent', '0-30' ]
                $name_part = $nameArr[0];                       //  SFRDomCount

                $name_part_arr = explode('Dom', $name_part);    //  ['SFR', 'Count']
                $name_attr = $name_part_arr[1];                 //  Count, Percent, Average

                $day_range = $nameArr[1];                       //  0-30

                if(strpos($name, 'SFR') !== false){
                    $blueprint['SFR'][$day_range][$name_attr] = $value;
                }else{
                    $blueprint['TWH'][$day_range][$name_attr] = $value;
                }
            }

        return $blueprint;
    }

    /**
     * Days on market data formatter
     * @param dataset       Result of query
     */
    function dom_formatter(Array $dataset)
    {
        //  Structual of the format
            $blueprint = array( 'SFR' => [], 'TWH' => [] );

        //  name: e.g. SFRDomPercent_0-30
            foreach ($dataset as $name => $value) {

                $nameArr = explode('_', $name);     //  [ 'SFRDomPercent', '0-30' ]
                $day_range = $nameArr[1];           //  0-30

                if(strpos($name, 'SFR') !== false){
                    $blueprint['SFR'][$day_range] = $value;
                }else{
                    $blueprint['TWH'][$day_range] = $value;
                }
            }

        return $blueprint;
    }

    /**
     * Category formatter
     * @param dataset       Result of query
     */
    function category_formatter(Array $dataset)
    {
        //  Structual of the format
            $blueprint = array( 'SFR' => [], 'TWH' => [] );

        //  Store into structual.  e.g. SFRCount
            foreach ($dataset as $name => $value) {

                if(strpos($name, 'SFR') !== false){

                    $el = str_replace("SFR", "", $name);
                    $blueprint['SFR'][$el] = $value;

                }else{

                    $el = str_replace("TWH", "", $name);
                    $blueprint['TWH'][$el] = $value;
                }
            }

        return $blueprint;
    }

    /**
     * Growth formatter
     * @param dataset       Result of query
     */
    function growth_formatter(Array $dataset)
    {
        //  Structual of the format
            $blueprint = [
                'SFR' => [],
                'TWH' => [],
                'Growth' => [
                    'SFR' => [],
                    'TWH' => []
                ]
            ];

        //  Store into structual
            foreach ($dataset as $name => $value) {

                if(strpos($name, 'Growth') !== false){  //  Growth: e.g. SFRGrowthCount

                    if(strpos($name, 'SFR') !== false):
                        $el = str_replace("SFRGrowth", "", $name);
                        $blueprint['Growth']['SFR'][$el] = $value;
                    endif;
                    
                    if(strpos($name, 'TWH') !== false):
                        $el = str_replace("TWHGrowth", "", $name);
                        $blueprint['Growth']['TWH'][$el] = $value;
                    endif;

                }else{  //  Regular: e.g. SFRCount

                    if(strpos($name, 'SFR') !== false): 
                        $el = str_replace("SFR", "", $name);
                        $blueprint['SFR'][$el] = $value;
                    endif;
                    
                    if(strpos($name, 'TWH') !== false): 
                        $el = str_replace("TWH", "", $name);
                        $blueprint['TWH'][$el] = $value;
                    endif;
                }
            }

        //  
            return $blueprint;
    }

    /**
     * Token generator
     */
    function token_generator()
    {
        $ULTIL = new Ultil();
        $token = $ULTIL -> generateRandomId(64);
        return $token;
    }

    /**
     * Shorten the month name
     */
    function shorten_month(string $month_name)
    {
        switch ($month_name) {
            case 'January': return 'Jan'; break;
            case 'February': return 'Feb'; break;
            case 'March': return 'Mar'; break;
            case 'Apirl': return 'Apr'; break;
            case 'May': return 'May'; break;
            case 'June': return 'Jun'; break;
            case 'July': return 'Jul'; break;
            case 'August': return 'Aug'; break;
            case 'September': return 'Sep'; break;
            case 'October': return 'Oct'; break;
            case 'November': return 'Nov'; break;
            case 'December': return 'Dec'; break;
        }
    }
?>