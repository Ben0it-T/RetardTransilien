<?php

namespace RetardTransilien\Utils;

class Util
{
    function convertMtoDHM($time) {
        $zero   = new \DateTime('@0');
        $offset = new \DateTime('@' . $time * 60);
        $diff   = $zero->diff($offset);
        if ($time>=1440) {
            $result = $diff->format('%aj %hh %imn');
        }
        elseif ($time < 1440 AND $time>=60) {
            $result = $diff->format('%hh %imn');
        }
        else {
            $result = $diff->format('%imn');
        }
        return $result;
    }
}