<?php
namespace STIG\Helpers;

abstract class GridUtils extends \APP_DbObject
{ 

    public static function createGrid($defaultValue = null)
    {
        $g = [];
        for ($y = ROW_MIN; $y <= ROW_MAX; $y++) {
            for ($x = COLUMN_MIN; $x <= COLUMN_MAX; $x++) {
                $g[$x][$y] = $defaultValue;
            }
        }
        return $g;
    }
        
    /**
     * @param int $row
     * @param int $column
     * @return bool
     */
    public static function isCoordOutOfGrid($row, $column)
    {
        if($column > COLUMN_MAX) return true;
        if($column < COLUMN_MIN) return true;
        if($row > ROW_MAX) return true;
        if($row < ROW_MIN) return true;

        return false;
    }
    protected function isValidCell($cell)
    {
        return !GridUtils::isCoordOutOfGrid($cell['y'],$cell['x']);
    }
            
    public static function array_usearch($array, $test)
    {
        $found = false;
        $iterator = new \ArrayIterator($array);

        while ($found === false && $iterator->valid()) {
            if ($test($iterator->current())) {
                $found = $iterator->key();
            }
            $iterator->next();
        }

        return $found;
    }
    public static function searchCell($cells, $x, $y)
    {
        return self::array_usearch($cells, function ($cell) use ($x, $y) {
            return $cell['x'] == $x && $cell['y'] == $y;
        });
    }
        
    public static function getNeighbours($cell)
    {
        $directions = [
            ['x' => -1, 'y' => 0],
            ['x' => +1, 'y' => 0],
            ['x' => 0, 'y' => -1],
            ['x' => 0, 'y' => +1],
        ];

        $cells = [];
        foreach ($directions as $dir) {
            $newCell = [
                'x' => $cell['x'] + $dir['x'],
                'y' => $cell['y'] + $dir['y'],
            ];
            if (self::isValidCell($newCell)) {
                $cells[] = $newCell;
            }
        }
        return $cells;
    }
  /**
   * getReachableCellsAtDistance: perform a Disjktra shortest path finding :
   *   - $cell : starting pos
   *   - $d : max distance we are looking for
   *   - $costCallback : function used to compute cost
   * 
   * //Taken from bga-memoir project used to get units movements range
   */
  public static function getReachableCellsAtDistance(
    $startingCell,
    $d,
    $costCallback
  ) {
    $queue = new \SplPriorityQueue();
    $queue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
    $queue->insert(['cell' => $startingCell], 0);
    $markers = self::createGrid(false);

    while (!$queue->isEmpty()) {
      // Extract the top node and adds it to the result
      $node = $queue->extract();
      $cell = $node['data']['cell'];
      $cell['d'] = -$node['priority'];
      $pos = ['x' => $cell['x'], 'y' => $cell['y']];
      $mark = $markers[$pos['x']][$pos['y']];
      if ($mark !== false) {
        continue;
      }
      $markers[$pos['x']][$pos['y']] = $cell;

      // Look at neighbours
      $neighbours = self::getNeighbours($pos);
      foreach ($neighbours as $nextCell) {
        $cost = $costCallback($cell, $nextCell, $d);
        $dist = $cell['d'] + $cost;
        $t = $markers[$nextCell['x']][$nextCell['y']];
        if ($t !== false) {
          continue;
        }

        if ($dist <= $d) {
          //$nextCell['cost'] = $cost;
          $queue->insert(['cell' => $nextCell], -$dist);
        }
      }
    }

    // Extract the reachable cells
    $cells = [];
    foreach ($markers as $col) {
      foreach ($col as $cell) {
        if ($cell !== false && $cell['d'] > 0) {
          $cells[] = $cell;
        }
      }
    }

    return [$cells, $markers];
  }
}


