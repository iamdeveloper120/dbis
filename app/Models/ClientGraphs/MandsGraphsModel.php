<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model; 

class MandsGraphsModel extends Model
{
    
 
    public function getMandsSummaryDataForGraphs($client_id, $start_date, $end_date)
    {

        $builder = $this->db->table('view_mands_session_data_summary');
        $builder->select('*');
        $builder->where('client_id', $client_id);

        if ($start_date !== NULL && $end_date !== NULL) {
            $builder->where('session_date >= ', $start_date);
            $builder->where('session_date <=', $end_date);
        }
        $builder->orderBy('session_date', 'ASC');
        $query = $builder->get();

        $dates = [];
        $total_mands = [];
        $total_peer_mands = [];
        $total_eye_contact_mands = [];
        $variety_of_mands = [];

        $FPP = [];
        $PPP  = [];
        $GP  = [];
        $V  = [];
        $IV  = [];
        $Item  = [];
        $MO  = [];
        $TMO  = [];
        $total  = [];

        $mand_errors_S = [];
        $mand_errors_R = [];
        $mand_errors_IA = [];

        $vocal_response_SS = [];
        $vocal_response_WA = [];
        $vocal_response_IW = [];
        $vocal_response_AF = [];

        $prompt_delay_improve = [];
        $prompt_delay_remain = [];
        $prompt_delay_worse = [];

        $echoic_improve = [];
        $echoic_remain = [];
        $echoic_worse = [];

        foreach ($query->getResult() as $row) {
            $daily_date = stringToDate($row->session_date, CC_DATE_FORMAT);
            $dates[] = $daily_date;

            $total_mands[] = $row->total_mands; //($session->skills_retained == 0 ? 'NULL' : $session->skills_retained);
            $total_peer_mands[] = $row->total_peer_mands ?? 0;
            $total_eye_contact_mands[] = $row->total_eye_contact_mands ?? 0;
            $variety_of_mands[] = $row->variety_of_mands; //($session->doi == 0 ? 'NULL' : $session->doi);

            $FPP[] = $row->total_FPP_mands;
            $PPP[] = $row->total_PPP_mands;
            $GP[] = $row->total_GP_mands;
            $V[] = $row->total_V_mands;
            $IV[] = $row->total_IV_mands;
            $Item[] = $row->total_Item_mands;
            $MO[] = $row->total_MO_mands;
            $TMO[] = $row->total_TMO_mands;

            $mand_errors_S[] = $row->percentage_of_scrolled_mands != 0 ? $row->percentage_of_scrolled_mands : null;
            $mand_errors_R[] = $row->percentage_of_repetitive_mands != 0 ? $row->percentage_of_repetitive_mands : null;
            $mand_errors_IA[] = $row->percentage_of_inappropriate_autoclitics != 0 ? $row->percentage_of_inappropriate_autoclitics : null;

            $vocal_response_SS[] = $row->percentage_of_SS_attempts;
            $vocal_response_WA[] = $row->percentage_of_WA_attempts;
            $vocal_response_IW[] = $row->percentage_of_IW_attempts;
            $vocal_response_AF[] = $row->percentage_of_AF_attempts;

            $prompt_delay_improve[] = $row->percentage_of_improved_with_prompt_delay;
            $prompt_delay_remain[] = $row->percentage_of_remained_with_prompt_delay;
            $prompt_delay_worse[] = $row->percentage_of_worsened_with_prompt_delay;

            $echoic_improve[] = $row->percentage_of_improved_with_echoic_trials;
            $echoic_remain[] = $row->percentage_of_remained_with_echoic_trials;
            $echoic_worse[] = $row->percentage_of_worsened_with_echoic_trials;
        }

        $total_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Total Mands',
                    'data' => $total_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $variety_of_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Variety of Mands',
                    'data' => $variety_of_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $peer_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Peer Mands',
                    'data' => $total_peer_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $eye_contact_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Eye Contact Mands',
                    'data' => $total_eye_contact_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];

