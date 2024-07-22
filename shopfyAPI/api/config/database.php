<?php

// Set default time zone
date_default_timezone_set("Asia/Manila");

// Set time limit of requests
set_time_limit(1000);

// Define constants for server credentials/configuration
define("SERVER", "localhost");
define("DATABASE", "shopfy_db");
define("USER", "root");
define("PASSWORD", "");  // Replace with your actual MySQL root password
define("DRIVER", "mysql");

class Connection {
    private $connectionString;
    private $options;

    public function __construct() {
        $this->connectionString = DRIVER . ":host=" . SERVER . ";dbname=" . DATABASE . ";charset=utf8mb4";
        $this->options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];
    }

    public function connect() {
        try {
            return new \PDO($this->connectionString, USER, PASSWORD, $this->options);
        } catch (\PDOException $e) {
            // Log error or handle it as necessary
            die("Connection failed: " . $e->getMessage());
        }
    }
}

?>
