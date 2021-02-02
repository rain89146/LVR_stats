<?php
    class Controllisting extends Listing {

        /**
         * STEP1: Parse listing file
         * @param string $root_path
         * @param string $table_name
         */
        public function parseListingFile(string $root_path, string $table_name) 
        {
            return $this->parse_csv_data($root_path, $table_name);
        }

        /**
         * STEP2: Populate data into database
         */
        public function populateData($listings)
        {
            $this->populate_data($listings);
        }


        /**
         * STEP1: Parse listing file
         * @param string $root_path
         * @param string $table_name
         */
        public function parse_individual_file(string $root_path, string $table_name) 
        {
            return $this->parse_csv_data_indivisual($root_path, $table_name);
        }

        /**
         * STEP2: Populate data into database
         */
        public function populate_individual_Data($listings)
        {
            return $this->populate_data_individual($listings);
        }

        /**
         * Insert to database
         * @param string $uniqueid
         * @param string $tablename
         * @param string $index
         * @param string $values
         */
        public function insertToDb($matrix_id, $table_name, $key_name, $key_value)
        {
            $this->insert_data($matrix_id, $table_name, $key_name, $key_value);
        }

        /**
         * Insert token
         * @param token         Token
         * @param row_id        Row id
         */
        public function insertToken($token, $row_id)
        {
            $this->insert_token($token, $row_id);
        }

        /**
         * Clear data from the table
         * @param table_name        Name of the table
         */
        public function DeleteTable(string $table_name)
        {
            return $this->delete_data_from_table($table_name);
        }

        /**
         * Save statistic data
         * @param query_id query id
         * @param table_name table name
         * @param key_name field
         * @param key_value value
         * @param query_time query time
         */
        public function saveStatisticResult(string $query_id, string $table_name, string $key_name, string $key_value)
        {
            return $this->save_statistic_data($query_id, $table_name, $key_name, $key_value);
        }
    }
?>