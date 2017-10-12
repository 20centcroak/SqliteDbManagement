<?php

namespace Croak\DbManagementSqlite;

use Croak\DbManagement\DbManagement;
use Croak\DbManagement\Exceptions\DataBaseException;

/**
 * Manages the sqlite database: connection, read/write
 * it uses DbManagement for a global management
 */
class DbManagementSqLite implements DbManagement
{
    /**
    *@var PDO       object to address the database
    */
    private $pdo;

    /**
    * connect to the database
    * the database reference is defined by its URL
    * @param String $url                url of the database
    * @param String $usr                username for the database to comply with DbManagement, not needed here
    * @param String $pwd                password for the database to comply with DbManagement, not needed here
    * @param array $options             options for the database to comply with DbManagement, not needed here
    * @return DbManagementSqLite        the created DbManagementSqLite object
    * @throws DataBaseException         error in connecting to the database
    */
    public function connect($url, $usr=null, $pwd=null, $name=null)
    {
        $url = "sqlite:".$url;
        try {
            //open the database
            $pdo = new \PDO($url);
            $ok = $pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
            $ok = $ok & $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $ok = $ok & $pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);

            if (!$ok) {
                throw new DataBaseException(DataBaseException::DB_SETTINGS_FAILED);
            }

            $this->pdo = $pdo;
        } catch (\PDOException $e) {
            throw new DataBaseException(DataBaseException::DB_CONNECTION_FAILED);
        }
    }

    /**
    * prepares and execute a query given as a parameter
    * @param String $query          a query to access data in the database
    * @param array $arrayData       an array containing values to insert in the query
    * @throws DataBaseException     if an error occured when preparing or executing the query
    * @return the result of the query
    */
    public function query($query, $arrayData = array())
    {
        try {

            $request = $this->pdo->prepare($query);
            $ok = $request->execute($arrayData);
            if (!$ok) {
                throw new DataBaseException(DataBaseException::QUERY_EXECUTION_FAILED);
            }
            return $request;
        } catch (\PDOException $e) {
            throw new DataBaseException(DataBaseException::QUERY_FAILED);
        }
    }

    /**
     * request to add an DbManagementObject in its table
     * manage its database fields thanks to its keys and build the query
     * @param String $query     the query to modify, it is passed as a reference
     * @param array $keys       the keys of the object to insert in the corresponding fields
     */
    public function add(&$query, $keys){
        $keyString=" (";
        $valString="VALUES (";
        foreach($keys as $key){
            $keyString = $keyString.$key.", ";
            $valString = $valString."?, ";
        }
        $keyString = substr_replace($keyString,") ",strlen($keyString)-2,-1);
        $valString = substr_replace($valString,")", strlen($valString)-2,-1);
        $query = $query.$keyString.$valString;

    }

    /**
    * request with a min param : for example /measures?value-min=28
    * min param is valueMin=28, it means that the database query will only 
    * return values >= 28
    * @param String $query the query to modify, it is passed as a reference
    * @param String $key the key on which make condition
    * @param String $value the value assowiated with the key
    */
    public function sortMin(&$query, $key, $value){
        $word = $this->sortWord($query);
        $query = $query.$word."$key >= ?";
    }
    
    /**
    * request with a max param : for example /measures?value-max=28
    * max param is valueMax=28, it means that the database query will only 
    * return values <= 28
    * @param String $query the query to modify, it is passed as a reference
    * @param String $key the key on which make condition
    * @param String $value the value assowiated with the key
    */
    public function sortMax(&$query, $key, $value){
        $word = $this->sortWord($query);
        $query = $query.$word."$key <= ?";
    }

    /**
    * request with a param : for example /measures?value=28
    * param is value=28, it means that the database query will only 
    * return values = 28
    * @param String $query the query to modify, it is passed as a reference
    * @param String $key the key on which make condition
    * @param String $value the value assowiated with the key
    */
    public function sort(&$query, $key, $value){
        $word = $this->sortWord($query);
        $query = $query.$word."$key = ?";
    }

    /**
    * request with an ascending ordering param : for example /measures?sort-value-asc
    * ordering param is sort-value-asc, it means that the database query will  
    * return lines in sorting them according to value, in ascending order
    * @param String $query the query to modify, it is passed as a reference
    * @param String $param the param used to sort data
    */
    public function orderUp(&$query, $param){
        $word = $this->orderWord($query);
        $query = $query.$word."$param ASC";
    }

    /**
    * request with an descending ordering param : for example /measures?sort-value-asc
    * ordering param is sort-value-asc, it means that the database query will  
    * return lnes in sorting them according to value, in ascending order
    * @param String $query the query to modify, it is passed as a reference
    * @param String $param the param used to sort data
    */
    public function orderDown(&$query, $param){
        $word = $this->orderWord($query);
        $query = $query.$word."$param DESC";
    }

    /**
    * close query, add a specific String at the end if necessary
    * @param String $query the query to modify, it is passed as a reference
    */
    public function closeQuery(&$query){
        $query = $query.";";
    }

    /**
    * it is necessary to check if the condition is the very first one 
    * or is this is an extra condition to
    * use the correct word in the query
    * @param $query the query to fill in
    * @return the word to be used to fill in the current query
    */
    private function sortWord($query){
        if(preg_match('/^.*WHERE.*$/i',$query)===1){
            return " AND ";
        }
        else{
            return " WHERE ";
        }
    }

    /**
    * it is necessary to check if the order condition is the very first one 
    * or is this is an extra condition to
    * use the correct word in the query
    * @param $query the query to fill in
    * @return the word to be used to fill in the current query
    */
    private function orderWord($query){
        if(preg_match('/^.*ORDER.*$/i',$query)===1){
            return ", ";
        }
        else{
            return " ORDER BY ";
        }
    }

    /**
    * close the database connection
    */
    public function disconnect()
    {
        $this->pdo = null;
    }

    /**
    * returns the last inserted id
    * @return int
    */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
