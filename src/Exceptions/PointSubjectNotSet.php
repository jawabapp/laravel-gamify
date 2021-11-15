<?php

namespace Jawabapp\Gamify\Exceptions;

use Exception;
use Throwable;

class PointSubjectNotSet extends Exception
{
    protected $message = 'Initialize $subject field in constructor.';
}
