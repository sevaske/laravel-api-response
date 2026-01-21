<?php

return [
    // The key used to indicate whether the response was successful.
    // Examples: "success", "status", "ok"
    'success_key' => 'success',

    // The key used for a human-readable response message.
    // This field is optional and will be omitted if no message is provided.
    'message_key' => 'message',

    // The value returned when the response is successful or when the response represents an error.
    'success_value' => true,
    'error_value' => false,

    // Key used for successful response payload.
    'data_key' => 'data',
    // Key used for error details.
    'errors_key' => 'errors',
];
