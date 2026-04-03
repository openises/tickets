<?php
/*
MySQL Dump PHP class by CubeScripts, www.cubescripts.com
*/

class MySQLDump {

    public $tables = array();
    public $connected = false;
    public $output;
    public $droptableifexists = false;
    public $mysql_error;
    public $conn = null;
    public $fields = array();

    public function connect($host,$user,$pass,$db) {
        $return = true;
        $conn = @mysqli_connect($host,$user,$pass,$db);
        if (!$conn) { $this->mysql_error = mysqli_connect_error(); $return = false;     }
        $this->conn = $conn;
        $this->connected = $return;
        return $return;
        }

    public function list_tables() {
        $return = true;
        if (!$this->connected) { $return = false;     }
        $this->tables = array();
        $sql = $this->conn->query("SHOW TABLES");
        while ($row = $sql->fetch_array()) {
            array_push($this->tables,$row[0]);
            }
        return $return;
        }

    public function list_values($tablename) {
        $sql = $this->conn->query("SELECT * FROM `$tablename`");
        $this->output .= "\n\n-- Dumping data for table: $tablename\n\n";
        while ($row = $sql->fetch_array()) {
            $broj_polja = count($row) / 2;
            $this->output .= "INSERT INTO `$tablename` VALUES(";
            $buffer = '';
            for ($i=0;$i < $broj_polja;$i++) {
                $vrednost = $row[$i];
                if ($vrednost === null) { $vrednost = 'NULL'; }
                elseif (!is_integer($vrednost)) { $vrednost = "'".$this->conn->real_escape_string((string)$vrednost)."'";     }
                $buffer .= $vrednost.', ';
                }
            $buffer = substr($buffer,0,strlen($buffer)-2);
            $this->output .= $buffer . ");\n";
            }
        }

    public function dump_table($tablename) {
        $this->output = "";
        $this->get_table_structure($tablename);
        $this->list_values($tablename);
        }

    public function get_table_structure($tablename) {
    //    snap(__LINE__, $tablename);
        $arr = array("NO" => "NOT NULL", "YES" => "NULL");
        $primary = '';

        $this->output .= "\n\n-- Dumping structure for table: $tablename\n\n";
        if ($this->droptableifexists) { $this->output .= "DROP TABLE IF EXISTS `$tablename`;\nCREATE TABLE `$tablename` (\n";     }
            else { $this->output .= "CREATE TABLE `$tablename` (\n";     }
        $sql = $this->conn->query("DESCRIBE `$tablename`");        // returns mysqli_result object
        $this->fields = array();
        while ($row = $sql->fetch_array()) {
            $name = $row[0]; //name
            $type = $row[1]; //type
            $null = $arr[strtoupper(trim($row[2]))]; //null?

            $key = $row[3]; //(primary) key - PRI za primary
            if ($key == "PRI") { $primary = $name;     }
            $default = $row[4]; //default
            $extra = $row[5];
            if ($extra !== "") { $extra .= ' ';     } //makeup
            $this->output .= "  `$name` $type $null $extra,\n";
            }
        if ($primary !== '') {
            $this->output .= "  PRIMARY KEY  (`$primary`)\n);\n";
        } else {
            $this->output .= ");\n";
        }
        }

    }
