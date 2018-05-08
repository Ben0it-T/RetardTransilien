<?php

namespace RetardTransilien\Domain;

class Ongoing
{
    /**
     * Param varname.
     *
     * @var string
     */
    private $varname;

    /**
     * Param value.
     *
     * @var string
     */
    private $value;


    public function getVarname() {
        return $this->varname;
    }

    public function setVarname($varname) {
        $this->varname = $varname;
        return $this;
    }

    
    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
}
