<?php
/**
 *  This class is a model extension for phpspider with a convenient way manipulate database.
 */
namespace Extension;

class Model {
    const REQUIRED = 1;
    const OPTIONAL  = 0;
    //this memeber hold a instance of Database Driver class
    private $DBDriver = null;
    //this member defines mappings between class members and  database members
    private $mapping_fields = array();
    private $mapping_table = '';
    private $members = array();
    public function __construct(){
        $this->DBDriver = new DBExtension();
        $this->_members();
    }
    public function __set($name,$value){
        $this->set($name,$value);
    }
    public function __get($name){
       return $this->get($name);
    }
    /**
     * Set model member
     */
    public function set($name,$value,$property = self::OPTIONAL){
        if ($this->member_exist($name)) {
            $this->members[$name]['value'] = $value;
            $this->members[$name]['property'] = $property;
        } else {
            $this->members[$name]['name'] = $name; 
            $this->members[$name]['value'] = $value;
            $this->members[$name]['property'] = $property;
        }
    }

    /**
     *Get model member
     */
    public function get($name){
        if($this->member_exist($name)) {
            return $this->members[$name]['value'];
        }
        return false;
    }
    /**
     * This function construct model's data which mapping to database fields,from a array
     * @param $array array
     * @return $model The model contains values which is an intersection with $array and table fields;
     * The array should only contain a name-value array,and name must be a string time
     *
     * For Example:
     * ```php
     *   $prodcut = array('
     *      'uid' => 123456,
     *      'name' => 'product_name'
     *   ');
     * 
     * ```
     * It's recognized
     */
    public function fromArray($array) {
        if (!is_array($array)) {
            throw new \ErrorException("Argument Error: The 1st Argument must be an array");
        }
        if (empty($array)) {
            return false;
        }
        foreach($array as $name => $value) {
            $this->set($name,$value);
        }
        return $this;
    }

    /**
     * 
     * Collecte members data of current  model and generate a update sql to update the database
     *
     */
    public function update() {
       return  $this->DBDriver->update($this->mapping_table, $this->mapping_fields);
    }
    /**
     *
     * Collecte members data of current model and insert data to database
     */
    public function add() {
        return $this->DBDriver->add($this->mapping_table,$this->mapping_fields);
    }
    private function member_exist($name){
        return array_key_exists($name,$this->members) && $this->members[$name]['name'] == $name;
    }


    public function linkTable($table) {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException("Maptable method only accept a non-empty string as parameter!");
        }
        $this->mapping_table = $table;
    }

    public function linkFields($fields) {
        if (!is_array($fields) || empty($fields)) {
            throw new \InvalidArgumentException("Mapfields method only accept a non-empty string as parameter!");
        }
        $this->mapping_fields = $fields;
    }
    //Collecting members of submodel
    private function _members() {
        $refcls = new \ReflectionClass(static::class);
    }
    /**
     * 
     * This function is used to generate a mapping between model members and database field
     *
     */
    private function __mapping_fields() {
        if (!empty($this->members)) {
            foreach($this->members as $name => $member) {
                if (!empty($this->mapping_fields) && !array_key_exists($name,$this->mapping_fields)) {
                    $this->mapping_fields[$name]  = $member['name'];
                }
            }
        }
    }
    /**
     *Mapping table with current  model, by default the mapping_table is the  model name if mapping_table property in object is not specified .
     *For Example:
     *   If you have a model object named ProductModel, and then it will mapped to a Product table in database.   
     */
    private function __mapping_table() {
        if (empty($this->mapping_table)) {
            //strip the futile namespace information
            $raw_clsname = str_replace('\\','/',static::class);
            $clsname =  basename($raw_clsname);
            if ($clsname != 'Model') {
                $clsname= strstr($clsname,'Model',true);
                $table = strtolower($clsname);
                $this->mapping_table = $table;
            }
        }
    }
}
