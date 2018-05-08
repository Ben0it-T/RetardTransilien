<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Param;

class ParamDAO extends DAO
{
    /**
     * Returns param matching the supplied varname.
     *
     * @param string $varname
     *
     * @return \RetardTransilien\Domain\Param|throws an exception if no matching param is found
     */
    public function find($varname) {
        $sql = "select * from transilien_param where varname=?";
        $row = $this->getDb()->fetchAssoc($sql, array($varname));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No param matching " . $varname);
    }

    /**
     * Return a list of all params, sorted by varname.
     *
     * @return array A list of all params.
     */
    public function findAll() {
        $sql = "select * from transilien_param order by varname asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $params = array();
        foreach ($result as $row) {
            $params[$row['varname']] = $this->buildDomainObject($row);
        }
        return $params;
    }

    /**
     * Creates an Param object based on a DB row.
     *
     * @param array $row The DB row containing Param data.
     * @return RetardTransilien\Domain\Param
     */
    protected function buildDomainObject(array $row) {
        $param = new Param();
        $param->setVarname($row['varname']);
        $param->setValue($row['value']);
        return $param;
    }
}
