<?php

namespace Jplhomer\Axiom;

use CurlHandle;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

// For Monolog 3.0
if (class_exists('Monolog\Level')) {
    class_alias('Monolog\Level', 'Jplhomer\Axiom\MonologLevel');
}

// For Monolog 3.0
if (class_exists('Monolog\LogRecord')) {
    class_alias('Monolog\LogRecord', 'Jplhomer\Axiom\MonologRecord');
}

class AxiomLogHandler extends AbstractProcessingHandler
{
    protected string $dataset;

    protected string $apiToken;

    /**
     * @param  string  $dataset  The Axiom dataset name
     * @param  string  $apiToken  The Axiom API token
     * @param  int|string|\Monolog\Level  $level  The minimum logging level at which this handler will be triggered
     * @param  bool  $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($dataset, $apiToken, $level = null, bool $bubble = true)
    {
        $this->dataset = $dataset;
        $this->apiToken = $apiToken;

        // Set default level based on Monolog version
        if ($level === null) {
            // @phpstan-ignore-next-line
            $level = class_exists('Monolog\Level') ? \Monolog\Level::Debug : Logger::DEBUG;
        }

        parent::__construct($level, $bubble);
    }

    /**
     * Starts a fresh curl session for the given endpoint and returns its handler.
     *
     * @return CurlHandle|resource cURL handle
     */
    private function loadCurlHandle()
    {
        $url = "https://api.axiom.co/v1/datasets/{$this->dataset}/ingest";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$this->apiToken,
            'Content-Type: application/json',
        ]);

        return $ch;
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param  array|\Monolog\LogRecord  $record
     */
    protected function write($record): void
    {
        $ch = $this->loadCurlHandle();

        // Handle both Monolog 2.0 (array) and 3.0 (LogRecord object)
        if (is_object($record) && class_exists('Monolog\LogRecord')) {
            // Monolog 3.0
            $data = $record->context;
            $data['message'] = $record->message;
            $data['level'] = $record->level;
            $data['channel'] = $record->channel;
        } else {
            // Monolog 2.0
            $data = $record['context'];
            $data['message'] = $record['message'];
            $data['level'] = $record['level'];
            $data['channel'] = $record['channel'];
        }

        // Axiom expects an array of records, so we wrap the record in an array.
        $data = [$data];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_exec($ch);
        curl_close($ch);
    }
}
