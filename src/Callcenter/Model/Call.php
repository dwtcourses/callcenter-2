<?php
declare(strict_types=1);

namespace Callcenter\Model;

final class Call implements \JsonSerializable
{
    /**
     * @var string
     */
    public $callerid;

    /**
     * @var string
     */
    public $uid;

    /**
     * @var string
     */
    public $queue = '';

    /**
     * @var string
     */
    public $status = "NA";

    /**
     * @var int
     */
    public $time;

    /**
     * @var bool
     */
    public $answered = false;

    /**
     * Caller constructor.
     * @param string $callerid
     * @param string $uid
     */
    public function __construct(string $callerid, string $uid)
    {
        $this->callerid = $callerid;
        $this->uid = $uid;

        $this->time = time();
    }

    /**
     * @param string $queue
     */
    public function setQueue(string $queue) : void
    {
        $this->queue = $queue;
        $this->setStatus('QUEUED');
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getQueue() : string
    {
        return $this->queue;
    }

    /**
     * @return int
     */
    public function getDuration() : int
    {
        return time() - $this->time;
    }

    /**
     * @return bool
     */
    public function isAnswered() : bool
    {
        return $this->answered;
    }

    /**
     * @param string $status
     * @return bool
     */
    public function setStatus(string $status) : bool
    {
        $status = strtoupper($status);

        if ($this->status == $status) {
            return false;
        }

        if ($status == 'INCALL') {
            $this->answered = true;
        }

        if ($status == 'HANGUP') {
            $status = ($this->answered)?"HANGUP":"ABANDON";
        }

        $this->status = $status;
        $this->time = time();

        return true;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $str = (($this->callerid)?:"anonymous")."|{$this->status}";
        
        if ($this->queue) {
            $str .= "|{$this->queue}";
        }
        
        return $str;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'CALL',
            'id' => $this->uid,
            'callerid' => $this->callerid,
            'status' => $this->status,
            'queue' => $this->queue,
            'time' => $this->time,
            'answered' => $this->answered,
        ];
    }

    /**
     * @return Report
     */
    public function getReport() : Report
    {
        $ts = time();

        $duration = $ts - $this->time;
        $at = $ts - $duration;

        $report = new Report();
        $report->type = 'CALL';
        $report->id = $this->callerid;
        $report->status = $this->status;
        $report->timestamp = date('Y-m-d H:i:s', $at);
        $report->duration = $duration;
        $report->queue = $this->queue;

        return $report;
    }
}
