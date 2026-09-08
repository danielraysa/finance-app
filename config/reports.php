<?php

return [
    'company_name' => env('REPORT_COMPANY_NAME', env('APP_NAME', 'Finance App')),
    'company_address' => env('REPORT_COMPANY_ADDRESS'),
    'company_contact' => env('REPORT_COMPANY_CONTACT'),
    'logo' => env('REPORT_COMPANY_LOGO', public_path('logo_fppti_hijau_640x480.jpg')),
];
