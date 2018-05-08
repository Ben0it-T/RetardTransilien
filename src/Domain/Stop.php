<?php

namespace RetardTransilien\Domain;

class Stop
{
    /**
     * Stop id.
     *
     * @var string
     */
    private $id;

    /**
     * Stop name.
     *
     * @var string
     */
    private $name;

    /**
     * Stop desc.
     *
     * @var string
     */
    private $desc;

    /**
     * Stop lat.
     *
     * @var float
     */
    private $lat;

    /**
     * Stop lon.
     *
     * @var float
     */
    private $lon;

    /**
     * Stop zoneId.
     *
     * @var sring
     */
    private $zoneId;

    /**
     * Stop url.
     *
     * @var sring
     */
    private $url;

    /**
     * Stop type.
     *
     * @var integer
     */
    private $type;

    /**
     * Stop parentStation.
     *
     * @var string
     */
    private $parentStation;






    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }


    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    
    public function getDesc() {
        return $this->desc;
    }

    public function setDesc($desc) {
        $this->desc = $desc;
        return $this;
    }


    public function getLat() {
        return $this->lat;
    }

    public function setLat($lat) {
        $this->lat = $lat;
        return $this;
    }


    public function getLon() {
        return $this->lon;
    }

    public function setLon($lon) {
        $this->lon = $lon;
        return $this;
    }

    
    public function getZoneId() {
        return $this->zoneId;
    }

    public function setZoneId($zoneId) {
        $this->zoneId = $zoneId;
        return $this;
    }


    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }


    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }


    public function getParentStation() {
        return $this->parentStation;
    }

    public function setParentStation($parentStation) {
        $this->parentStation = $parentStation;
        return $this;
    }
}