<?php
namespace STIG\Helpers;

abstract class Utils extends \APP_DbObject
{
    public static function filter(&$data, $filter)
    {
        $data = array_values(array_filter($data, $filter));
    }

    public static function die($args = null)
    {
        if (is_null($args)) {
            throw new \BgaVisibleSystemException(
                implode('<br>', self::$logmsg)
            );
        }
        throw new \BgaVisibleSystemException(json_encode($args));
    }

    /**
     * Return a string corresponding to an assoc array of resources
     */
    public static function resourcesToStr($resources)
    {
        $descs = [];
        foreach ($resources as $resource => $amount) {
            if (in_array($resource, ['sources', 'sourcesDesc', 'cId'])) {
                continue;
            }

            if ($amount == 0) {
                continue;
            }

            $descs[] = $amount . '<' . strtoupper($resource) . '>';
        }
        return implode(',', $descs);
    }

    public static function tagTree($t, $tags)
    {
        foreach ($tags as $tag => $v) {
            $t[$tag] = $v;
        }

        if (isset($t['childs'])) {
            $t['childs'] = array_map(function ($child) use ($tags) {
                return self::tagTree($child, $tags);
            }, $t['childs']);
        }
        return $t;
    }

    /**
     * @param array $array
     * @param int $key in array
     * @param int $value to change from array datas
     */
    static function updateDataFromArray ($array, $key, &$value) {
        if(array_key_exists($key,$array)) $value = $array[$key];
    }
    
    /**
     * @param int $refTurn
     * @return array of int
     */
    static function calcFutureTurnsActions ($refTurn) {
        $nextTurns = 0;
        $nextActions = 0;
        $k = $refTurn + 1;
        while($k<=TURN_MAX){
            //Each turn provides K actions -> max actions = 55
            $nextActions += $k;
            $nextTurns++;
            $k++;
        }
        return ['nextTurns' => $nextTurns, 'nextActions' => $nextActions,];
    }
 
    /**
     * @param int $refTurn Turn to refer
     * @param int $done Number of actions done
     * @return int number of actions unused in turn
     */
    static function countRemainingActionsInTurn($refTurn,$done) {
        $max = self::countMaxActionsInTurn($refTurn);
        return $max - $done;
    }

    /**
     * @param int $refTurn Turn to refer
     * @return int number of actions to be played in turn
     */
    static function countMaxActionsInTurn($refTurn){
        return min(MAX_PERSONAL_ACTIONS_BY_TURN, $refTurn); //10 actions for turns 11,12,...
    }
}
