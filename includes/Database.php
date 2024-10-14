<?php
class Database {
    private static $instance = null;
    private $data = [];
    private $dataFile = '/home/engine/app/project/data.json';

    private function __construct() {
        if (file_exists($this->dataFile)) {
            $this->data = json_decode(file_get_contents($this->dataFile), true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function saveData() {
        file_put_contents($this->dataFile, json_encode($this->data));
    }

    public function query($sql, $params = []) {
        // This method is not implemented for file-based storage
        throw new Exception('Query method is not implemented for file-based storage');
    }

    public function fetchAll($table) {
        return $this->data[$table] ?? [];
    }

    public function fetch($table, $id) {
        return $this->data[$table][$id] ?? null;
    }

    public function execute($table, $data) {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }
        $id = uniqid();
        $this->data[$table][$id] = $data;
        $this->saveData();
        return $id;
    }
}