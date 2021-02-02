<?php
    require_once './vendor/autoload.php';
    use League\Csv\Reader;
    use League\Csv\Statement;

    //  LISTING CLASS
    class Listing extends DBconn {

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * Import CSV file into database (IMPORT DATA)
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Parse the csv file
         * @param root_path     The root path of the file
         * @param table_name    Table name would like to insert to
         */
        protected function parse_csv_data(string $root_path, string $table_name)
        {
            //  get the files
                $FILES = scandir($root_path);
                $FILES = array_diff(scandir($root_path), array('.', '..'));

            //  scan each file and store into contaner
                $container = [];
                foreach($FILES as $file):

                    //  Get the complete path
                        $COMPLETE_PATH = $root_path . $file;

                    //  Process each listing in the CSV file
                        clearstatcache();
                        if(filesize($COMPLETE_PATH)):

                            $FILE_CONTENT = file_get_contents($COMPLETE_PATH); 
                            $COLUMN_NAME_COUNT = 0;

                            while($dup_pos = strpos($FILE_CONTENT, "()")) {
                                $FILE_CONTENT = substr_replace($FILE_CONTENT, "UNKNOWN" . $COLUMN_NAME_COUNT, $dup_pos, 2);
                                $COLUMN_NAME_COUNT += 1;
                            }
                            file_put_contents($COMPLETE_PATH, $FILE_CONTENT);

                            //  pull csv data
                            $csv = Reader::createFromPath($COMPLETE_PATH, 'r');
                            $csv -> setHeaderOffset(0);
                            $stmt = (new Statement()) -> offset(0);

                            //process each listing entry
                            $LISTINGS = $stmt -> process($csv);
                            $data_obj = array(
                                'status' => true,
                                'tb_name' => $table_name,
                                'data' => $LISTINGS
                            ); 

                            //  store into container
                            array_push($container, $data_obj);              
                        endif;
                endforeach;

            //  return
            return $container;
        }

        /**
         * Parse the csv file
         * @param root_path     The root path of the file
         * @param table_name    Table name would like to insert to
         */
        protected function parse_csv_data_indivisual(string $COMPLETE_PATH, string $table_name)
        {
            clearstatcache();
            if(filesize($COMPLETE_PATH)):

                $FILE_CONTENT = file_get_contents($COMPLETE_PATH); 
                $COLUMN_NAME_COUNT = 0;

                while($dup_pos = strpos($FILE_CONTENT, "()")) {
                    $FILE_CONTENT = substr_replace($FILE_CONTENT, "UNKNOWN" . $COLUMN_NAME_COUNT, $dup_pos, 2);
                    $COLUMN_NAME_COUNT += 1;
                }
                file_put_contents($COMPLETE_PATH, $FILE_CONTENT);

                //  pull csv data
                $csv = Reader::createFromPath($COMPLETE_PATH, 'r');
                $csv -> setHeaderOffset(0);
                $stmt = (new Statement()) -> offset(0);

                //process each listing entry
                $LISTINGS = $stmt -> process($csv);
                $data_obj = array(
                    'status' => true,
                    'tb_name' => $table_name,
                    'data' => $LISTINGS
                ); 

                return $data_obj;
            endif;
        }

        /**
         * Populate data into database
         * @param listing       Lisitng were provided by parse_csv_data function
         */
        protected function populate_data($listings)
        {
            if(count($listings)):

            //  Category layer
                foreach ($listings as $key => $data):
                    
                //  Get the data
                    $data_status =      $data['status'];
                    $listing_data =     $data['data'];
                    $table_name =       $data['tb_name'];
                    
                    if($data_status) {
        
                    //  Listing layer
                        foreach ($listing_data as $listing) {
        
                        //  Check if listing already exists
                            $matrix_id = $listing["Matrix Unique ID"];
                            
                        //  Store listing, flat table structure
                            foreach ($listing as $key_name => $key_value) {
        
                                //  if the value is not empty
                                if(!empty($key_value)){
        
                                    //  clean the string
                                    $key_name = preg_replace("/\s+/", "", $key_name);
        
                                    //  Insert to dababase
                                    $success = $this -> insert_data($matrix_id, $table_name, $key_name, $key_value);
                                    echo $success . "<br>";
                                }
                            }   
                        }  
        
                    }
                endforeach;
            endif;
        }

        /**
         * Populate data into database
         * @param listing       Lisitng were provided by parse_csv_data function
         */
        protected function populate_data_individual($data)
        {
            $data_status =      $data['status'];
            $listing_data =     $data['data'];
            $table_name =       $data['tb_name'];
            
            if($data_status) :

                $count = 0;
                $total = count($listing_data);

                //  Listing layer
                    foreach ($listing_data as $listing) {

                    //  Check if listing already exists
                        $matrix_id = $listing["Matrix Unique ID"];
                        
                    //  Store listing, flat table structure
                        foreach ($listing as $key_name => $key_value) {

                            //  if the value is not empty
                            if(!empty($key_value)){

                                //  clean the string
                                $key_name = preg_replace("/\s+/", "", $key_name);
                                $success = $this -> insert_data($matrix_id, $table_name, $key_name, $key_value);
                                echo ($success) ? '*' : '';
                            }
                        }
                        
                        $count ++;
                    }  

                //  When it's done callback
                    return ($count === $total) ? 1 : 0;

            endif;
        }

        /**
         * Insert data into database
         * @param matix_id      Matrix id
         * @param table_name    Table name
         * @param key_name      Key name
         * @param key_value     key value
         */
        protected function insert_data(string $matrix_id, string $table_name, string $key_name, string $key_value) 
        {
            $sql = "INSERT INTO $table_name ( matrix_id, field, value ) VALUES ( ?, ?, ? )";
            $param = array($matrix_id, $key_name, $key_value);

            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql, $param);
                    $this -> disconnect($conn);

            return ($stmt) ? true : false;
        }

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * Security
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Account authorization
         * @param ip                Ip address of the device
         * @param user              User name
         * @param pass              Password 
         */
        protected function account_auth(string $ip, string $user, string $pass)
        {
            $sql = "SELECT row_id, ip_address, us_name, us_passw, token FROM auth WHERE ip_address = ? AND us_name = ? AND us_passw = ?";
            $param = array($ip, $user, $pass);
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql, $param);
                    $this -> disconnect($conn);

            $res = false;
            while($row = $stmt -> fetch()):
                $res = array(
                    'id' => $row['row_id'],
                    'ip' => $row['ip_address'],
                    'us' => $row['us_name'],
                    'ps' => $row['us_passw'],
                    'tk' => $row['token']
                );
            endwhile;
            return $res;
        }

        /**
         * Insert token into the auth table
         * @param token         Token
         * @param row_id        row id
         */
        protected function insert_token(string $token, string $row_id)
        {
            $sql = "UPDATE auth SET token = ? WHERE row_id =  ?";
            $param = array($token, $row_id);
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql, $param);
                    $this -> disconnect($conn);
            return ($stmt) ? true : false;
        }

        /**
         * Token verification
         * @param token         Token
         */
        protected function token_verification(string $token)
        {
            $sql = "SELECT row_id FROM auth WHERE token = ?";
            $param = array($token);
            
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql, $param);
                    $this -> disconnect($conn);

            $res = false;
            while($row = $stmt -> fetch()): if($row['row_id']): $res = true; endif; endwhile;
            return $res;
        }

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * Query the request
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Filter search with criteria
         * @param criteria      Searching criteria
         * @param table_name    Table name
         * @param look_for      Coloumn that is looking for
         */
        protected function filter_search_with_criteria(array $criteria, string $table_name, string $look_for)
        {
            if(is_array($criteria)):

                //  Total criteria and keys name
                    $tc = count($criteria);
                    $keys = array_keys( $criteria );

                //  Init sql statement
                    $sql = "SELECT $look_for ";
                    $sql .= "FROM ( SELECT T1.matrix_id, ";
                    $sql .= sql_inner_join_select_statement($tc, $keys);
                    $sql .= sql_inner_join_statement($table_name, 'matrix_id', $tc);
                    $sql .= sql_inner_join_where_clause($tc, $keys);
                    $sql .= " WHERE ";
                    $sql .= sql_condition_statemment($criteria, $tc);
                    $sql .= " ORDER BY FLOOR($look_for) DESC";
                    // echo $sql . "<br><br>";

                //  Statement, param, and execute
                    $conn = $this -> connect(); 
                    $stmt = $this -> query($conn, $sql);
                            $this -> disconnect($conn);

                //  Format the result into array
                    $arr = construct_common_array($stmt, $look_for);                
                    return get_allreport($arr);
            endif;
        }

        /**
         * Get sqft price statistic
         * @param criteria      Searching criteria
         * @param table_name    Table name
         * @param price_type    price type, if it's sold listing, then ListPrice, otherwise ClosePrice
         */
        protected function get_sqft_statistic(array $criteria, string $table_name, string $price_type) 
        {
            if(is_array($criteria)):

                //  Total criteria and keys name
                    $tc = count($criteria);
                    $keys = array_keys( $criteria );

                //  Init sql statement
                    $sql = "SELECT ( $price_type / SqFtTotal ) AS SqPrice ";
                    $sql .= "FROM ( SELECT T1.matrix_id, ";
                    $sql .= sql_inner_join_select_statement($tc, $keys);
                    $sql .= sql_inner_join_statement($table_name, 'matrix_id', $tc);
                    $sql .= sql_inner_join_where_clause($tc, $keys);
                    $sql .= " WHERE ";
                    $sql .= sql_condition_statemment($criteria, $tc);
                    $sql .= " ORDER BY FLOOR(SqPrice) DESC";
                // echo $sql . "<br><br>";

                //  Statement, param, and execute
                    $conn = $this -> connect(); 
                    $stmt = $this -> query($conn, $sql);
                            $this -> disconnect($conn);
                    $arr = construct_common_array($stmt, 'SqPrice');
                return get_allreport($arr);
            endif;
        }

        /**
         * New listing statistic
         * @param criteria      First part of the criteria
         * @param criteria2     Second part of the criteria
         * @param table_name    Table name
         * @param look_for      Field_name
         */
        protected function new_listing_statistic(array $criteria, array $criteria2, string $table_name, string $look_for)
        {
            if(is_array($criteria) && is_array($criteria2)):

                //  Total criteria and keys name
                    $tc = count( $criteria );
                    $keys = array_keys( $criteria );
                    $tc2 = count( $criteria2 );
                    $keys2 = array_keys( $criteria2 );

                //  Init
                    $sql = "SELECT $look_for FROM ( SELECT T1.matrix_id AS matrix_id, ";
                    $sql .= sql_inner_join_select_statement($tc, $keys);
                    $sql .= sql_inner_join_statement($table_name, 'matrix_id', $tc);
                    $sql .= sql_inner_join_where_clause($tc, $keys);
                    $sql .= " WHERE ";
                    $sql .= sql_condition_statemment($criteria, $tc);
                    $sql .= " AND matrix_id NOT IN ( SELECT matrix_id FROM ( SELECT T1.matrix_id AS matrix_id, ";
                    $sql .= sql_inner_join_select_statement($tc2, $keys2);
                    $sql .= sql_inner_join_statement($table_name, 'matrix_id', $tc2);
                    $sql .= sql_inner_join_where_clause($tc2, $keys2);
                    $sql .= " WHERE ";
                    $sql .= sql_condition_statemment($criteria2, $tc2);
                    $sql .= " )";
                    $sql .= " ORDER BY FLOOR($look_for) DESC";

                //  statement, param, and execute
                    $conn = $this -> connect();
                    $stmt = $this -> query($conn, $sql);
                            $this -> disconnect($conn);
            
                    $arr = construct_common_array($stmt, $look_for);
            
                return get_allreport($arr);
            endif;
        }

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * Delete data from the table
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Delete data from table
         * @param table_name
         */
        protected function delete_data_from_table(string $table_name)
        {
            $sql = "DELETE FROM $table_name";
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);
            return ($stmt) ? true : false;
        }

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * SAVE statistic to the statistic_log
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Retreive inserted data from data base
         * @param criteria      Search criteria / field name list 
         * @param table_name    Table name
         */
        protected function retrieve_query_id(array $criteria, string $table_name)
        {
            if(is_array($criteria)):

                //  Build query
                    $totalCount =   count($criteria);
                    $keys =         array_keys($criteria);

                //  Sql statement
                    $sql = "SELECT QueryId ";
                    $sql .= "FROM ( SELECT T1.query_id AS QueryId, ";
                    $sql .= sql_inner_join_select_statement($totalCount, $keys);
                    $sql .= sql_inner_join_statement($table_name, 'query_id', $totalCount);
                    $sql .= sql_inner_join_where_clause($totalCount, $keys);
                    $sql .= " WHERE ";
                    $sql .= sql_condition_statemment($criteria, $totalCount);

                //  Connect && Disconnect
                    $conn = $this -> connect();
                    $stmt = $this -> query($conn, $sql);
                            $this -> disconnect($conn);

                //  Extract value
                    $res = '';
                    while($row = $stmt -> fetch()){ $res = $row['QueryId']; }
                    return $res;
            endif;
        }

        /**
         * Retrieve value by query id
         * @param look_for      The field name that look for
         * @param query_id      The query_id of this item
         */
        protected function retrieve_value_by_query_id(string $look_for, string $query_id)
        {
            //  Sql statement
                $sql = "SELECT value FROM statistic_log WHERE query_id = ? AND field = ?";
                $param = array($query_id, $look_for);

            //  Connect && Disconnect
                $conn = $this -> connect();
                $stmt = $this -> query($conn, $sql, $param);
                        $this -> disconnect($conn);

            //  Fetch data  
                $res = '';
                while($row = $stmt -> fetch()): $res = $row['value']; endwhile;
                return $res;
        }

        /**
         * Save statistic data
         * @param query_id      Query id
         * @param table_name    Table name
         * @param key_name      Field
         * @param key_value     Value
         */
        protected function save_statistic_data(string $query_id, string $table_name, string $key_name, string $key_value)
        {
            $sql = "INSERT INTO $table_name ( query_id, field, value ) VALUES ( ?, ?, ? )";
            $param = array($query_id, $key_name, $key_value );
            
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql, $param);
                    $this -> disconnect($conn);
            
            return $stmt;
        }

    /** 
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     * GENERATE PostalCode and City list
     * ------------------------------------------------------------------------------------------------------------------------------------------------------
     */

        /**
         * Generate postal code list
         */
        protected function generate_postal_code_list()
        {
            $sql = "SELECT PostalCode FROM zip_list";
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);

            $list = [];
            while($row = $stmt -> fetch()): $list[] = $row['PostalCode']; endwhile;
            return $list;
        }

        /**
         * Generate city name list
         */
        protected function generate_city_value_list()
        {
            $sql = "SELECT city_name FROM city_list";
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);
            
            $list = [];
            while ($row = $stmt -> fetch()): $list[] = $row['city_name']; endwhile;
            return $list;
        }

        /**
         * Generate city name list
         * @param type          Name of the row. e.g. name, value
         */
        protected function generate_city_name_list_by_type(string $type)
        {
            
            $row_name = "";
            switch ($type) {
                case 'name':    $row_name = 'city_name';    break;                
                case 'value':   $row_name = 'city_value';   break;
            }

            $sql = "SELECT city_name, city_value FROM city_list";
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);
            
            $list = [];
            while ($row = $stmt -> fetch()): $list[] = $row[$row_name]; endwhile;
            return $list;
        }


        /**
         * Generate city name list
         */
        protected function generate_city_list()
        {
            $sql = "SELECT city_name, city_value FROM city_list";
            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);
            
            $list = [];
            while ($row = $stmt -> fetch()): 
                $list[] = array(
                    'name' => $row['city_name'], 
                    'value' => $row['city_value']
                ); 
            endwhile;
            return $list;
        }

        /**
         * Get the data list by given field name
         * @param table_name    Table name
         */
        protected function get_city_zip_list(string $table_name)
        {
            $sql = "SELECT DISTINCT T1.value AS PostalCode, T2.value AS City ";
            $sql .= "FROM $table_name T1 JOIN $table_name T2 USING ( matrix_id ) ";
            $sql .= "WHERE T1.field = 'PostalCode' AND T2.field = 'City'";

            $conn = $this -> connect();
            $stmt = $this -> query($conn, $sql);
                    $this -> disconnect($conn);

            $list = [];
            while($row = $stmt -> fetch()):
                $list[] = array(
                    $row['PostalCode'],
                    $row['City']
                );
            endwhile;

            return $list;
        }
    }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * SQL statement prepare
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */

    /**
     * Generate sql condition statement
     * @param criteria          Array list of filter options
     * @param totalCount        Count of total criteria
     */
    function sql_condition_statemment(array $criteria, int $totalCount)
    {
        //  Init
            $sql = "";

        //  Rendr base on condition
            $count = 0;
            foreach ($criteria as $filter => $value) {

                $str = (is_array($value)) 
                ? objectSearchHandle($filter, $value)
                : "$filter = '$value'";
                
                $is_not_last = ($totalCount - 1 != $count);

                if($str):
                    $sql .= $str;
                    $sql .= ($is_not_last) ? " AND " : "";
                endif;

                $count ++;
            }
        //  Return
            return $sql;
    }

    /**
     * Generate sql inner join from statement
     * @param table_name        Table name
     * @param count             Count of total criteria
     */
    function sql_inner_join_statement(string $table_name, string $coloum_name, int $count)
    {
        $sql = " FROM " . $table_name . " AS T1 ";
        for($i = 1; $i < $count; $i++){

            $t_index = $i + 1;
            $t_name = "T$t_index";
            
            $sql .= "INNER JOIN $table_name AS $t_name USING ( " . $coloum_name . " ) ";
        }
        return $sql;
    }

    /**
     * Generate sql inner join select statement
     * @param count 
     * @param keys
     */
    function sql_inner_join_select_statement(int $count, array $keys)
    {
        $sql = "";
        for($i = 0; $i < $count; $i++ ) {

            $t_index = $i + 1;
            $t_name = "T$t_index";

            $sql .= $t_name . ".value AS " . $keys[$i];
            $sql .= ($i != $count - 1) ? ", " : "";
        }
        return $sql;
    }

    /**
     * Generate sql inner join select statement
     * @param count 
     * @param keys
     */
    function sql_select_statement(int $count, array $keys)
    {
        $sql = "";
        for($i = 0; $i < $count; $i++ ) { $sql .= ($i != $count - 1) ? $keys[$i] . ", " : $keys[$i] . " "; }
        return $sql;
    }

    /**
     * Generate sql inner join where clause
     * @param count 
     * @param keys
     */
    function sql_inner_join_where_clause(int $count, array $keys)
    {
        $sql = " WHERE ";
        for($i = 0; $i < $count; $i++ ) {

            $t_index = $i + 1;
            $t_name = "T$t_index";

            $sql .= "$t_name.field = '" . $keys[$i];
            $sql .= ($i != $count - 1) ? "' AND " : "'";
        }
        $sql .= ") TmpTable";
        return $sql;
    }

    /**
     * Search object handle
     * @param coloum            Coloumn name
     * @param value             Filter value
     */
    function objectSearchHandle(string $coloum, array $value)
    {
        //  If the object is request to compare the date
            if(array_key_exists('date_compare', $value)):
                return getDateSQLstmnt($coloum, $value, 'date_compare', 'Date'); endif;
        
        //  If the object is request to compare the date
            if(array_key_exists('query', $value)):
                return getDateSQLstmnt($coloum, $value, 'query'); endif;

        //  If the object is request to compare the number
            if(array_key_exists('number_compare', $value)):
                return getDateSQLstmnt($coloum, $value, 'number_compare', 'Number'); endif;
        
        //  If the object is request to compare the price
            if(array_key_exists('price_compare', $value)):
                $allowed_price_filter = ['ListPrice', 'ClosePrice', 'CurrentPrice', 'LastListPrice', 'LeasePrice', 'OriginalListPrice', 'RentedPrice'];
                return (in_array($coloum, $allowed_price_filter)) ? getDateSQLstmnt($coloum, $value, 'price_compare') : ""; endif;
        
            //  If none above, but is also array as well.
            return $coloum . " IN (" . arrayIntoString_sql($value) . " )";
    }

    /**
     * Generate dynamic sql statement
     * @param coloumn           Name of the coloumn
     * @param arr               Object of values
     * @param compare_name      Command
     */
    function getDateSQLstmnt(string $coloum, array $arr, string $compare_name, string $type = NULL)
    {
        $compare =  $arr[$compare_name];
        $start =    (isset($arr['start'])) ?    $arr['start'] : "";
        $end =      (isset($arr['to'])) ?       $arr['to'] : "";

        switch ($compare) {
            case 'Exact':       
                return (is_numeric($start)) ? "$coloum = $start"  : "$coloum = '$start'";       
            break;
            case 'LessThan':   
                return (is_numeric($start)) ? "$coloum < $start" : "$coloum < '$start'";      
            break;
            case 'LessEqualThan':   
                return (is_numeric($start)) ? "$coloum <= $start" : "$coloum <= '$start'";      
            break;
            case 'MoreThan':    
                return (is_numeric($start)) ? "$coloum > $start" : "$coloum > '$start'";      
            break;
            case 'MoreEqualThan':    
                return (is_numeric($start)) ? "$coloum >= $start" : "$coloum >= '$start'";      
            break;
            case 'None':        
                return "";                                                                      
            break;
            case 'Between': 
                switch($type){
                    case 'Number': return "$coloum between $start and $end"; break;
                    case 'Date': return "Date($coloum) between '$start' and '$end'"; break;
                }
            break;
            case 'coloumn_notequal_month':
                return "MONTH($start) != MONTH($end)"; 
            break;
        }
    }

    /**
     * Convert array into string for SQL statement
     * @param arr array
     */
    function arrayIntoString_sql(array $arr)
    {
        $str = "";
        foreach ($arr as $k => $v) {
            $str .= (count($arr) - 1 != $k)
            ? "'". $v . "', "
            : "'". $v . "'";
        }
        return $str;
    }

    /**
     * Construct common associate array
     * @param stmt              Sql result statement
     * @param rowName           Row name
     */
    function construct_common_array($stmt, $rowName)
    {
        $arr = [];
        while($row = $stmt -> fetch()): $arr[] = $row[$rowName]; endwhile;
        return $arr;
    }

/** 
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 * Calculate the result
 * ------------------------------------------------------------------------------------------------------------------------------------------------------
 */

    /**
     * Get all report, including count, average, sum, and median
     * @param arr array of numbers
     */
    function get_allreport(array $arr)
    {
        //  total numbers in array
            $count = count($arr);
            $total = ($count) ? get_sum($arr) : 0;
            $median = ($count) ? get_median($arr) : 0;
            $average = ($count) ? $total / $count : 0;

        return array(
            'median'=>      $median,
            'count'=>       $count,
            'average' =>    $average,
            'total' =>      $total
        );
    }

    /**
     * Get the sum
     * @param arr array of numbers
     */
    function get_sum(array $arr)
    {
        $total = 0;
        foreach ($arr as $key => $value) {
            $total += floatval($value);
        }
        return $total;
    }

    /**
     * Get the median
     * @param arr array of numbers
     */
    function get_median(array $arr)
    {
        $count = count($arr);
        $middleval = floor(($count-1)/2);
        if($count % 2) {
            $median = $arr[$middleval];
        } else {
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }
?>