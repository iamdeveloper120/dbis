<?php

namespace App\Entities\ClientSessions;

use CodeIgniter\Entity\Entity;

class DailySession extends Entity
{
    protected $dates = ['created_at', 'updated_at'];


    /**
     * Returns the full name of the user.
     */
    public function supervisor_name(): string
    {
        return trim(implode(' ', [$this->supervisor_first_name, $this->supervisor_last_name]));
    }

    public function instructor_name(): string
    {
        return trim(implode(' ', [$this->instructor_first_name, $this->instructor_last_name]));
    }

    public function client_name(): string
    {
        return trim(implode(' ', [$this->client_first_name, $this->client_last_name]));
        //return trim(implode(' ', [$this->client_first_name]));
    }

    public function session_status(): string
    {
        if ($this->status == 1) {
            return 'In Progress';
        }
        if ($this->status == 2) {
            return 'In Review';
        }
        if ($this->status == 3) {
            return 'Completed and Processed';
        }
        if ($this->status == 4) {
            return 'Completed and Partially Processed';
        }
         return '?';
    }

    public function is_session(): string
    {
        if ($this->status == 0) {
            return 'No Session';
        }
        if ($this->status == 1) {
            return 'Yes';
        }
         return '?';
    }
    public function rating(): string
    {
        if ($this->session_quality_rating == 1) {
            return 'Poor';
        } else if ($this->session_quality_rating == 2) {
            return 'Good';
        } else if ($this->session_quality_rating == 3) {
            return 'Excellent';
        } else {
            return '';
        }
    }
    public function is_program_change(): string
    {

        if ($this->program_change_made > 0) {
            $times = '';
            if ($this->program_change_made == 1) {
                $times = $this->program_change_made . ' Time';
            } else {
                $times = $this->program_change_made . ' Times';
            }
            return $times;
        } else {
            return '';
        }
    }
}
