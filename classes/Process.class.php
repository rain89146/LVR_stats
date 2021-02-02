<?php 

    /**
     * Process the data into desire format
     */
    class Process extends Ultil{

        /**
         * Populate the campairson result
         * @param month_range array of month range
         * @param callback callback function 
         * @return json json string of data
         */
        public function PopulateComparesionResult(string $QueryDate, string $type, object $callback, array $info, $dates = NULL)
        {
            /**
             * Base on different type, generate different report, and generates array to save to db
             * 
             * Periodic compare report: base on give array of month, loop through the dates;
             * Linear date compare report: compare given dates, and complare each result with the first element
             * Basic report: just calculate the basic statistic numbers
             */
            switch($type){
                case 'seriesDateCompareReport':
                    $result = $this->series_date_compare_report($QueryDate, $dates, $callback, $info);
                break;
                case 'linearDatesCompareReport':
                    $result = $this->linear_date_compare_report($QueryDate, $dates, $callback, $info);
                break;
                case 'seriesBasicReport':
                    $result = $this->series_basic_report($QueryDate, $dates, $callback, $info);
                break;
                case 'DomBasicReport':
                    $result = $this->dom_basic_report($QueryDate, $dates, $callback, $info);
                break;
                case 'DomDistributionReport':
                    $result = $this->dom_distribution_report($QueryDate, $dates, $callback, $info);
                break;
                case 'historicalCompareReport':
                    $result = $this->historical_compare_report($QueryDate, $dates, $callback, $info);
                break;
                case 'basicReport':
                    $result = $this->basic_report($QueryDate, $callback, $info);
                break;
            }
            return $result;
        }

        /**
         * Populate the custom campairson result
         * @param month_range array of month range
         * @param callback callback function 
         * @return json json string of data
         */
        public function PopulateComparesionResult_custom_dom(string $QueryDate, array $DOM_list_string, array $DOM_list, object $callback, array $info, $dates = NULL)
        {
            return $this->dom_distribution_report_custom($QueryDate, $dates, $callback, $info, $DOM_list_string, $DOM_list);
        }

        /**
         * Get DOM basic report
         * DOM sectional basic report
         * 
         * @param dates         Array of series of dates
         * @param callback      Callback function that take dates and info
         * @param info          Array of info about this info
         */
        protected function dom_basic_report(string $QueryDate, array $dates, object $callback, array $info)
        {
            if(isset($dates)):
                $holder = [];
                foreach ($dates as $date):

                    //  Init
                    $temp = [];

                    //  Generate query Id
                        $temp['QueryId'] =      $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] =    $date[0];
                        $temp['DateEnd'] =      $date[1];

                    //  Query time
                        $temp['QueryDate'] =    $QueryDate;

                    //  Request info
                        if(isset($info)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

                    //  Request data
                        $statis = $callback($date, $info);
                        
                    //  Extract data
                        $statisFormatted = $this->sectional_compare_formatted($statis);
                        $temp = array_merge($temp, $statisFormatted);

                    //  Append
                        $holder[] = $temp;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * Get DOM Distribution report (DomDistributionReport)
         * DOM sectional basic report
         * 
         * @param dates         Array of series of dates
         * @param callback      Callback function that take dates and info
         * @param info          Array of info about this info
         */
        protected function dom_distribution_report(string $QueryDate, array $dates, object $callback, array $info)
        {
            if(isset($dates)):
                $holder = [];
                foreach ($dates as $date):

                    //  Init
                    $temp = [];

                    //  Generate query Id
                        $temp['QueryId'] =      $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] =    $date[0];
                        $temp['DateEnd'] =      $date[1];

                    //  Query time
                        $temp['QueryDate'] =    $QueryDate;

                    //  Request info
                        if(isset($info)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

                    //  Request data
                        $statis = $callback($date, $info);
                        
                    //  Extract data
                        $statisFormatted = $this->detailed_sectional_compare_formatted($statis);
                        $temp = array_merge($temp, $statisFormatted);

                    //  Append
                        $holder[] = $temp;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * THIS CUSTOM REPORT
         */
        protected function dom_distribution_report_custom(string $QueryDate, array $dates, object $callback, array $info, array $DOM_list_string, array $DOM_list)
        {
            if(isset($dates)):
                $holder = [];
                foreach ($dates as $date):

                    //  Init
                    $temp = [];

                    //  Generate query Id
                        $temp['QueryId'] =      $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] =    $date[0];
                        $temp['DateEnd'] =      $date[1];

                    //  Query time
                        $temp['QueryDate'] =    $QueryDate;

                    //  Request info
                        if(isset($info)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

                    //  Request data
                        $statis = $callback($date, $info, $DOM_list);
                        
                    //  Extract data
                        $statisFormatted = $this->detailed_sectional_compare_formatted_custom($statis, $DOM_list_string);
                        $temp = array_merge($temp, $statisFormatted);

                    //  Append
                        $holder[] = $temp;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * Get historical compare report
         * Compare the current result with historical result
         * 
         * @param date          The initial date to start with
         * @param callback      Callback function that take dates and info
         * @param info          Array of info about this info
         */
        protected function historical_compare_report(string $QueryDate, string $date, object $callback, array $info)
        {
            $holder = [];

            //  Current data
                $current_statis = $callback($info);
                // var_dump($current_statis);

            //  A month and A year ago
                $monthAgo = $this->generate_date_by_offset($date, '-1 month');
                $yearAgo =  $this->generate_date_by_offset($date, '-1 year');

            //  Query by date
                $date_set = [$monthAgo, $yearAgo];
                foreach ( $date_set as $target_date ) {

                //  Assemble searching filter
                    $filters = [];
                    $filters['QueryDate'] = $target_date;
                    if(isset($info)): foreach ($info as $k => $v) : $filters[$k] = $v; endforeach; endif;

                //  History data
                    $view = new Viewlisting();
                    $history_statis = $view->getPreviousSavedStatistic($filters);

                //  Append results
                    $arr = [];
                    foreach ($current_statis as $category => $collection) {

                        $temp = [];
                        foreach ($collection as $k => $v) {

                            //  Retreieve the history data
                            $look_for = $category . ucfirst($k);
                            $found = $history_statis[$look_for];

                            //  Calculate the different between the current and history
                            $change = ($found) ? $this->getPercentageChange($v, $found) : 0;
                            $indexName = "Growth" . ucfirst($k);
                            $temp[$indexName] = $this->roundNumberToPlaces($change, 2) . "%";
                        }
                        $arr[$category] = $temp;
                    }
                    $holder[$target_date] = $arr;
                }

            return $holder;
        }

        /**
         * Get report from a series of dates
         * Compare the result with previous generated result
         * 
         * @param dates         Array of series of dates
         * @param callback      Callback function that take dates and info
         * @param info          Array of info about this info
         */
        protected function series_date_compare_report(string $QueryDate, array $dates, object $callback, array $info)
        {
            if(isset($dates)):
                $holder = []; $previous; $temp = []; $count = 0;
                foreach ($dates as $date):

                    //  Generate query Id
                        $temp['QueryId'] = $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] =    $date[0];
                        $temp['DateEnd'] =      $date[1];
                                        
                    //  Request info
                        if(isset($info)): 
                            foreach ($info as $k => $v): $temp[$k] = $v;  endforeach; 
                        endif;

                    //  Query time
                        $temp['QueryDate'] = $QueryDate;
                        
                    //  Request data
                        $statis = $callback($date, $info);

                    //  Extract data
                        $statisFormatted = $this->target_compare_formatted($statis, $count, $previous);
                        $temp = array_merge($temp, $statisFormatted);
                    
                    //  Push to all statistic array
                        $holder[] = $temp;

                    //  Increment, update previous every irritation
                        $previous = $statis; 
                        $count ++;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * Get linear report from a series of dates
         * Compare the result ONLY with the first result
         * 
         * @param dates         Array of series of dates
         * @param callback      Callback function that take dates and info
         * @param info          Array of info about this info
         */
        protected function linear_date_compare_report(string $QueryDate, array $dates, object $callback, array $info)
        {
            if(isset($dates)):
                $holder = []; $previous; $temp = []; $count = 0;
                foreach ($dates as $date):

                    //  Generate query Id
                        $temp['QueryId'] = $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] =    $date[0];
                        $temp['DateEnd'] =      $date[1];
                                        
                    //  Request info
                        if(isset($info)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

                    //  Query time
                        $temp['QueryDate'] = $QueryDate;
                        
                    //  Request data
                        $statis = $callback($date, $info);

                    //  Extract data
                        $statisFormatted = $this->target_compare_formatted($statis, $count, $previous);
                        $temp = array_merge($temp, $statisFormatted);
                    
                    //  Push to all statistic array
                        $holder[] = $temp;

                    //  Increment, only set the previous at the first element
                        if($count === 0): $previous = $statis; endif;
                        $count ++;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * Get basic report
         * Generate basic report based on the info provided
         * 
         * @param callback      Callback function that take info
         * @param info          Array of info about this report
         * @param report_type   Type of the report
         */
        protected function basic_report(string $QueryDate, object $callback, array $info)
        {
            $holder = []; $temp = [];
                    
            //  Request data
                $statis = $callback($info);
                
            //  Generate query Id
                $temp['QueryId'] = $this->generateRandomId(19);
            
            //  Request info
                if(isset($temp)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

            //  Query time
                $temp['QueryDate'] = $QueryDate;      
                
            //  Extract data
                $statisFormatted = $this->basic_compare_formatted($statis);
                $temp = array_merge($temp, $statisFormatted);

            //  Push to all statistic array
                $holder[] = $temp;
            
            return $holder;
        }

        /**
         * Get series basic report with no comparison
         * Generate a set of reports based on the dates provided
         * 
         * @param dates         Array of series of dates
         * @param callback      Callback function that takes dates and info
         * @param info          Array of info about this report
         * @param report_type   Type of the report
         */
        protected function series_basic_report(string $QueryDate, array $dates, object $callback, array $info)
        {
            if(isset($dates)):
                $holder = []; $temp = [];
                foreach ($dates as $date):

                    //  Generate query Id
                        $temp['QueryId'] = $this->generateRandomId(19);

                    //  Date
                        $temp['DateBegin'] = $date[0];
                        $temp['DateEnd'] = $date[1];
                                        
                    //  Request info
                        if(isset($info)): foreach ($info as $k => $v): $temp[$k] = $v; endforeach; endif;

                    //  Query time
                        $temp['QueryDate'] = $QueryDate;

                    //  Request data
                        $statis = $callback($date, $info);

                    //  Extract data
                        $statisFormatted = $this->basic_compare_formatted($statis);
                        $temp = array_merge($temp, $statisFormatted);
                    
                    //  Push to all statistic array
                        $holder[] = $temp;

                endforeach;
            endif;
            return $holder;
        }

        /**
         * Target compare formatted
         * Means compare the array with previous array of subject
         * @param dataset       A series of array data
         * @param count         The count flag
         * @param previous      The previous array of data
         */
        protected function target_compare_formatted(array $dataset, int $count, array $previous = NULL)
        {
            $temp = [];

            //  Append to temp
                foreach ($dataset as $k => $v):
                    $temp[$k."Count"] =     $this->roundNumberToPlaces($v['count'], 2);
                    $temp[$k."Median"] =    $this->roundNumberToPlaces($v['median'], 2);
                    $temp[$k."Average"] =   $this->roundNumberToPlaces($v['average'], 2);
                    $temp[$k."Total"] =     $this->roundNumberToPlaces($v['total'], 2);
                endforeach;

            //  Calculate the difference
                if ($count !== 0): 
                    foreach ($dataset as $k => $v):

                        //  Get the percentage change
                            $d_cnt = $this->getPercentageChange($previous[$k]['count'], $v['count']);
                            $d_ave = $this->getPercentageChange($previous[$k]['average'], $v['average']);
                            $d_med = $this->getPercentageChange($previous[$k]['median'], $v['median']);
                            $d_tot = $this->getPercentageChange($previous[$k]['total'], $v['total']);

                        //  Store the data
                            $temp[$k."GrowthCount"] =   $this->roundNumberToPlaces($d_cnt, 2) . "%";
                            $temp[$k."GrowthMedian"] =  $this->roundNumberToPlaces($d_med, 2) . "%";
                            $temp[$k."GrowthAverage"] = $this->roundNumberToPlaces($d_ave, 2) . "%";
                            $temp[$k."GrowthTotal"] =   $this->roundNumberToPlaces($d_tot, 2) . "%";

                    endforeach;
                endif;
        
            return $temp;
        }

        /**
         * Section formatted
         * Means formatted the data into different sections, DOM only
         * @param dataset       A series of array data
         */
        protected function sectional_compare_formatted(array $dataset)
        {
            $holder = [];
            foreach ($dataset as $category => $collection) {
                $DOMlist = [ '0-30', '31-60', '61-90', '91-120', '121' ];
                $count = 0;
                foreach ($collection as $key => $valueset) {
                    foreach ($valueset as $key => $value) {
                        if($key === 'percent'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                    }
                    $count ++;
                }
            }
            return $holder;
        }

        /**
         * Detailed section formatted
         * Means formatted the data into different fine sections, DOM only
         * @param dataset       A series of array data
         */
        protected function detailed_sectional_compare_formatted(array $dataset)
        {
            $holder = [];
            foreach ($dataset as $category => $collection) {
                $DOMlist = [ '0-30', '31-60', '61-90', '91-120', '121' ];
                $count = 0;
                foreach ($collection as $key => $valueset) {
                    foreach ($valueset as $key => $value) {
                        if($key === 'percent'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                        if($key === 'average'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                        if($key === 'count'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                    }
                    $count ++;
                }
            }
            return $holder;
        }

        /**
         * CUSTOM Detailed section formatted
         * Means formatted the data into different fine sections, DOM only
         * @param dataset       A series of array data
         */
        protected function detailed_sectional_compare_formatted_custom(array $dataset, array $DOM_list_string)
        {
            $holder = [];
            foreach ($dataset as $category => $collection) {
                $DOMlist = $DOM_list_string;
                $count = 0;
                foreach ($collection as $key => $valueset) {
                    foreach ($valueset as $key => $value) {
                        if($key === 'percent'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                        if($key === 'average'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                        if($key === 'count'):
                            $name = $category . "Dom" . ucfirst($key) . "_" . $DOMlist[$count];
                            $holder[$name] = $value;
                        endif;
                    }
                    $count ++;
                }
            }
            return $holder;
        }

        /**
         * Basic formatted
         * Means formatted the data into basic avg, sum, med, count format
         * @param dataset       A series of array data
         */
        protected function basic_compare_formatted(array $dataset)
        {
            $temp = [];
            foreach ($dataset as $k => $v):
                $temp[$k."Count"] =     $this->roundNumberToPlaces($v['count'], 2);
                $temp[$k."Median"] =    $this->roundNumberToPlaces($v['median'], 2);
                $temp[$k."Average"] =   $this->roundNumberToPlaces($v['average'], 2);
                $temp[$k."Total"] =     $this->roundNumberToPlaces($v['total'], 2);
            endforeach;
            return $temp;
        }
    }
?>