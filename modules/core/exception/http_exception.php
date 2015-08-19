<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Exception;
use Exception;

class HttpException extends Exception
{

    public function __construct($responseCode = 500, $customMessage = null, $innerException = null)
    {
        if ($responseCode < 400)
        {
            throw new Exception('HTTP error response codes are either 4xx or 5xx, which ' . $responseCode . ' is not');
        }

        $message = $customMessage;

        if (is_null($customMessage))
        {
            $statusCodes = json_decode(file_get_contents('./includes/status-codes.json'));

            switch($responseCode)
            {
                case 500:
                    $message = get_class($innerException) . ': ' . $innerException->getMessage();
                    break;
                case 404:
                    $message = 'Could not find page';
                    break;
                default:
                    $message = 'Error ' . $responseCode;
                    break;
            }
        }

        if (is_null($innerException))
        {
            parent::__construct($message, $responseCode, $innerException);
        }
        else
        {
            parent::__construct($message, $responseCode);
        }
    }
}