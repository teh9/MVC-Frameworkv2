<?php

    namespace application\lib;

    use PDO;
    use PDOException;

    class D{

        static $table;

        static $connectionParams = [];

        static function setup($host = NULL, $db = NULL, $user = NULL, $pw = NULL){

            try {

                self::$connectionParams = [
                    'host' => $host,
                    'db'   => $db,
                    'user' => $user,
                    'pw'   => $pw
                ];

                return new DB(self::$connectionParams);
            }catch (PDOException $e){

                throw new PDOException($e->getMessage());
            }
        }

        static function setTable($table){

            return self::$table = $table;
        }

        static function load($table){

            self::setTable($table);

            return (new DB(self::$connectionParams))->load($table);
        }

        static function store($object = NULL, $id = NULL){

            return (new DB(self::$connectionParams))->store($object, $id, self::$table);
        }

        static function findAll($tbl, $cond = NULL, $binds = []){

            return (new DB(self::$connectionParams))->findAll($tbl, $cond, $binds);
        }

        static function free($sql, $binds = []){

            return (new DB(self::$connectionParams))->free($sql, $binds);
        }

        static function update($obj, $id){

            return (new DB(self::$connectionParams))->update($obj, $id, self::$table);
        }

        static function trash($ids){

            return (new DB(self::$connectionParams))->trash($ids, self::$table);
        }

        static function lastId(){

            return (new DB(self::$connectionParams))->lastId();
        }

        static function findOne($tbl, $cond, $binds = []){

            return (new DB(self::$connectionParams))->findOne($tbl, $cond, $binds);
        }

        static function find($tbl, $cond = NULL, $bindings = []){

            return (new DB(self::$connectionParams))->find($tbl, $cond, $bindings);
        }

        static function rowCount($tbl){

            return (new DB(self::$connectionParams))->rowCount($tbl);
        }

        public function __destruct(){

            self::$table = null;
        }

    }

    class DB{

        private $otherConnectionParams = [];
        private $table;
        private $type = 'insert';
        private $pdo;

        public function __construct($otherConnectionParams = []){

            $conn = (object)$otherConnectionParams;

            $this->pdo = new PDO('mysql:host='.$conn->host.';dbname='.$conn->db.'', $conn->user, $conn->pw);

            $this->pdo->exec('set names utf8');

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        public function load($table){

            $this->setTable($table);

            return $this->pdo;
        }

        protected function setTable($table){

            $this->table = $table;

            return $this->table;
        }

        public function store($object = NULL, $id = NULL, $table = NULL){

            if(!empty($object)){

                $method = $this->type.'Rows';

                if(method_exists(__CLASS__, $method)) {

                    $this->setTable($table);

                    return $this->$method($object, $id, $table);
                }

                return false;
            }

            return false;
        }

        public function update($obj, $id, $table){

            $this->type = 'update';

            return $this->store($obj, $id, $table);
        }

        public function trash($ids, $table){

            $this->type = 'delete';

            return $this->store($ids, null, $table);
        }

        private function insertRows($obj, $id = NULL){

            $array = (array)$obj;

            $columns = implode(', ', array_keys($array));

            $questionMarks = trim(str_repeat('?, ', count($array)), ', ');

            $sql = 'INSERT INTO '.$this->table.' ('.$columns.') VALUES ('.$questionMarks.')';

            return $this->doQuery($sql, array_values($array));
        }

        private function updateRows($obj, $id){

            $array = (array)$obj;

            $columns = implode(' = ?, ', array_keys($array)).' = ?';

            array_push($array, $id);

            $sql = 'UPDATE '.$this->table.' SET '.$columns.' WHERE id = ?';

            return $this->doQuery($sql, array_values($array));
        }

        private function deleteRows($ids = []){

            $list = trim(str_repeat('?, ', count($ids)), ', ');

            $sql = 'DELETE FROM '.$this->table.' WHERE id IN ('.$list.')';

            return $this->doQuery($sql, $ids);
        }

        private function doQuery($sql, $binds = []){

            $stmt = $this->pdo->prepare($sql);

            if($stmt->execute($binds)){

                unset($stmt);
                return true;
            }

            return false;
        }

        public function findAll($tbl, $cond = NULL, $binds = []){

            return $this->find($tbl, $cond, $binds);
        }

        public function find($tbl, $cond = NULL, $binds = []){

            if($cond == NULL){
                try{
                    return $this->pdo->query('SELECT * FROM '.$tbl)->fetchAll(PDO::FETCH_ASSOC);
                }catch (PDOException $e){
                    throw new PDOException($e->getMessage());
                }
            }

            try{
                $stmt = $this->pdo->prepare('SELECT * FROM '.$tbl.' WHERE '.$cond);

                $stmt->execute($binds);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch (PDOException $e){
                throw new PDOException($e->getMessage());
            }
        }

        public function findOne($tbl, $cond, $binds = []){

            $result = $this->find($tbl, $cond.' LIMIT 1', $binds);

            return (!empty($result)) ? $result[0] : $result;
        }

        public function free($sql, $binds = []){

            try {
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute($binds);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch (PDOException $e){

                throw new PDOException($e->getMessage());
            }
        }

        public function lastId(){

            return $this->pdo->lastInsertId();
        }

        public function rowCount($tbl){

            try{

                return $this->pdo->query('SELECT COUNT(id) AS count FROM '.$tbl)->fetchAll(PDO::FETCH_ASSOC);
            }catch (PDOException $e){

                throw new PDOException($e->getMessage());
            }
        }

        public function __destruct(){
            unset($this->pdo);
        }
}