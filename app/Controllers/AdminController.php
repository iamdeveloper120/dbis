<?php

namespace App\Controllers;

 
class AdminController extends BaseController
{
    public $page_title = '';

    public function getResponseObject($status, $statusText, $message, $validationErrors, $data): array
    {
        return  [
            'status' => $status,
            'statusText' => $statusText,
            'message' => $message,
            'validationErrors' => $validationErrors,
            'data' => $data
        ];
    }
}
