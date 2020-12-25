<?php

namespace Tswtools\Logx;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logx
{
    public static $msInstance = null;

    public $mLogger          = null;
    public $mFormatter       = null;
    public $mRotatingHandler = [];
    public $mStreamHandler   = [];

    private $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    private $mEnable        = true;
    private $mDaily         = 1;
    private $mIpInclude     = [];
    private $mIpExclude     = [];
    private $mMethodInclude = [];
    private $mMethodExclude = [];
    private $mLevel         = '';
    private $mIp            = '';
    private $mMethod        = '';
    private $mClass         = '';
    private $mLine          = 0;
    private $mMessage       = 0;

    public static function __callStatic($level, $arguments)
    {
        try
        {
            if(!self::$msInstance)
            {
                self::$msInstance = new static();
                self::$msInstance->mLogger = new Logger('xlogger');
                if (!self::$msInstance->mFormatter)
                {
                    $logFormat = "[%datetime%]%message%\n";
                    self::$msInstance->mFormatter = new LineFormatter($logFormat);;
                }

                self::$msInstance->setConfig();
            }

            self::$msInstance->mLevel = $level;

            if (self::$msInstance->mEnable && in_array($level,self::$msInstance->levels))
            {
                self::$msInstance->setLoger();

                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                self::$msInstance->setParameter($arguments, $backtrace);;
                if (self::$msInstance->isValidData())
                {
                    $ip      = self::$msInstance->mIp;
                    $class   = self::$msInstance->mClass;
                    $method  = self::$msInstance->mMethod;
                    $line    = self::$msInstance->mLine;
                    $message = self::$msInstance->mMessage;

                    $message = "[{$ip}[{{$class}::{{$method}][{{$line}] " . $message;
                    self::$msInstance->mLogger->{$level}($message);
                }

            }
        }
        catch (\Exception $e)
        {
        }
    }

    private function setConfig()
    {
        $this->mEnable        = config("logx.enable");
        $this->mDaily         = config("logx.daily");
        $this->mIpInclude     = config("logx.ip.include");
        $this->mIpExclude     = config("logx.ip.exclude");
        $this->mMethodInclude = config("logx.method.include");
        $this->mMethodExclude = config("logx.method.exclude");
        $this->mIp            = $this->getClientIp();
    }

    private function setLoger()
    {
        try
        {
            $logFile  = storage_path(). "/logs/{$this->mLevel}.log";

            if($this->mDaily > 0)
            {
                if(!isset($this->mRotatingHandler[$this->mLevel]))
                {
                    $handler = new RotatingFileHandler($logFile);
                    $handler->setFormatter($this->mFormatter);

                    $this->mRotatingHandler[$this->mLevel] = $handler;
                    $this->mLogger->setHandlers([$handler]);
                }
            }
            else
            {
                if(!isset($this->mStreamHandler[$this->mLevel]))
                {
                    $handler = new StreamHandler($logFile);
                    $handler->setFormatter($this->mFormatter);

                    $this->mStreamHandler[$this->mLevel] = $handler;
                    $this->mLogger->setHandlers([$handler]);
                }
            }
        }
        catch (\Exception $e)
        {
        }
    }

    private function __clone(){}
    private function __construct(){}

    private function isValidData()
    {
        if ($this->mIpInclude && !in_array($this->mIp, $this->mIpInclude))
        {
            return false;
        }

        if ($this->mIpExclude && in_array($this->mIp, $this->mIpExclude))
        {
            return false;
        }

        $class  = $this->mClass;
        $method = $this->mMethod;

        $classAll    = "*::{$method}";
        $methodAll   = "{$class}::*";
        $classMethod = "{$class}::{$method}";

        if ($this->mMethodInclude && !(
                in_array($class, $this->mMethodInclude) ||
                in_array($method, $this->mMethodInclude) ||
                in_array($classAll, $this->mMethodInclude) ||
                in_array($methodAll, $this->mMethodInclude) ||
                in_array($classMethod, $this->mMethodInclude)))
        {
            return false;
        }

        if ($this->mMethodExclude && (
                in_array($class, $this->mMethodExclude) ||
                in_array($method, $this->mMethodExclude) ||
                in_array($classAll, $this->mMethodExclude) ||
                in_array($methodAll, $this->mMethodExclude) ||
                in_array($classMethod, $this->mMethodExclude)))
        {
            return false;
        }

        return true;
    }

    private function setParameter($arguments, $backtrace)
    {
        $backtrace_line = array_shift($backtrace);
        $backtrace_call = array_shift($backtrace);

        $line   = $backtrace_line['line'];
        $method = $backtrace_call['function'];

        $classFull = isset($backtrace_call['class']) ? $backtrace_call['class'] : '';
        $classArray = explode("\\",$classFull);
        $class = end($classArray) ?: '';

        $argumentStr = [];
        foreach ($arguments as $argument)
        {
            $argumentStr[] = json_encode($argument, JSON_UNESCAPED_UNICODE);
        }
        $message = join($argumentStr, ':');

        $this->mLine    = $line;
        $this->mMethod  = $method;
        $this->mClass   = $class;
        $this->mMessage = $message;
    }

    private function getClientIp()
    {
        if (isset($_SERVER['REMOTE_ADDR']))
        {
            $cip = $_SERVER['REMOTE_ADDR'];
        }
        elseif (getenv("REMOTE_ADDR"))
        {
            $cip = getenv("REMOTE_ADDR");
        }
        elseif (getenv("HTTP_CLIENT_IP"))
        {
            $cip = getenv("HTTP_CLIENT_IP");
        }
        else
        {
            $cip = "";
        }

        if ($cip == '::1')
        {
            $cip = "127.0.0.1";
        }

        return $cip;
    }
}