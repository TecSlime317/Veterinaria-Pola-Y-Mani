<?php
    // Centraliza la configuración y las operaciones compartidas de conexión a la base de datos.
    class Database{
        // Declara la conexión activa y los parámetros del servidor y la base de datos.
        private mysqli $_connection; // Almacena el objeto de conexión inicializado por el constructor.
        private $_host = "localhost";
        private $_user = "root";
        private $_pass = ""; //MUY IMPORTANTE. Esta conexion de la base de datos es de XAMPP, porque no deberia de subir mi contraseña a github. Si usas XAMPP sin configurar, solo copia y pega todo en conexion.php (si no existe, crealo en esta misma carpeta), y si lo tienes configurado, cambia los datos de conexion a tu base de datos.
        private $_db = "veterinaria_pola_y_mani";

        // Conserva una única instancia de Database durante la ejecución.
        private static ?Database $_instancia = null;

        // Devuelve la instancia existente o crea la conexión por primera vez.
        public static function getinstancia(){
            // Evita crear más de una instancia de la clase.
            if(!isset(self::$_instancia)){
                self:: $_instancia = new self;
            }
            return self ::$_instancia;

        }

        // Abre la conexión MySQL, reporta errores y establece la codificación de caracteres.
        public function __construct(){
            $this-> _connection= new mysqli($this-> _host, $this -> _user, $this -> _pass, $this -> _db);
            // Interrumpe con un error explícito si MySQL no pudo establecer la conexión.
            if(mysqli_connect_error()){
                trigger_error("Falla en la conexion de la base de datos" .mysqli_connect_error().E_USER_ERROR);

            }
            $this->_connection->set_charset('utf8mb4');
        }

        // Espacio reservado para la lógica de cierre de la conexión; actualmente no realiza acciones.
        private function __close(){}

        // Expone la conexión para que los módulos ejecuten consultas.
        public function getConnection(){
            return $this -> _connection;
        }

        // Comprueba que la consulta recibida sea una cadena antes de ejecutarla.
        private static function es_string($sql){
            // Rechaza valores de entrada que no sean cadenas.
            if(!is_string($sql)){
                trigger_error('class.Database.inc : $SQL eviado
                no es un string! '.$sql);
                return false;
            }
            return true;
        }

        // Ejecuta una consulta que devuelve un resultado y retorna el resultado o el error de MySQL.
        public static function get_data($sql){
            // Detiene la operación si la consulta recibida no es una cadena.
            if(!self::es_string($sql))
                exit();

            $db= Database::getinstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            // Devuelve el error reportado por MySQL o el resultado de la consulta.
            if($resultado === false){
                return "class.Database.class: error".$mysqli->error;
            }
            else
                return $resultado;
        }

        // Ejecuta una consulta que puede devolver varias filas y retorna el resultado o el error de MySQL.
        public function get_rows($sql){
            // Detiene la operación si la consulta recibida no es una cadena.
            if(!self::es_string($sql))
                exit();

            $db= Database::getinstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            // Devuelve el error reportado por MySQL o el conjunto de filas obtenido.
            if($resultado === false){
                return "class.Database.class: error".$mysqli->error;
            }
            else 
                return $resultado;
        }

        // Ejecuta una sentencia SQL y devuelve true si tiene éxito o el error de MySQL si falla.
        public static function ejecutar_sql($sql){
            // Detiene la operación si la consulta recibida no es una cadena.
            if(!self::es_string($sql))
                exit();

            $db = Database::getInstancia();
            $mysqli = $db->getConnection();
            $resultado = $mysqli->query($sql);
            // Devuelve el error reportado por MySQL o confirma la ejecución exitosa.
            if($resultado === false){
                return "class.Database.class: error ".$mysqli->error;
            }
            else{
                return true;
            }
        }


    }
     // Fin de la clase que centraliza el acceso a la base de datos.
    
?>