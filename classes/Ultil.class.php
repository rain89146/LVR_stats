<?php
    class Ultil {

        /**
         * Generate dom list
         * @param list          List of the dom
         */
        public function generate_dom_list(array $list)
        {
            $total = count($list);
            $last_index = $total - 1;

            $last_item = $list[$last_index];
            $last_number = $last_item[1];

            $arr = [];
            for ($i = 0; $i < $total; $i++) {
                $arr[] = array(
                    'number_compare' => 'Between',
                    'start' => $list[$i][0],
                    'to' => $list[$i][1]
                );    
            }
            $arr[] = array(
                'number_compare' => 'MoreEqualThan',
                'start' => $last_number + 1
            );

            return $arr;
        }

        /**
         * Generate number pair
         * @param start         Start number
         * @param times         Times of irritation
         * @param spacer        Number between each irritation
         */
        public function generate_number_pair(int $start, int $times, int $spacer)
        {
            $arr = [];
            $temp = 0;
            for($i = 0; $i < $times; $i++){

                $begin_num = ($i === 0) 
                    ? $start
                    : $temp + 1;
                
                $end_num = $begin_num + $spacer;
                $temp = $end_num;

                $arr[] = [$begin_num, $end_num];
            }
            return $arr;
        }

        /**
         * Generate dom list pair
         * @param list          List of the dom
         */
        public function generate_dom_list_pair(array $list)
        {
            $total = count($list);
            $last_index = $total - 1;

            $last_item = $list[$last_index];
            $last_number = $last_item[1];

            $arr = [];
            for ($i = 0; $i < $total; $i++) {
                $begin = $list[$i][0];
                $end = $list[$i][1];
                $arr[] = "$begin-$end";
            }

            $last_number++;
            $arr[] = "$last_number";
            return $arr;
        }

        /**
         * Get date range by given options
         * @param today         Today's date
         * @param type          type, e.g. wholeMonth_m / wholeWeek_w
         * @param unit_in_advance The unit of time in advance, e.g. if is in month the this unit is in month
         * @return range        Returns the array of date range
         */
        public function getDateRange(string $today, string $type, int $unit_in_advance = NULL)
        {
            //  Minimum unit in advance is 2
            $unit_in_advance = ($unit_in_advance) ? $unit_in_advance : 2;

            /**
             * wholeMonth_m: return whole month range, unit in month
             * wholeWeek_w: return whole week range, unit in week
             * wholeDay_m: return whole month range, unit in month, by given date
             */
            switch($type) {
                case 'wholeMonth_m': return $this->generate_month_range_whole_month($today, $unit_in_advance); break;
                case 'wholeWeek_w' : return $this->generate_week_range_whole_week($today, $unit_in_advance); break;
                case 'wholeWeek_h' : return $this->generate_week_range_ss_week($today, $unit_in_advance); break;
            }
        }
        
        /**
         * Generate month range whole month, in unit of month
         * @param date          Today's date
         * @param months        Number of months in advance
         */
        protected function generate_month_range_whole_month(string $date, int $months)
        {
            //  Get the last date of this month
                $last_date_of_this_month = date("Y-m-t", strtotime($date));
    
            //  Check the date is within the month or not
                $isFullMonth = ($date <= $last_date_of_this_month) ? false : true;
    
            //  Generate date range
                $date_range = [];
                for($i = 0; $i < $months; $i++):
    
                    //  calc the diff
                        $diff = ($isFullMonth == false) ? 1 : 0;
                        $n = $i + $diff;
    
                    //  base
                        $base = strtotime("-$n months", strtotime($date));
                        $base = date('Y-m-d', $base);
    
                    //  get the last date of this month
                        $begin = date('Y-m-01', strtotime($base));
                        $end = date("Y-m-t", strtotime($base));
    
                    //  Append
                        $date_range[] = [$begin, $end];
                endfor;
            return array_reverse($date_range);
        }

        /**
         * Generate week range by whole week, in unit of week
         * @param date          Today's date
         * @param weeks         Number of weeks in advance
         */
        protected function generate_week_range_whole_week(string $date, int $weeks)
        {
            //  get the offset, last week sunday
                $offset = date('w', strtotime($date));

            //  Init the day, last week sunday
                $init_day = date('Y-m-d', strtotime("-$offset day", strtotime($date)));

            //  
            $date_range = [];
            $p;
            for($i = 0; $i < $weeks; $i ++) {

                //  base
                    $base = ($i == 0) 
                        ? date('Y-m-d', strtotime("-$i week", strtotime($init_day))) 
                        : date('Y-m-d', strtotime("-1 day", strtotime($p)));
                
                //  end
                    $end = date("Y-m-d", strtotime("-6 days", strtotime($base)));
                    $p = $end;
                
                //  Append
                    $arr = [$end, $base];
                    $arr = $this->return_sorted_array($arr);
                    $date_range[] = $arr;
            }
            return array_reverse($date_range);
        }

        /**
         * Generate week range by whole week, in unit of week
         * @param date          Today's date
         * @param weeks         Number of weeks in advance
         */
        protected function generate_week_range_ss_week(string $date, int $weeks)
        {
            //  get the offset, last week sunday
                $offset = date('w', strtotime($date));

            //  Init the day, last week sunday
                $init_day = date('Y-m-d', strtotime("-$offset day", strtotime($date)));

            //  
            $date_range = [];
            $p;
            for($i = 0; $i < $weeks; $i ++) {

                //  base
                    // $base = ($i == 0) 
                    //     ? date('Y-m-d', strtotime("-$i week", strtotime($init_day))) 
                    //     : date('Y-m-d', strtotime("-1 day", strtotime($p)));
                    $base =  date('Y-m-d', strtotime("-$i week", strtotime($init_day)));
                // //  end
                //     $end = date("Y-m-d", strtotime("-6 days", strtotime($base)));
                //     $p = $end;

                //  Last Sunday and this Saturday;
                    $l_s = date('Y-m-d', strtotime("Sunday last week", strtotime($base)));
                    $t_s = date('Y-m-d', strtotime("Saturday this week", strtotime($base)));
                
                //  Append
                    $arr = [$l_s, $t_s];
                    $arr = $this->return_sorted_array($arr);
                    $date_range[] = $arr;
            }
            return array_reverse($date_range);
        }

        /**
         * Generate date by offset
         * @param today         Today's date
         * @param offset        Offset in string, e.g. - 3 months / + 3 weeks
         */
        public function generate_date_by_offset(string $today, string $offset)
        {
            return date('Y-m-d', strtotime("$offset", strtotime($today)));
        }

        /**
         * Calculates in percent, the change between 2 numbers.
         * @param oNum          The initial value
         * @param nNum          The value that changed
         */
        public function getPercentageChange($oNum, $nNum)
        {
            $oNum = (isset($oNum) && !is_null($oNum) && !empty($oNum)) ? $oNum : 0;
            $nNum = (isset($nNum) && !is_null($nNum) && !empty($nNum)) ? $nNum : 0;
            $dValue = $nNum - $oNum;
            return ($oNum !== 0) ? ($dValue / $oNum) * 100 : 0;
        }

        /**
         * Round number to certain decimal places
         * @param num           A number that is about to be rounded
         * @param decimals      Decimal places of the number to round 
         */
        public function roundNumberToPlaces(float $num, int $decimals)
        {
            return ($num) ? number_format((float)$num, $decimals, '.', '') : 0;
        }

        /**
         * Check if the value is a date or not
         * @param date          Date in string
         */
        public function check_if_is_date(string $date)
        {
            return (DateTime::createFromFormat('Y-m-d', $date) !== FALSE) ? true : false;
        }

        /**
         * Generate random id
         * @param length        Desired length
         */
        public function generateRandomId(int $length)
        {
            $keys = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
            return substr(str_shuffle($keys), 0, $length); 
        }

        /**
         * Return sorted array
         * @param new_arr       Array of item needs to be sorted
         */
        public function return_sorted_array($new_arr)
        {
            // Input Array 
            $arr = $new_arr;
            
            // sort array with given user-defined function 
            usort($arr, array("Ultil", "smaller_date_goes_first"));
            return $arr; 
        }

        /**
         * Smaller date goes first
         * @param time1     
         * @param time2
         */
        public function smaller_date_goes_first($time1, $time2)
        { 
            if (strtotime($time1) > strtotime($time2)) 
                return 1; 
            else if (strtotime($time1) < strtotime($time2))  
                return -1; 
            else
                return 0; 
        }

        /**
         * Get date of the sunday
         * @param today         Date of query date
         */
        public function get_sunday(string $today)
        {
            $offset = date('w', strtotime($today));
            $sunday = date('Y-m-d', strtotime("-$offset day", strtotime($today)));            
            return $sunday;
        }

        /**
         * Get the last date of the pervious month
         * @param today         Date of query date
         */
        public function get_last_day_month($today)
        {
            $date = strtotime("last day of previous month", strtotime($today));
            $last_day_pervious_month = date('Y-m-d', $date);
            return $last_day_pervious_month;
        }
    }
?>