<?php

namespace Magium\Clairvoyant\Listener;

use Magium\Clairvoyant\Writer\WriterInterface;

class RequestListener
{

    private $events = [];
    private $extra = [];
    private $startTime;
    private $id;

    private $writer;

    private $type;

    public function __construct(WriterInterface $writer, $projectId)
    {
        $this->startTime = microtime(true);

        if (function_exists('random_bytes')) {
            $this->id = uniqid(bin2hex(random_bytes(32)), true);
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $this->id = uniqid(bin2hex(openssl_random_pseudo_bytes(32)), true);
        } else {
            // we don't need the ID cryptographically secure
            $this->id = uniqid(mt_rand(0, PHP_INT_MAX), true);
        }
        register_shutdown_function([$this, 'shutdown']);

        $this->writer = $writer;
        $this->noteEvent('init', ['project_id' => $projectId]);
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType($default = null)
    {
        if (!$this->type) {
            return $default;
        }
        return $default;
    }

    public function noteEvent($type, $event)
    {
        $event['microtime'] = microtime(true);
        if (!isset($this->events[$type])) {
            $this->events[$type] = [];
        }
        $this->events[$type][] = $event;
    }

    public function addExtra($extra)
    {
        $this->extra[] = $extra;
    }

    public function shutdown()
    {
        if (!$this->events) return;

        if (PHP_SAPI == 'cli') {
            $context = [
                'script' => $_SERVER['PHP_SELF'],
                'args' => $_SERVER['argv'],
                'type' => $this->getType('cli')
            ];
        } else {
            $context = [
                'request_uri' => $_SERVER['REQUEST_URI'],
                'script' => $_SERVER['SCRIPT_FILENAME'],
                'query_string' => isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:null,
                'session_id' => session_id(),
                'cookies' => $_COOKIE,
                'type' => $this->getType('http')
            ];
        }
        $this->noteEvent('shutdown', []);
        $endTime = microtime(true);
        $context['host'] = gethostname();
        $context['sapi'] = PHP_SAPI;
        $context['elapsed_time'] =  $endTime - $this->startTime;
        $context['end_time'] = $endTime;
        $context['events'] = $this->events;
        $context['extra'] = $this->extra;


        $this->writer->write($this->id, $context);
        $this->events = [];
    }
}
