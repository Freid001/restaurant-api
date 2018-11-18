<?php

namespace App;

/**
 * Class Helper
 * @package App
 */
class Helper
{
    /**
     * @param array $keys
     * @param array $arr
     * @return bool
     */
    public function arrayKeysExists(array $keys, array $arr) : bool
    {
        return !array_diff_key(array_flip($keys), $arr);
    }

    /**
     * @param array $arr
     * @return array
     */
    public function arrayRemoveAssocKeys(array $arr) : array
    {
        $return = [];
        foreach($arr as $key => $value){
            $return[] = $value;
        }

        return $return;
    }
}