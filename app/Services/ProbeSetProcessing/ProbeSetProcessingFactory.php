<?php

namespace App\Services\ProbeSetProcessing;

class ProbeSetProcessingFactory
{
    private static $instances = [];

    public static function create(string $type): ProbeSetProcessingServiceInterface
    {
        if (!isset(self::$instances[$type])) {
            switch ($type) {
                case 'yes_no':
                    self::$instances[$type] = new YesNoProcessingService();
                    break;
                case 'count':
                    self::$instances[$type] = new CountProcessingService();
                    break;
                case 'traffic_light':
                    self::$instances[$type] = new TrafficLightProcessingService();
                    break;
                case 'prompt_level':
                    self::$instances[$type] = new PromptLevelProcessingService();
                    break;
                case 'duration':
                    self::$instances[$type] = new DurationProcessingService();
                    break;
                case 'percentage_yes_no':
                    self::$instances[$type] = new PercentageYesNoProcessingService();
                    break;
                case 'stimulus_program':
                    self::$instances[$type] = new StimulusProcessingService();
                    break;
                default:
                    throw new \Exception("Unsupported probe set type: $type");
            }
        }

        return self::$instances[$type];
    }
}

/*class ProbeSetProcessingFactory
{
    public static function create(string $type): ProbeSetProcessingServiceInterface
    {
        switch ($type) {
            case 'yes_no':
                return new YesNoProcessingService();
            case 'count':
                return new CountProcessingService();
            case 'traffic_light':
                return new TrafficLightProcessingService();
            case 'prompt_level':
                return new PromptLevelProcessingService();
            case 'duration':
                return new DurationProcessingService();
            default:
                throw new \Exception("Unsupported probe set type: $type");
        }
    }
}*/
