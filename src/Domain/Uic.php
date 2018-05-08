<?php

namespace RetardTransilien\Domain;

class Uic
{
    /**
     * Uic uic.
     *
     * @var string
     */
    private $uic;

    /**
     * Uic tr3.
     *
     * @var integer
     */
    private $tr3;

    /**
     * Uic name.
     *
     * @var string
     */
    private $name;

    

    public function getUic() {
        return $this->uic;
    }

    public function setUic($uic) {
        $this->uic = $uic;
        return $this;
    }


    public function getTr3() {
        return $this->tr3;
    }

    public function setTr3($tr3) {
        $this->tr3 = $tr3;
        return $this;
    }

    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }
}