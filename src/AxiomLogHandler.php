<?php

namespace Jplhomer\Axiom;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AxiomLogHandler extends AbstractProcessingHandler
{
    protected string $dataset;

    protected string $apiToken;

    /**
     * @param  string  $dataset  The Axiom dataset name
     * @param  string  $apiToken  The Axiom API token
     * @param  int|string  $level  The minimum logging level at which this handler will be triggered
     * @param  bool  $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($dataset, $apiToken, $level = null, bool $bubble = true)
    {
        $this->dataset = $dataset;
        $this->apiToken = $apiToken;

        if ($level === null) {
            $level = Logger::DEBUG;
        }

        parent::__construct($level, $bubble);
    }

    /**
     * Starts a fresh curl session for the given endpoint and returns its handler.
     *
     * @return resource cURL handle
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
     * @param  array  $record
     */
    protected function write($record): void
    {
        $ch = $this->loadCurlHandle();

        $data = $record['context'];
        $data['message'] = $record['message'];
        $data['level'] = $record['level'];
        $data['channel'] = $record['channel'];

        // Axiom expects an array of records, so we wrap the record in an array.
        $data = [$data];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_exec($ch);
        curl_close($ch);
    }
}
