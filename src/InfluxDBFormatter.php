<?php

namespace Pdffiller\LaravelInfluxProvider;

use Monolog\Formatter\NormalizerFormatter;

class InfluxDBFormatter extends NormalizerFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record = parent::format($record);

        $message = $this->prepareMessage($record);

        return $message['formatted'];
    }

    /**
     * @param array $record
     * @return array
     * will be prepared to [name: String, value: Integer, tags: Array, fields: Array, timestamp: Integer]
     */
    protected function prepareMessage(array $record)
    {
        $message['name'] = 'Error';
        $message['value'] = 1;
        $message['timestamp'] = exec('date +%s%N');
        
        $fields['ServerName'] = gethostname();

        if (isset($record['level'])) {
            $fields['Severity'] = $this->rfc5424ToSeverity($record['level']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $fields['requestUri'] = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $fields['requestMethod'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($record['context']['user_id'])) {
            $fields['user_id'] = $record['context']['user_id'];
            unset($record['context']['user_id']);
        }

        if (!empty($record['context'])) {
            foreach($record['context'] as $key => $value) {
                $fields[$key] = $value;
            }
        }

        $message['tags'] = $fields;

        return $message;
    }

    /**
     * @param int $level
     * @return array
     */
    private function rfc5424ToSeverity($level)
    {
        $levels = [
            100 => 'Debugging',
            200 => 'Informational',
            250 => 'Notice',
            300 => 'Warning',
            400 => 'Error',
            500 => 'Critical',
            550 => 'Alert',
            600 => 'Emergency'
        ];
        $result = isset($levels[$level]) ? $levels[$level] : $levels[600];

        return $result;
    }
}
