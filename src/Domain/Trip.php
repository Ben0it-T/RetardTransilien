<?php

namespace RetardTransilien\Domain;

class Trip
{
    /**
     * Trip routeId.
     *
     * @var string
     */
    private $routeId;

    /**
     * Trip serviceId.
     *
     * @var integer
     */
    private $serviceId;

    /**
     * Trip tripId.
     *
     * @var string
     */
    private $tripId;

    /**
     * Trip headsign.
     *
     * @var string
     */
    private $headsign;


    /**
     * Trip departureTime.
     *
     * @var time
     */
    private $departureTime;

    /**
     * Trip arrivalTime.
     *
     * @var time
     */
    private $arrivalTime;

    

    public function getRouteId() {
        return $this->routeId;
    }

    public function setRouteId($routeId) {
        $this->routeId = $routeId;
        return $this;
    }


    public function getServiceId() {
        return $this->serviceId;
    }

    public function setServiceId($serviceId) {
        $this->serviceId = $serviceId;
        return $this;
    }

    
    public function getTripId() {
        return $this->tripId;
    }

    public function setTripId($tripId) {
        $this->tripId = $tripId;
        return $this;
    }


    public function getHeadsign() {
        return $this->headsign;
    }

    public function setHeadsign($headsign) {
        $this->headsign = $headsign;
        return $this;
    }

    
    public function getDepartureTime() {
        return $this->departureTime;
    }

    public function setDepartureTime($departureTime) {
        $this->departureTime = $departureTime;
        return $this;
    }

    
    public function getArrivalTime() {
        return $this->arrivalTime;
    }

    public function setArrivalTime($arrivalTime) {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }
}