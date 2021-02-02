<?php 

//  Database connect
class DBconn {

    private $HOST_NAME = 'localhost';
    private $USER_NAME = 'root';
    private $PASSWORD = '';
    private $DB_NAME = 'listing_db';
    private $CHAR_SET = 'utf8mb4';

    //  connect
    public function connect ()
    {
        try{
            
            //  set the dsn
            $dsn = "mysql:host=" . $this->HOST_NAME . ";dbname=" . $this->DB_NAME. ";charset=" . $this->CHAR_SET;

            //important settings for production
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            //  Return the pdo
            return new PDO($dsn, $this->USER_NAME, $this->PASSWORD, $options);
    
        }catch(\PDOException $e){
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Take sql statement to process
     * @param sql the sql statement
     * @param param optional
     */
    public function query(object $conn, string $sql, array $param = NULL)
    {
        $stmt = $conn->prepare($sql);
        $stmt->execute($param);
        return $stmt;
    }

    /**
     * Disconnect. release the memory
     * @param conn connection instance
     */
    public function disconnect(object $conn)
    {
       $conn = null;
    }
}

?>