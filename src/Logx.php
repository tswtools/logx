<?php

namespace Tswtools\Logx;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logx
{
    private $levels = ['debug','info','notice','warning','error','critical','alert','emergency'];

    public function __call($level, $arguments)
    {
        try
        {
            if (config("logx.enable"))
            {
                if (in_array($level, $this->levels))
                {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                    $data      = $this->setPutParameter($level, $arguments, $backtrace);
                    if ($this->isValidData($data))
                    {
                        $this->writeLog($data);
                    }
                }
            }
        }
        catch (\Exception $e)
        {
        }
    }

    private function isValidData($data)
    {
        $ipInclude = config('logx.ip.include');
        $ipExclude = config('logx.ip.exclude');

        $methodInclude = config('logx.method.include');
        $methodExclude = config('logx.method.exclude');

        if ($ipInclude && !in_array($data['ip'], $ipInclude))
        {
            return false;
        }

        if ($ipExclude && in_array($data['ip'], $ipExclude))
        {
            return false;
        }

        $class  = $data['class'];
        $method = $data['method'];

        $classAll    = "*::{$method}";
        $methodAll   = "{$class}::*";
        $classMethod = "{$class}::{$method}";

        if ($methodInclude && !(
                in_array($class, $methodInclude) ||
                in_array($method, $methodInclude) ||
                in_array($classAll, $methodInclude) ||
                in_array($methodAll, $methodInclude) ||
                in_array($classMethod, $methodInclude)))
        {
            return false;
        }

        if ($methodExclude && (
                in_array($class, $methodExclude) ||
                in_array($method, $methodExclude) ||
                in_array($classAll, $methodExclude) ||
                in_array($methodAll, $methodExclude) ||
                in_array($classMethod, $methodExclude)))
        {
            return false;
        }

        return true;
    }

    private function setPutParameter($level,$arguments, $backtrace)
    {
        $backtrace_line = $backtrace[1]; // 哪一行调用的log方法
        $backtrace_call = $backtrace[2]; // 谁调用的log方法

        $line   = $backtrace_line['line'];
        $method = $backtrace_call['function'];

        $classFull = isset($backtrace_call['class']) ? $backtrace_call['class'] : '';
        $classArray = explode("\\",$classFull);
        $class = end($classArray) ?: '';

        $ip = $this->getClientIp();

        $message = $this->geFormatArguments($arguments);
        return [
            'level'   => $level,
            'line'    => $line,
            'method'  => $method,
            'class'   => $class,
            'ip'      => $ip,
            'message' => $message
        ];
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

    private function geFormatArguments($arguments)
    {
        $argumentStr = [];
        foreach ($arguments as $argument)
        {
            $argumentStr[] = json_encode($argument, JSON_UNESCAPED_UNICODE);
        }

        return join($argumentStr, ':');
    }

    private function getMessage($data)
    {
        $class   = $data['class'];
        $method  = $data['method'];
        $line    = $data['line'];
        $ip      = $data['ip'];
        $message = $data['message'];

        $message = "[$ip][{$class}::{$method}][{$line}] " . $message;

        return $message;
    }

    private function writeLog($data)
    {
        try
        {
            $level = $data['level'];
            $logger = new Logger('logx');

            $logFormat = "[%datetime%]%message%\n\n";
            $formatter = new LineFormatter($logFormat);

            $logFile = storage_path() . "/logs/logx-{$level}.log";

            $daily = config('logx.daily');

            if ($daily)
            {
                $handler = new RotatingFileHandler($logFile);
            }
            else
            {
                $handler = new StreamHandler($logFile);
            }

            //设定显示模式
            $handler->setFormatter($formatter);

            //清除之前的handler数据
            if (!empty($logger->getHandlers()))
            {
                $logger->popHandler();
            }
            //设置新的handler
            $logger->pushHandler($handler);

            //写入数据
            $message = $this->getMessage($data);
            $logger->{$level}($message);
        }
        catch (\Exception $e)
        {
        }
    }
}