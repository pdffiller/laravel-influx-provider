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
            $tags['serverName'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($record['level'])) {
            $tags['severity'] = $this->rfc5424ToSeverity($record['level']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $tags['endpoint_url'] = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $tags['method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($record['context']['user_id'])) {
            $tags['user_id'] = $record['context']['user_id'];
        }

        if (isset($record['context']['project_id'])) {
            $tags['project_id'] = $record['context']['project_id'];
        }

        if (isset($record['context']['file'])) {
            $tags['file'] = $this->replaceDigitData($record['context']['file']);
        }

        if (isset($record['context']['event']['api_stats'][0])) {
            foreach ($record['context']['event']['api_stats'][0] as $k => $v) {
                if (is_string($v) || is_int($v)) {
                    $tags[$k] = $v;
                }
            }
        }

        if (count($tags)) {
            foreach ($tags as $k => $v) {
                if (is_numeric($v)) {
                    $message['fields'][$k] = (int) $v;
                }
            }

            $message['tags'] = $tags;
        }

        if (isset($message['fields']['Debug']['message'])) {
            $message['fields']['Debug']['message'] = $this->trimLines($message['fields']['Debug']['message']);
        }

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
        $str = preg_replace('~\/[0-9]+~', '/*', $str);
        $str = preg_replace('~\=[0-9]+~', '=*', $str);
            
        return $str;
    }

    private function trimLines($message)
    {
        $limit = config('influxdb.log_message_lines_limit');
        if ($limit) {
            $message_array = explode("\n", $message);
            if ( $limit < count($message_array)) {
                $message = implode("\n", array_slice($message_array, 0, $limit));
            }
        }

        return $message;
    }
}
