<?php

namespace App\Controllers\AppConfiguration;

use App\Controllers\AdminController;
use DateTimeZone;

/**
 * General Site Settings
 */
class GeneralSettingsController extends AdminController
{
    /**
     * The theme to use.
     *
     * @var string
     */

    /**
     * Displays the site's general settings.
     */
    public function general()
    {
        $timezoneAreas = [];

        foreach (timezone_identifiers_list() as $timezone) {
            if (strpos($timezone, '/') === false) {
                $timezoneAreas[] = $timezone;
                continue;
            }

            [$area, $zone] = explode('/', $timezone);
            if (!in_array($area, $timezoneAreas, true)) {
                $timezoneAreas[] = $area;
            }
        }

        $currentTZ     = setting('App.appTimezone');
        $currentTZArea = strpos($currentTZ, '/') === false
            ? $currentTZ
            : substr($currentTZ, 0, strpos($currentTZ, '/'));

        $this->page_title = 'General Settings';

        return  view('AppConfiguration/GeneralSettings/index', [
            'timezones'       => $timezoneAreas,
            'currentTZArea'   => $currentTZArea,
            'timezoneOptions' => $this->getTimezones($currentTZArea),
            'dateFormat'      => setting('App.dateFormat') ?: 'M j, Y',
            'timeFormat'      => setting('App.timeFormat') ?: 'g:i A',
            'page_title'      =>  $this->page_title,
        ]);
    }

    /**
     * Saves the general settings
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function saveGeneral()
    {        
        $rules =    [
            'siteName' => [
                'label'  => 'Site Name',
                'rules'  => 'required|string',
                'errors' => [
                    'required' => '{field} Required',
                    'string' => '{field} excepts only string value',
                ],
            ],
            'timezone' => [
                'label'  => 'Time Zone',
                'rules'  => 'required|string',
                'errors' => [
                    'required' => '{field} Required',
                    'string' => '{field} excepts only string value',
                ],
            ],
            'dateFormat' => [
                'label'  => 'Date Format',
                'rules'  => 'required|string',
                'errors' => [
                    'required' => '{field} Required',
                    'string' => '{field} excepts only string value',
                ],
            ],
            'timeFormat' => [
                'label'  => 'Time Format',
                'rules'  => 'required|string',
                'errors' => [
                    'required' => '{field} Required',
                    'string' => '{field} excepts only string value',
                ],
            ], 'weekStartDay' => [
                'label'  => 'Week Start Day',
                'rules'  => 'required|string',
                'errors' => [
                    'required' => '{field} Required',
                    'string' => '{field} excepts only string value',
                ],
            ],
            'processingResolutionDays' => [
                'label'  => 'Processing Resolution Days',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} excepts only Number value',
                ],
            ],
        ];

        $data = [
            'siteName'   => $this->request->getPost('siteName'),
            'timezone' => $this->request->getPost('timezone'),
            'dateFormat'   => $this->request->getPost('dateFormat'),
            'timeFormat'   => $this->request->getPost('timeFormat'),
            'weekStartDay' => $this->request->getPost('weekStartDay'),
            'processingResolutionDays' => $this->request->getPost('processingResolutionDays'),
        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Validation Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            setting('App.siteName', $this->request->getPost('siteName'));
            setting('App.appTimezone', $this->request->getPost('timezone'));
            setting('App.dateFormat', $this->request->getPost('dateFormat'));
            setting('App.timeFormat', $this->request->getPost('timeFormat'));
            setting('App.weekStartDay', $this->request->getPost('weekStartDay'));
            setting('App.sessionProcessingResolutionDays', $this->request->getPost('processingResolutionDays'));
            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record created successfully',
                'data' => ''
            ];
        }

        return $this->response->setJSON($response);
    }

    /**
     * AJAX method to list available timezones within
     * a single timezone area  (AMERICA, AFRICA, etc)
     */
    public function getTimezones(?string $area = null): string
    {
      
        $area = $area === null
            ? $this->request->getPost('timezoneArea')
            : $area;
 
        $ids = [
            'Africa'     => DateTimeZone::AFRICA,
            'America'    => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Arctic'     => DateTimeZone::ARCTIC,
            'Asia'       => DateTimeZone::ASIA,
            'Atlantic'   => DateTimeZone::ATLANTIC,
            'Australia'  => DateTimeZone::AUSTRALIA,
            'Europe'     => DateTimeZone::EUROPE,
            'Indian'     => DateTimeZone::INDIAN,
            'Pacific'    => DateTimeZone::PACIFIC,
        ];

        $options = [];

        if ($area === 'UTC') {
            $options['UTC'] = 'UTC';
        } else {
            foreach (timezone_identifiers_list($ids[$area]) as $timezone) {
                $formattedTimezone  = str_replace('_', ' ', $timezone);
                $formattedTimezone  = str_replace($area . '/', '', $formattedTimezone);
                $options[$timezone] = $formattedTimezone;
            }
        }

        return view('AppConfiguration/GeneralSettings/timezones', [
            'options'    => $options,
            'selectedTZ' => setting('App.appTimezone'),
        ]);
    }
}
