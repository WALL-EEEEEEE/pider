<?php
/**
 *  This class is a model extension for phpspider with a convenient way manipulate database.
 */
namespace Extension;
use Extension\DBExtension;

class Model extends DBExtension {
    const REQUIRED = 1;
    const OPTIONAL  = 0;
    //this memeber hold a instance of Database Driver class
    private $DBDriver = null;
    //this member defines mappings between class members and  database members
    private $mapping_fields = array();
    private $mapping_table = '';
    private $members = array();
    /**
     * @var the conditions constrains to perfomed when update(),add(),delete() operation 
     * @default id=? if no condition specified , condition where be current id on update() and delete() operations
     */
    private $conditions = array();
    /**
     * @var specify if the primary key is auto increment,default is false
     * @value default=false
     */
    private $auto_increment = false;
    /**
     * @var specify the primary fields, default, we assume  there is always a id field in  your database, and it's default to be primary
     * @value default = id
     */
    private $id = 'id';

    public function __construct(){
        var_dump('mode ...');
        $this->DBDriver = new DBExtension();
        $this->_members();
    }
    public function __set($name,$value){
        $this->set($name,$value);
    }

    public function __get($name){
        if($this->member_exist($name)) {
            return $this->members[$name]['value'];
        }
        return false;
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
       $this->__assure();
       $update_datas = array();
       foreach($this->mapping_fields as $name => $field) {
           $update_datas[$field]= $this->members[$name]['value'];
       }
       var_dump($update_datas);
       return  $this->DBDriver->update($this->mapping_table, $update_datas,$condtions);
    }

   /**
     *
     * Collecte members data of current model and insert data to database
     */
    public function add() {
        $this->__assure();
        return $this->DBDriver->add($this->mapping_table,$this->mapping_fields);
    }

    public function delete() {
        $this->__assure();
        return $this->DBDriver->delete($this->mapping_table,$conditions);
    }

    private function member_exist($name){
        return array_key_exists($name,$this->members) && $this->members[$name]['name'] == $name;
    }

    /**
     * @function where() specify the condition used to add, update, delete operaion
     * @param $conditions  an associative array, contains the fields and expected value 
     * For example: 
     * <?php
     * $model = new ProductModel();
     * $model->where(['id'=>'3'])->update();
     *
     * The code upon, where just update the recode which id equals 3
     */
    public function where($conditions) {
        if (!is_array($array)) {
            throw new \ErrorException("Argument Error: The 1st Argument must be an array");
        }
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @function table 
     * specify the table to be operated
     * @param $table the table name
     *
     */
    public function table($table) {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException("Maptable method only accept a non-empty string as parameter!");
        }
        $this->mapping_table = $table;
        return $this;
    }

    /**
     * @function fields()
     * specify the fields mapping bewteen the database and current model members
     */
    public function fields($fields) {
        if (!is_array($fields) || empty($fields)) {
            throw new \InvalidArgumentException("Mapfields method only accept a non-empty string as parameter!");
        }
        $this->mapping_fields = $fields;
        return $this;
    }

    //Collecting members of submodel
    private function _members() {
        $refcls = new \ReflectionClass(static::class);
        if (static::class != self::class) {
            $refprops= $refcls->getDefaultProperties();
            if (!empty($refprops)) {
                foreach($refprops as $name => $value) {
                    $this->members[$name]['name'] = $name ;
                    $this->members[$name]['value'] = $value;
                    $this->members[$name]['property'] = self::REQUIRED;
                }
            }
        }
    }

    private function __assure() {
        $this->__mapping_fields();
        $this->__mapping_table();
        $this->__assure_table();
        $this->__assure_fields();
        $this->__default_condition();
    }
    //syncronize  current mapping_table with database table
    private function __assure_table() {
        $this->DBDriver->Assuretable($this->mapping_table);
        $this->DBDriver->table($this->mapping_table);
    }

    //syncronize current mapping_fields with database fields
    private function __assure_fields() {
        foreach($this->mapping_fields as $name => $field) {
            if($this->members[$name]['property'] == self::REQUIRED ) {
                $this->DBDriver->AssureFields($field);
            } else {
                if (!$this->DBDriver->Existsfield($field)) {
                    unset($this->mapping_fields[$name]);
                }
            }
        }
    }

   private function __mapping_fields() {
        if (!empty($this->members)) {
            foreach($this->members as $name => $member) {
                if ((!empty($this->mapping_fields) && !array_key_exists($name,$this->mapping_fields)) || empty($this->mapping_fields) ) {
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

    /**
     * Genertate the default condition, default conditions is the current primary field key if exists, vice verse.
     */
    private function  __default_condition() {
        $primary_field = $this->DBDriver->primary;
        if(!empty($primary_field)) {
            $this->conditions[$primary_field] =  $this->members[$primary_field]['value'];
        }
    }
}
