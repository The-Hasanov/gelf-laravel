<?php

namespace LaravelGelf;

use Gelf\Message;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LaravelGelfHandler extends AbstractProcessingHandler
{
    /**
     * @var LaravelGelf
     */
    protected $laravel_gelf;

    /**
     * LaravelGelfHandler constructor.
     * @param int|string  $level
     * @param bool        $bubble
     * @param LaravelGelf $laravel_gelf
     */
    public function __construct($level = Logger::DEBUG, $bubble = true, LaravelGelf $laravel_gelf)
    {
        $this->laravel_gelf = $laravel_gelf;
        parent::__construct($level, $bubble);
    }


    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $this->laravel_gelf->publisher()->publish($this->prepareMessage($record));
    }

    /**
     * @param $record
     * @return Message
     */
    protected function prepareMessage($record)
    {
        $message = $this->getGelfFormatter()->format($record);

        $message->setFacility($this->laravel_gelf->get('facility'));

        foreach ($this->laravel_gelf->get('additional', []) as $key => $value) {
            $message->setAdditional($key, $value);
        }

        return $message;
    }

    /**
     * @return GelfMessageFormatter
     */
    protected function getGelfFormatter()
    {
        $formatterClass = $this->laravel_gelf->get('formatter', GelfMessageFormatter::class);

        if (class_exists($formatterClass)) {
            return new $formatterClass();
        }

        throw new \RuntimeException('Class ' . $formatterClass . ' not exists');
    }

    /**
     * @return \Monolog\Formatter\FormatterInterface|GelfMessageFormatter
     */
    protected function getDefaultFormatter()
    {
        return new GelfMessageFormatter();
    }
}
