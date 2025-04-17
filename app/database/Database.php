<?php

class DataBase
{
    private $localhost = DB__HOST;
    private $username = DB__USER;
    private $password = DB__PASS;
    private $dbname = DB__NAME;
    private $db;
    private $stmt;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=$this->localhost;dbname=$this->dbname;";
            $username = $this->username;
            $password = $this->password;
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (Exception $e) {
            $error = new ErrorPage();
            $error->_500_();
        }
    }
    public function query($sql)
    {
        $this->stmt = $this->db->prepare($sql);
    }
    public function bind($param, $value)
    {
        $this->stmt->bindParam($param, $value);
    }
    public function execute()
    {
        $this->stmt->execute();
    }
    public function fetchAll()
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    public function fetch()
    {
        $this->execute();
        return $this->stmt->fetch();
    }
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
}
