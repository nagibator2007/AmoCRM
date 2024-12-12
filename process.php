<?php
// Данные из формы
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$price = $_POST['price'];

// Данные для авторизации в AmoCRM
$access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijg1OGJlMWIwMWVkYmNhYjkwYWMwMGZiMjM5MmVjYmQ5ZTE3NzFkZGY5ZjU3OWFkZWIxMDI5YWZjMTRiYzM3ZDRjMjM4NmUyMmJmZDk5NmEyIn0.eyJhdWQiOiI5ZDRiYmIxNS1kZGQxLTQyNGYtOTE4Zi1iMjYzY2E3ZWQyNmYiLCJqdGkiOiI4NThiZTFiMDFlZGJjYWI5MGFjMDBmYjIzOTJlY2JkOWUxNzcxZGRmOWY1NzlhZGViMTAyOWFmYzE0YmMzN2Q0YzIzODZlMjJiZmQ5OTZhMiIsImlhdCI6MTczMzkxNTcyNywibmJmIjoxNzMzOTE1NzI3LCJleHAiOjE3MzQyMjA4MDAsInN1YiI6IjExODg4MDc0IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMyMTE3Nzc0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiYzFlZWNmOGQtZDY2ZS00YWU0LThlNDYtOGY2YmYwZjM5MDg1IiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.oom4QoBiELDSFRqi2Zivc8fRH69dh9RwyAXgQKE7scOBz_jz8dwHI5yQmN_-WyCkclKSf-OOMM1kLD-9yX2fpBQa7PI2i5F7dkXXA95deP0TQgKwIALBEP95AMPYd5QJixfAqBM3Q8Rj0LwQq1PQ_10BraPye1OqTqVL-iuhG3aoXImDUcNOJbT7PxvXEUnPbGWjH-qMsBlyL0RwRhpAspEwsAPFHynwKyNw1nBlzUjgavUPsZlm955oSwtOg9mLzwSGdgj7uF53qYDC2s4wVh7l9sDz-67Wy3JFvDGoivREq2VBUs5gE1tOEsgUJZao3X28MItuDC55oM933ffazA';
$subdomain = 'murderer721';

// Функция для запросов в AmoCRM
function amoRequest($method, $url, $data = null) {
    global $access_token;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ],
    ]);
    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Логирование ошибок
    if ($httpCode >= 400) {
        file_put_contents('log_errors.txt', "Error ($httpCode): " . $response . PHP_EOL, FILE_APPEND);
    }

    return json_decode($response, true);
}

// Создание контакта
$contactData = [
    'name' => $name,
    'custom_fields_values' => [
        [
            'field_code' => 'EMAIL',
            'values' => [['value' => $email, 'enum_code' => 'WORK']]
        ],
        [
            'field_code' => 'PHONE',
            'values' => [['value' => $phone, 'enum_code' => 'WORK']]
        ]
    ]
];
$contact = amoRequest('POST', "https://$subdomain.amocrm.ru/api/v4/contacts", [$contactData]);
$contactId = $contact['_embedded']['contacts'][0]['id'] ?? null;

// Логирование контакта
file_put_contents('log_contact.txt', print_r($contact, true));

// Создание сделки
if ($contactId) {
    $leadData = [
        'name' => "Сделка с $name", // Название сделки
        'price' => (int)$price, 
        '_embedded' => [
            'contacts' => [['id' => $contactId]] // Привязка контакта
        ]
    ];
    file_put_contents('log_lead_data.txt', print_r($leadData, true)); // Логирование данных сделки перед отправкой
    $leadResponse = amoRequest('POST', "https://$subdomain.amocrm.ru/api/v4/leads", [$leadData]);
    file_put_contents('log_leads.txt', print_r($leadResponse, true)); // Логирование ответа
}

echo 'Заявка успешно отправлена!';