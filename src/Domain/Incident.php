<?php

namespace RetardTransilien\Domain;

class Incident
{
    /**
     * Incident tripId.
     *
     * @var string
     */
    private $tripId;

    /**
     * Incident date.
     *
     * @var date
     */
    private $date;

    /**
     * Incident serviceId.
     *
     * @var string
     */
    private $serviceId;

    /**
     * Incident headsign.
     *
     * @var string
     */
    private $headsign;

    /**
     * Incident routeId.
     *
     * @var string
     */
    private $routeId;

    /**
     * Incident departureTime.
     *
     * @var time
     */
    private $departureTime;

    /**
     * Incident arrivalTime.
     *
     * @var time
     */
    private $arrivalTime;

    /**
     * Incident incidentType.
     *
     * @var string
     */
    private $incidentType;

    /**
     * Incident delay.
     *
     * @var string
     */
    private $delay;

    /**
     * Incident median.
     *
     * @var float(6,2)
     */
    private $median;



    public function getTripId() {
        return $this->tripId;
    }

    public function setTripId($tripId) {
        $this->tripId = $tripId;
        return $this;
    }

    
    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    
    public function getServiceId() {
        return $this->serviceId;
    }

    public function setServiceId($serviceId) {
        $this->serviceId = $serviceId;
        return $this;
    }

    
    public function getHeadsign() {
        return $this->headsign;
    }

    public function setHeadsign($headsign) {
        $this->headsign = $headsign;
        return $this;
    }

    
    public function getRouteId() {
        return $this->routeId;
    }

    public function setRouteId($routeId) {
        $this->routeId = $routeId;
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


    public function getIncidentType() {
        return $this->incidentType;
    }

    public function setIncidentType($incidentType) {
        $this->incidentType = $incidentType;
        return $this;
    }

    
    public function getDelay() {
        return $this->delay;
    }

    public function setDelay($delay) {
        $this->delay = $delay;
        return $this;
    }

    
    public function getMedian() {
        return $this->median;
    }

    public function setMedian($median) {
        $this->median = $median;
        return $this;
    }
}