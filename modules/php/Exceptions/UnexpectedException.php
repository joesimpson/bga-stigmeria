<?php
namespace STIG\Exceptions;
use STIG\Core\Game;

class UnexpectedException extends \BgaVisibleSystemException
{
    protected $code;

    public function __construct($code,$str)
    {
        parent::__construct($str);
        $this->code = $code;
    }
}
?>
