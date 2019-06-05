<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 18/07/2017
 * Time: 14:33
 */

namespace AppBundle\Utils;

class LockTableHelper
{
    private $statements = [];

    // 'LOCK TABLES survapp_afp_report AS s0_ WRITE, survapp_afp_report WRITE;'

    public function addLock($mode, $tableName, $alias = null)
    {
        $statement = "";
        $statement.= " " . $tableName;
        if (isset($alias)) {
            $statement.= " AS " . $alias;
        }
        $statement.= " " . $mode;

        $this->statements[] = $statement;
    }

    public function getStatement()
    {
        if (count($this->statements) == 0) {
            return '' ;
        }

        $count = 1 ;
        $result = "LOCK TABLES";

        foreach ($this->statements as $s) {
            $result .= " " . $s;
            if ($count < count($this->statements)) {
                $result .= ",";
            }

            $count+=1;
        }

        $result .= ";";

        return $result ;
    }
}