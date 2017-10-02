<?php
namespace Extension;

/*
 *This file aims to extend the db class of phpspider,for better experience without
 *any hindrance while interacting with database
 * */
//@TODO autoloader for custome units
use db;
class DBExtension extends db implements DBDriver {
    //Cached tables in database 
    private $cached_tables = array();
    //Cached fields in database
    private $cached_fields = array();
    //Current tablename
    private $table = '';
    public function __construct() {
//        $this->__fields();
//        $this->__tables();
    }
    /**
     * default database is $GLOBALS['config']['db']
     * @param $dbname
     * @param array $dbconfig
     */
    public static function switch_db($dbname, $dbconfig = array()) {
         if(array_key_exists($dbname, $GLOBALS['config']['ex_db'])) {
             self::set_connect($dbname,$GLOBALS['config']['ex_db'][$dbname]);
         } else {
             self::set_connect($dbname,$dbconfig);
         }
    }

    /**
     * @TODO test
     * parse database modifier, such like "http://username:password@hostname:database.tablename"
     * @param $modifier
     */
    public static function parse_modifier($modifier) {
        if (empty($modifier)) {
            return false;
        }
        //parsing
        $is_parse = preg_match('/$https?:\/\/([A-Za-z0-9]+:[A-Za-z0-9]+@)?([A-Za-z0-9]+:)?(A-Za-z)+.(A-Za-z)+$/',$modifier);
        if (!$is_parse) {
            printf("%s","modifier is informat");
            return false;
        }
        $dbconf=  array();
        $parse_url = parse_url($modifier);
        if (!empty($parse_url['scheme']) || !empty($parse_url['host']) || $parse_url['port'] ) {
                $parse_url['scheme'] = empty($parse_url['scheme'])?"http":$parse_url['scheme'];
                $parse_url['host']  = empty($parse_url['host']) ? "127.0.0.1":$parse_url['host'];
                $parse_url['host']  = empty($parse_url['port']) ? "3306":$parse_url['port'];
                $dbconf['host'] = $parse_url['scheme']."//".$parse_url['host'].":".$parse_url['port'];
        }
        $parse_db = array();
        $matches = preg_match('/^.*([A-Za-z]+)\.([A-za-z])+$/',$modifier,$parse_db);
        if (!$matches) {
            printf("Modifier: database and table name must be specified!");
            return false;
        }
        $dbconf['database'] = $parse_db[1];
        $dbconf['table'] = $parse_db[2];
        return $dbconf;
    }


    /**
     * This function is used to check if tables  exist in database,default to assure the current table stored in $this->table's existence.
     */
    public function  Assuretable($table = ''){
        if (empty($this->table) && empty($table)) {
            return false;
        }
        $table = empty($table)?$this->table:$table;
        if (empty($this->cached_tables)) {
            $this->__tables();
        }
        if (!in_array($table,$this->cached_tables)) {
            throw new \ErrorException('Error: No Such Table Name In Database!');
        }
    }

    /**
     *This function is used to check if fields exist in database
     *
     */
    public function Assurefield($field) {
        if (empty($this->table) && empty($field)) {
            return false;
        }
        if (empty($this->cached_fields)) {
            $this->__fields();
        }
        if (!in_array($field,$this->cached_fields) ) {
            throw new \ErrorException('Error: No Such Such Field In Table '.$this->table.'!');
        }
    }

    /**
     *This function is used to get all tables in database and cache it.
     */
   private function __tables() {
        $sql = 'SHOW TABLES';
        $tables = array();
        $raw_tables = $this->get_all($sql);
        //convert it to 1 diamenson array
        if (!empty($raw_tables)) {
            foreach($raw_tables as $raw_table) {
                $raw_table = array_values($raw_table);
                $tables[]= $raw_table[0];
            }
        }
        $this->cached_tables = $tables;
        return $tables;
    }

    /**
     *
     * This function is used to get fields of table and cached it
     */
    private function __fields() {
        $fields = array();
        if (empty($this->table)) {
            throw new \ErrorException('Error: No Table Name Specified!');
        }
        if (empty($this->cached_tables)) {
            $this->__tables();
        }
        if (!in_array($this->table,$this->cached_tables)) {
            throw new \ErrorException('Error: No Such Table Name In Database!');
        }
        $sql = 'Desc '.$this->table;
        $raw_fields = self::get_all($sql);
        $fields = array();
        if ($raw_fields == false) {
            return false;
        } else {
            foreach($raw_fields as $field) {
                if ($field['Extra'] != 'auto_increment') {
                    $field_name =  $field['Field'];
                    $fields[$field_name]['name'] = $field_name;
                    $fields[$field_name]['type'] = $field['Type'];
                    $fields[$field_name]['is_primary'] = $field['Key'] == 'PRI';
                    $fields[$field_name]['is_null'] = $field['Null'] == 'YES';
                }
            }
        }
        $this->cached_fields = $fields;
        return $fields;
    }
}
