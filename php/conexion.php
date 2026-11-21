<?php
    class Database{
        private mysqli $_connection;  //nota, esta conexion usa los permisos genericos de XAMPP, si te da error de permisos, revisa si tienes algun usuario y contraseña configurados
        private $_host = "localhost";
        private $_user = "root";
        private $_pass = "";
        private $_db = "veterinaria-pola-y-mani";

        private static ?Database $_instancia = null;

        public static function getinstancia(){
            if(!isset(self::$_instancia)){
                self:: $_instancia = new self;
            }
            return self ::$_instancia;

        }

        public function __construct(){
            $this-> _connection= new mysqli($this-> _host, $this -> _user, $this -> _pass, $this -> _db);
            if(mysqli_connect_error()){
                trigger_error("Falla en la conexion de la base de datos" .mysqli_connect_error().E_USER_ERROR);

            }
            $this->_connection->set_charset('utf8mb4');
        }

        private function __close(){}

        public function getConnection(){
            return $this -> _connection;
        }
        //metodo para revisar que el SQL sea un string
        private static function es_string($sql){
            if(!is_string($sql)){
                trigger_error('class.Database.inc : $SQL eviado
                no es un string! '.$sql);
                return false;
            }
            return true;
        }
        //metodo para oobtener resultado de una consulta a la base de datos
        public static function get_data($sql){
            //para consultas que devuelven un valor
            if(!self::es_string($sql))
                exit();

            //si el query fue validado procedemos coon la base de datos
            $db= Database::getinstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            if($resultado === false){
                return "class.Database.class: error".$mysqli->error;
            }
            else
                return $resultado;
        }
        //metodo para obtener un resultado de uuna consulta a la base de datos
        public function get_rows($sql){
            //para consultas que devuelven varios datos
            if(!self::es_string($sql))
                exit();

            //si el query fue valido procederemos con la conexion a la base de datos
            $db= Database::getinstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            if($resultado === false){
                return "class.Database.class: error".$mysqli->error;
            }
            else 
                return $resultado;
        }

        public static function ejecutar_sql($sql){
            if(!self::es_string($sql))
                exit();
            //si el query fue validado procederemos con la conexion a la base de datos

            $db = Database::getInstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            if($resultado === false){
                return "class.Database.class: error ".$mysqli->error;
            }
            else{
                return true;
            }
        }


    }
     //termina class database
    
?>