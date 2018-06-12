<?php
namespace Pider\Storage;

class FileStorage {
    private $fname; 
    private $fhandler;
    private $default_buffer_size = 1024;
    private $if_create;

    public function __construct(string $fname, bool $if_create = false) {
        $this->fname = $fname;
        $this->if_create = $if_create;
        $this->_valid();
    }

    public function read() {
        return $this->_read();
    }

    public function write(string $content) {
        $this->_write($content);
    }

    public function readLine(){
        return $this->read();
    }
    public function writeLine($content) {
        $content = $content.PHP_EOL;
        $this->write($content);
    }

    public function _write(string $content, int $size = 1024) {
        if(empty($size)) {
            $buffer_size = $this->default_buffer_size;
        }
        $buffer_size = $size;
        $this->_valid_write();
        $fhandler = fopen($this->fname,'a+');
        $size = fwrite($fhandler,$content,$buffer_size);
        fclose($fhandler);
        return $size;
    }

    public function _read(int $size = 1024):string {
        if(empty($size)) {
            $buffer_size = $this->default_buffer_size;
        }
        $buffer_size = $size;
        $this->_valid_read();
        $fhandler = fopen($this->fname,'r');
        while($data = fread($fhandler,$buffer_size)) {
            var_dump($data);
        }
        fclose($fhandler);
        return $data;
    }

    private function _valid() {
        $file = $this->fname;
        if (!file_exists($file) && !$this->if_create) {
            throw new FileNotFoundException("File ".$file." not exist");
        } else if (!file_exists($file) && $this->if_create){
            $parent = dirname($file);
            $this->_valid_write($parent);
            if(!file_exists($parent)) {
                mkdir($parent,0744,true);
            }
            $fhandler = fopen($file,'w+');
            fclose($fhandler);
        }
    }

    private function _valid_read($file = '') {
        if (empty($file)) {
            $file = $this->fname;
        }
        if (!is_readable($file)) {
            throw new FilePermissionException("You don't have the permission to read ".$file." ");
        }
    }

    private function _valid_write($file = '') {
        if (empty($file)) {
            $file = $this->fname;
        }
        if (!is_writable($file)) {
            throw new FilePermissionException("You don't have the permission to write".$file." ");
        }

    }

}
