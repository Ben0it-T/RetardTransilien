<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Ongoing;

class OngoingDAO extends DAO
{
    /**
     * Returns matching supplied varname.
     *
     * @param string $varname
     *
     * @return \RetardTransilien\Domain\Ongoing|throws an exception if no matching found
     */
    public function find($varname) {
        $sql = "select * from transilien_ongoing where varname=?";
        $row = $this->getDb()->fetchAssoc($sql, array($varname));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No param matching " . $varname);
    }

    /**
     * Return a list of all varname, sorted by varname.
     *
     * @return array A list of all varname.
     */
    public function findAll() {
        $sql = "select * from transilien_ongoing order by varname asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $ongoings = array();
        foreach ($result as $row) {
            $ongoings[$row['varname']] = $this->buildDomainObject($row);
        }
        return $ongoings;
    }

    /**
     * Update Ongoing
     *
     * @param \RetardTransilien\Domain\Ongoing $ongoing
     */
    public function update(Ongoing $ongoing) {
        $ongoingData = array(
            'varname' => $ongoing->getVarname(),
            'value' => $ongoing->getValue()
            );
        $this->getDb()->update('transilien_ongoing', $ongoingData, array('varname' => $ongoing->getVarname()) );
    }

    /**
     * Creates an Ongoing object based on a DB row.
     *
     * @param array $row The DB row containing Ongoing data.
     * @return RetardTransilien\Domain\Ongoing
     */
    protected function buildDomainObject(array $row) {
        $ongoing = new Ongoing();
        $ongoing->setVarname($row['varname']);
        $ongoing->setValue($row['value']);
        return $ongoing;
    }
}
