<?php

namespace App\Entities\Mands;

use App\Libraries\MandsOptionMetadata;
use CodeIgniter\Entity\Entity;

class MandsSessionData extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];

    public function get_prompt_level_label()
    {
        return MandsOptionMetadata::promptLevelLabel($this->prompt_level);
    }

    public function get_prompt_level_tooltip()
    {
        return MandsOptionMetadata::promptLevelTooltip($this->prompt_level);
    }

    public function get_mand_error_label()
    {
        return MandsOptionMetadata::mandErrorLabel($this->mands_error);
    }

    public function get_mand_error_tooltip()
    {
        return MandsOptionMetadata::mandErrorTooltip($this->mands_error);
    }

    public function get_initial_input_response_label()
    {
        return $this->lookupVocalResponseLabel($this->initial_attempt);
    }

    public function get_initial_input_response_tooltip()
    {
        return $this->lookupVocalResponseTooltip($this->initial_attempt);
    }

    public function get_prompt_delay_response_label()
    {
        return $this->lookupVocalResponseLabel($this->prompt_delay);
    }

    public function get_prompt_delay_response_tooltip()
    {
        return $this->lookupVocalResponseTooltip($this->prompt_delay);
    }

    public function get_echoic_1_response_label()
    {
        return $this->lookupVocalResponseLabel($this->echoic_1);
    }

    public function get_echoic_1_response_tooltip()
    {
        return $this->lookupVocalResponseTooltip($this->echoic_1);
    }

    public function get_echoic_2_response_label()
    {
        return $this->lookupVocalResponseLabel($this->echoic_2);
    }

    public function get_echoic_2_response_tooltip()
    {
        return $this->lookupVocalResponseTooltip($this->echoic_2);
    }

    public function get_echoic_3_response_label()
    {
        return $this->lookupVocalResponseLabel($this->echoic_3);
    }

    public function get_echoic_3_response_tooltip()
    {
        return $this->lookupVocalResponseTooltip($this->echoic_3);
    }

    public function get_prompt_delay_comparison_label()
    {
        return MandsOptionMetadata::responseComparisonLabel($this->comparison_prompt_delay);
    }

    public function get_echoic_comparison_label()
    {
        return MandsOptionMetadata::responseComparisonLabel($this->comparison_echoic_trial);
    }

    public function get_peer_manding_label()
    {
        return ((int) $this->is_peer_manding === 1) ? 'Yes' : 'No';
    }

    public function get_eye_contact_label()
    {
        return ((int) $this->is_eye_contact === 1) ? 'Yes' : 'No';
    }

    protected function lookupVocalResponseLabel($value)
    {
        return MandsOptionMetadata::vocalResponseLabel($value);
    }

    protected function lookupVocalResponseTooltip($value)
    {
        return MandsOptionMetadata::vocalResponseTooltip($value);
    }
}
