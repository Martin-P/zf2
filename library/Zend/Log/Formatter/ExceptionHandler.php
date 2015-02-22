<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Formatter;

use DateTime;

class ExceptionHandler implements FormatterInterface
{
    /**
     * Format specifier for DateTime objects in event data
     *
     * @see http://php.net/manual/en/function.date.php
     * @var string
     */
    protected $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * This method formats the event for the PHP Exception
     *
     * @param array $event
     * @return string
     */
    public function format($event)
    {
        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTime) {
            $event['timestamp'] = $event['timestamp']->format($this->getDateTimeFormat());
        }

        $output = $event['timestamp'] . ' ' . $event['priorityName'] . ' ('
                . $event['priority'] . ') ' . $event['message'] .' in '
                . $event['extra']['file'] . ' on line ' . $event['extra']['line'];

        if (!empty($event['extra']['trace'])) {
            $outputTrace = '';
            foreach ($event['extra']['trace'] as $trace) {
                foreach ($trace as $key => $value) {
                    if ('object' === $key) {
                        continue;
                    }

                    switch ($key) {
                        case 'file':
                        case 'line':
                        case 'class':
                            $name = ucfirst($key);
                            break;
                        case 'function':
                            $name = 'Func';
                            break;
                        case 'type':
                            $name  = 'Type';
                            $value = $this->getType($value);
                            break;
                        case 'args':
                            $name  = 'Args';
                            $value = print_r($value, true);
                            break;
                    }

                    $outputTrace .= str_pad($name, 6) . ": {$value}\n";
                }
            }
            $output .= "\n[Trace]\n" . $outputTrace;
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }

    /**
     * Get the type of a function
     *
     * @param string $type
     * @return string
     */
    protected function getType($type)
    {
        switch ($type) {
            case "::":
                return "static";
            case "->":
                return "method";
            default:
                return $type;
        }
    }
}
