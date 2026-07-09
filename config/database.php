<?php
/**
 * Clase para manejar la conexión a la base de datos
 * Basado en: IACC - Programación Web II - Semana 6
 */
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "TIENDA";
    /** @var mysqli|null Conexión mysqli */
    private ?mysqli $conn = null;

    /**
     * Constructor - Establece la conexión
     */
    public function __construct() {
        $this->conn = new mysqli(
            $this->servername, 
            $this->username, 
            $this->password, 
            $this->dbname
        );

        // Verificar conexión
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
        
        // Establecer conjunto de caracteres a UTF-8
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Obtener la conexión
     * @return mysqli
     */
    public function getConnection(): ?mysqli {
        return $this->conn;
    }

    /**
     * Cerrar la conexión
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Ejecutar consulta segura con prepared statements
     * @param string $sql
     * @param array $params
    * @return mysqli_stmt|mysqli_result|bool
     */
    public function query($sql, $params = []) {
        if (empty($params)) {
            return $this->conn->query($sql);
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("Error en prepare: " . $this->conn->error);
        }

        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $values[] = $param;
            }
            
            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        return $stmt;
        $result = $stmt->get_result(); 
        return $result ?: $stmt; 
    }
}
?>