        $prompt_level_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'FPP',
                    'data' => $FPP,
                    'lineTension' => 0,
                    'fill' => false, // Set fill to false to remove colored area
                    'borderColor' => 'rgba(54, 162, 235, 1)', // Blue
                    'pointBorderColor' => 'rgba(54, 162, 235, 1)',
                    'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'PPP',
                    'data' => $PPP,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(255, 159, 64, 1)', // Orange
                    'pointBorderColor' => 'rgba(255, 159, 64, 1)',
                    'pointBackgroundColor' => 'rgba(255, 159, 64, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'GP',
                    'data' => $GP,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(75, 192, 192, 1)', // Teal
                    'pointBorderColor' => 'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' => 'rgba(75, 192, 192, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'V',
                    'data' => $V,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(153, 102, 255, 1)', // Purple
                    'pointBorderColor' => 'rgba(153, 102, 255, 1)',
                    'pointBackgroundColor' => 'rgba(153, 102, 255, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'IV',
                    'data' => $IV,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(255, 206, 86, 1)', // Yellow
                    'pointBorderColor' => 'rgba(255, 206, 86, 1)',
                    'pointBackgroundColor' => 'rgba(255, 206, 86, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'Item',
                    'data' => $Item,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(255, 99, 132, 1)', // Red
                    'pointBorderColor' => 'rgba(255, 99, 132, 1)',
                    'pointBackgroundColor' => 'rgba(255, 99, 132, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'MO',
                    'data' => $MO,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgba(75, 192, 75, 1)', // Dark Green
                    'pointBorderColor' => 'rgba(75, 192, 75, 1)',
                    'pointBackgroundColor' => 'rgba(75, 192, 75, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'TMO',
                    'data' => $TMO,
                    'lineTension' => 0,
                    'fill' => false,
                    'borderColor' => 'rgb(61,12,2)', // Pink
                    'pointBorderColor' => 'rgb(61,12,2)',
                    'pointBackgroundColor' => 'rgb(61,12,2)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2
                ]
            ]
        ];


        $mand_errors_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Scrolling',
                    'data' => $mand_errors_S,
                    'lineTension' =>  0,
                    'fill' => false, // Set fill to false to remove colored area
                    'borderColor' =>  'rgba(54, 162, 235, 1)', // Blue
                    'pointBorderColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBackgroundColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'Repetitive ',
                    'data' => $mand_errors_R,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(255, 159, 64, 1)', // Orange
                    'pointBorderColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBackgroundColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'Inappropriate Autoclitics ',
                    'data' => $mand_errors_IA,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(75, 192, 192, 1)', // Teal
                    'pointBorderColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],

            ]
        ];

        $vocal_response_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'SS',
                    'data' => $vocal_response_SS,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(255, 206, 86, 1)', // Yellow
                    'pointBorderColor' =>  'rgba(255, 206, 86, 1)',
                    'pointBackgroundColor' =>  'rgba(255, 206, 86, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'WA',
                    'data' => $vocal_response_WA,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(255, 99, 132, 1)', // Red
                    'pointBorderColor' =>  'rgba(255, 99, 132, 1)',
                    'pointBackgroundColor' =>  'rgba(255, 99, 132, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'IW',
                    'data' => $vocal_response_IW,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(75, 192, 192, 1)', // Teal
                    'pointBorderColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'AF',
                    'data' => $vocal_response_AF,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(153, 102, 255, 1)', // Purple
                    'pointBorderColor' =>  'rgba(153, 102, 255, 1)',
                    'pointBackgroundColor' =>  'rgba(153, 102, 255, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]
            ]
        ];

        $prompt_delay_trial_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Improved',
                    'data' => $prompt_delay_improve,
                    'lineTension' =>  0,
                    'fill' => false, // Set fill to false to remove colored area
                    'borderColor' =>  'rgba(54, 162, 235, 1)', // Blue
                    'pointBorderColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBackgroundColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'Deteriorated ',
                    'data' => $prompt_delay_worse,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(255, 159, 64, 1)', // Orange
                    'pointBorderColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBackgroundColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'No Change',
                    'data' => $prompt_delay_remain,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(75, 192, 192, 1)', // Teal
                    'pointBorderColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],

            ]
        ];

        $echoic_trial_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Improved',
                    'data' => $echoic_improve,
                    'lineTension' =>  0,
                    'fill' => false, // Set fill to false to remove colored area
                    'borderColor' =>  'rgba(54, 162, 235, 1)', // Blue
                    'pointBorderColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBackgroundColor' =>  'rgba(54, 162, 235, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'Deteriorated ',
                    'data' => $echoic_worse,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(255, 159, 64, 1)', // Orange
                    'pointBorderColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBackgroundColor' =>  'rgba(255, 159, 64, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],
                [
                    'label' => 'No Change',
                    'data' => $echoic_remain,
                    'lineTension' =>  0,
                    'fill' => false,
                    'borderColor' =>  'rgba(75, 192, 192, 1)', // Teal
                    'pointBorderColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' =>  'rgba(75, 192, 192, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ],

            ]
        ];



        $result_array = [
            'total_mands' => $total_mands_data,
            'peer_mands_data' => $peer_mands_data,
            'eye_contact_mands_data' => $eye_contact_mands_data,
            'variety_of_mands' => $variety_of_mands_data,
            'prompt_level_data' => $prompt_level_data,
            'mand_errors_data' => $mand_errors_data,
            'vocal_response_data' => $vocal_response_data,
            'prompt_delay_trial_data' => $prompt_delay_trial_data,
            'echoic_trial_data' => $echoic_trial_data,
        ];
        return $result_array;
    }
}
