<?php

namespace RetardTransilien\Domain;

class Agency 
{
    /**
     * Agency id.
     *
     * @var integer
     */
    private $id;

    /**
     * Article name.
     *
     * @var string
     */
    private $name;

    /**
     * Agency url.
     *
     * @var string
     */
    private $url;

    /**
     * Agency timezone.
     *
     * @var string
     */
    private $timezone;

    /**
     * Agency lang.
     *
     * @var string
     */
    private $lang;





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

    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }


    public function getTimezone() {
        return $this->timezone;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
        return $this;
    }


    public function getLang() {
        return $this->lang;
    }

    public function setLang($lang) {
        $this->lang = $lang;
        return $this;
    }
}