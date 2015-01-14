<?php
namespace application\classes;
class Database
{
    protected $db;

    private $host;
    private $driver;
    private $dbname;
    private $user;
    private $password;
    private $charset;

    /**
     * @param array $newConf
     */
    public function __construct($newConf = []){
        $config = Registry::get('db');
        $config = array_merge($config, $newConf);


        $this->host = $config['host'];
        $this->driver = $config['driver'];
        $this->dbname = $config['dbname'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->charset = $config['charset'];

    }
    /**
     * @return \PDO
     */
    private function getDb() { //Подключаемся...
        if(!$this->db){
            $this->db = new \PDO($this->driver.':host='.$this->host.'; dbname='.$this->dbname, $this->user, $this->password);
            $this->db->query('SET NAMES '.$this->charset);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return $this->db;

    }

    public  function getLastInsertedId() {
       return $this->db->lastInsertId();
    }

    /**
     * @param $sql
     * @param array $params
     * @param array $types
     * @return mixed
     */
    public function query($sql, $params = [], $types = [])
    {
        $db = $this->getDb();
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            if (!empty($types)) {

                //exit;
                if (preg_match("~int~", $types[$key])) {
                    $types[$key] = \PDO::PARAM_INT;
                }
                else if (preg_match("~str~", $types[$key])) {
                    $types[$key] = \PDO::PARAM_STR;
                }
                else if (preg_match("~bool~", $types[$key])) {
                    $types[$key] = \PDO::PARAM_BOOL;
                }
                else {
                    $types[$key] = \PDO::PARAM_STR;
                }
            }
            $stmt->bindValue("$key", $value, (!empty($types)) ? $types[$key] : \PDO::PARAM_STR);
        }

        $stmt->execute();
        $res = $stmt->fetchAll();
        if($res)
            return $res;
    }

    /**
     * @param $table
     * @param array $data
     * @param array $where
     * @return array
     */
    public function fetchAll($table, $data = [], $where = [])
    {
        $db = $this->getDb();
        $fieldnames = null;
        $fieldnames .= implode(', ', $data);

        $i = 0;
        $whereDetails = null;
        foreach ($where as $k => $v) {
            $whereDetails .= (!$i) ? "$k = :$k" : " AND $k = :$k";
            $i++;
        }
        if(!$where)
            $whereDetails = 1;

        $sql = "SELECT $fieldnames FROM $table WHERE $whereDetails";
        $stmt = $db->prepare($sql);
        foreach($where as $k => $v){
            $stmt->bindValue(":$k", $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * @param $table
     * @param array $data
     * @param array $where
     * @return mixed
     */
    public function fetchRow($table, $data = [], $where = [])
    {
        $db = $this->getDb();
        $fieldnames = null;
        $fieldnames .= implode(', ', $data);
        $i = 0;
        $whereDetails = null;
        foreach($where as $key => $value)
        {
            $whereDetails .= (!$i) ? "$key = :$key" : " AND $key = :$key";
            $i++;
        }
        if(!$where)
            $whereDetails = "1";

        $sql = "SELECT $fieldnames FROM $table WHERE $whereDetails";
        $stmt = $db->prepare($sql);
        foreach ($where as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }

        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $table
     * @param $field
     * @param array $where
     * @return mixed
     */
    public function fetchOne($table, $field, $where = [])
    {
        $db = $this->getDb();
        $whereDetails = null;
        $i = 0;
        foreach($where as $key => $value)
        {
            $whereDetails .= (!$i) ? "$key = :$key" : " AND $key = :$key";
            $i++;
        }
        $sql = "SELECT $field FROM $table WHERE $whereDetails";
        $stmt = $db->prepare($sql);
        foreach($where as $key => $value)
        {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_COLUMN);

    }

    /**
     * @param $table
     * @param array $data
     * @param array $where
     */
    public function update($table, $data = [], $where = [])
    {
        $db = $this->getDb();

        $fields = null;
        foreach($data as $key=> $value) {
            $fields .= "`$key`=:$key,";
        }
        $fields = rtrim($fields, ',');
        $i = 0;
        $whereDetails = null;
        foreach($where as $key => $value)
        {
            if($i == 0)
                $whereDetails .=  "`$key` = :_$key";
            else $whereDetails .= " AND `$key` = :_$key ";
            $i++;
        }

        $sql ="UPDATE $table SET $fields WHERE $whereDetails";
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value)
        {
            $stmt->bindValue(":$key", $value);
        }
        foreach($where as $k => $v)
        {
            $stmt->bindValue(":_$k", $v);
        }

        $stmt->execute();
    }

    /**
     * @param $table
     * @param array $data
     */
    public function insert($table, $data = [])
    {
        $db = $this->getDb();
        $fieldnames = implode(',', array_keys($data));
        $fieldValues = ':'.implode(', :', array_keys($data));


        $sql = "INSERT INTO $table ($fieldnames) VALUES ($fieldValues)";
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value)
        {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
    }


    /**
     * @param $table
     * @param array $where
     * @param int $limit
     */
    public function delete($table, $where = [], $limit = 1)
    {
        $db = $this->getDb();
        $whereDetails = null;
        $i = 0;
        foreach($where as $key => $value)
        {
            if($i == 0)
            $whereDetails .= "$key = :$key";
            else $whereDetails .= " AND $key = :$key";
            $i++;
        }
        $sql = "DELETE FROM $table WHERE $whereDetails LIMIT $limit";
        $stmt = $db->prepare($sql);
        foreach($where as $key => $value)
        {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
    }

}