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

        return $message;
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

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $fields['serverName'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($record['level'])) {
            $fields['severity'] = $this->rfc5424ToSeverity($record['level']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $fields['URI'] = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $fields['method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($record['context']['user_id'])) {
            $fields['user_id'] = $record['context']['user_id'];
            unset($record['context']['user_id']);
        }

        if (isset($record['context']['project_id'])) {
            $fields['project_id'] = $record['context']['project_id'];
            unset($record['context']['project_id']);
        }

        if (isset($record['context']['file'])) {
            $fields['file'] = $this->replaceDigitData($record['context']['file']);
            unset($record['context']['file']);
        }

        if (isset($record['context']['id'])) {
            unset($record['context']['id']);
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

    private function replaceDigitData($str)
    {
        $pattern = '~\/[0-9]+~';
        return preg_replace($pattern, '/*', $str);
    }
}
