<?php
namespace STIG\Exceptions;
use STIG\Core\Game;

class UserException extends \BgaUserException
{
    public function __construct($str)
    {
        parent::__construct(Game::get()::translate($str));
    }
}
?>
