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

}

function array_uunique($array, $comparator)
{
    $unique_array = [];
    do {
        $element = array_shift($array);
        $unique_array[] = $element;

        $array = array_udiff($array, [$element], $comparator);
    } while (count($array) > 0);

    return $unique_array;
}
