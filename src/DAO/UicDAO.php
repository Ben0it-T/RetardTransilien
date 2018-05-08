<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Uic;

class UicDAO extends DAO
{
    /**
     * Returns Uic matching the supplied uic.
     *
     * @param string $uic
     *
     * @return \RetardTransilien\Domain\Uic|throws an exception if no matching uic is found
     */
    public function find($id) {
        $sql = "select * from transilien_uic where uic=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No uic matching");
    }

    /**
     * Return a list of all uics, sorted by name.
     *
     * @return array A list of all uics.
     */
    public function findAll() {
        $sql = "select * from transilien_uic order by name asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $uics = array();
        foreach ($result as $row) {
            $uics[ $row['uic'] ] = $this->buildDomainObject($row);
        }
        return $uics;
    }

    /**
     * Returns Uic matching the supplied uic.
     *
     * @param string $uic
     *
     * @return \RetardTransilien\Domain\Uic|throws an exception if no matching uic is found
     */
    public function findLike($id) {
        $sql = "select * from transilien_uic where uic like ?";
        $row = $this->getDb()->fetchAssoc($sql, array($id.'%'));

        if ($row)
            return $this->buildDomainObject($row);
        else
            return null;
    }

    

    /**
     * Creates a Uic object based on a DB row.
     *
     * @param array $row The DB row containing Uic data.
     * @return RetardTransilien\Domain\Uic
     */
    protected function buildDomainObject(array $row) {
        $uic = new Uic();
        $uic->setUic($row['uic']);
        $uic->setTr3($row['tr3']);
        $uic->setName($row['name']);
        return $uic;
    }
}