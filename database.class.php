<?php
    class DataBase extends PDO {
        
        const DB_HOST = 'localhost';
        const DB_USER = 'root';
        const DB_PASS = 'root';
        const DB_NAME = 'fpview';

public function __construct() {
    $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";

    try {
        parent::__construct($dsn, self::DB_USER, self::DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
}
   
?>