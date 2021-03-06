<?php

namespace Amethyst\ConsumeRules;

use Amethyst\Contracts\ConsumeRuleContract;
use Amethyst\Exceptions;
use Amethyst\Models\ConsumeRule;
use DateInterval;
use MathParser\StdMathParser;

class FrequencyConsumeRule implements ConsumeRuleContract
{
    /**
     * Given the base consumeRule calculate the final consume.
     *
     * @param ConsumeRule $consumeRule
     * @param array       $options
     *
     * @return float
     */
    public function calculate(ConsumeRule $consumeRule, array $options = []): float
    {
        $payload = $consumeRule->payload;

        $options = (object) $options;

        $parser = new StdMathParser();

        if (!isset($payload->frequency_unit)) {
            throw new Exceptions\ConsumeRuleWrongPayloadException('Missing frequency_unit in payload');
        }

        if (!isset($payload->frequency_value)) {
            throw new Exceptions\ConsumeRuleWrongPayloadException('Missing frequency_value in payload');
        }

        if (!isset($options->start)) {
            throw new Exceptions\ConsumeRuleWrongOptionsException('Missing start in options');
        }

        if (!isset($options->end)) {
            throw new Exceptions\ConsumeRuleWrongOptionsException('Missing end in options');
        }

        // Calculate difference in seconds between end and start
        $diff = $options->start->diff($options->end);
        $diff = $this->getDateIntervalPropertyByUnit($diff, $payload->frequency_unit) / $payload->frequency_value;
        $cyclePassed = intval($diff);

        return $cyclePassed;
    }

    /**
     * Retrieve date interval property by unit.
     *
     * @param DateInterval $diff
     * @param string       $unit
     *
     * @return float|int
     */
    public function getDateIntervalPropertyByUnit(DateInterval $diff, $unit)
    {
        if ($unit === 'seconds') {
            return $diff->s + $diff->i * 60 + $diff->h * 60 * 60 + $diff->days * 60 * 60 * 24;
        }

        if ($unit === 'minutes') {
            return $diff->i + $diff->h * 60 + $diff->days * 60 * 24;
        }

        if ($unit === 'hours') {
            return $diff->h + $diff->days * 24;
        }

        if ($unit === 'days') {
            return $diff->days;
        }

        if ($unit === 'months') {
            return $diff->m + $diff->y * 12;
        }

        if ($unit === 'years') {
            return $diff->y;
        }
    }

    /**
     * Converts to seconds.
     *
     * @param string $unit
     * @param float  $value
     *
     * @return float
     */
    public function convertTime(string $unit, float $value)
    {
        if ($unit === 'seconds') {
            return $value;
        }

        if ($unit === 'minutes') {
            return $value * (60);
        }

        if ($unit === 'hours') {
            return $value * (3600);
        }

        if ($unit === 'days') {
            return $value * (86400);
        }

        if ($unit === 'weeks') {
            return $value * (7 * 86400);
        }

        if ($unit === 'months') {
            return $value * (30 * 86400);
        }

        if ($unit === 'years') {
            return $value * (365 * 86400);
        }

        throw new \Exception('Wrong frequencies');
    }
}
