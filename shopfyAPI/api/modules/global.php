<?php

class GlobalMethods {

    public function sendPayload($data, $remarks, $message, $code) {
        $status = array("remarks" => $remarks, "message" => $message);
        http_response_code($code);
        return array(
            "status" => $status,
            "payload" => $data,
            "timestamp" => date_create()
        );
    }

    public function sendResponse($message, $error, $statusCode) {
        $response = ['message' => $message];

        if ($error !== null) {
            $response['error'] = $error;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getResponse($data, $remarks, $error, $statusCode) {
        $response = ['remarks' => $remarks];

        if ($data !== null) {
            $response['data'] = $data; 
        }

        if ($error !== null) {
            $response['error'] = $error;
        }

        http_response_code($statusCode);
        return $response;
    }
}

?>
