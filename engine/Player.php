<?php

class Player {
    public $name;
    public $id;

    public function __construct($name, $id) {
        //TODO: check for sane values (str + len)
        $this->name = $name;
        $this->id = $id;
    }
}

?>
