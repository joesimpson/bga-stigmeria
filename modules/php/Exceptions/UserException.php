<?php
namespace STIG\Exceptions;
use STIG\Core\Game;

class UserException extends \Bga\GameFramework\UserException
{
    public function __construct($str)
    {
        parent::__construct(($str));
    }
}
?>
