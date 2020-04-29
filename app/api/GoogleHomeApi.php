<?php
class GoogleHomeApi {
    function response()
    {
        header('Content-Type: application/json');
        $update_response = file_get_contents("php://input");
        $update = json_decode($update_response, true);
        echo '"RichResponse": {
            "items": [
              {
                "simpleResponse": {
                  "textToSpeech": "test speech"
                }
              }
            ]
          }';
    }

}
