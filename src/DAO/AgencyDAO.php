<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Agency;

class AgencyDAO extends DAO
{
    /**
     * Returns Agency matching the supplied id.
     *
     * @param string $id
     *
     * @return \RetardTransilien\Domain\Agency|throws an exception if no matching agency is found
     */
    public function find($id) {
        $sql = "select * from transilien_agency where agency_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No agency matching");
    }

    /**
     * Return a list of all agencies, sorted by name.
     *
     * @return array A list of all agencies.
     */
    public function findAll() {
        $sql = "select * from transilien_agency order by agency_name asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $agencies = array();
        foreach ($result as $row) {
            $agencies[ $row['agency_id'] ] = $this->buildDomainObject($row);
        }
        return $agencies;
    }

    /**
     * Creates an Agency object based on a DB row.
     *
     * @param array $row The DB row containing Agency data.
     * @return RetardTransilien\Domain\Agency
     */
    protected function buildDomainObject(array $row) {
        $agency = new Agency();
        $agency->setId($row['agency_id']);
        $agency->setName($row['agency_name']);
        $agency->setUrl($row['agency_url']);
        $agency->setTimezone($row['agency_timezone']);
        $agency->setLang($row['agency_lang']);
        return $agency;
    }
}