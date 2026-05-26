# SATUSEHAT API Catalog Platform

A native PHP + Tailwind CSS platform for exploring and testing SATUSEHAT FHIR R4 API endpoints.

## Features

- **API Module Catalog**: Browse all available FHIR R4 resource modules
- **Interactive API Testing**: Test API endpoints directly from the browser
- **Modal Response Display**: View API responses in a modal without page redirects
- **Module Search**: Quickly find modules using the search functionality
- **Environment Switching**: Toggle between Sandbox and Production environments
- **Token Caching**: Automatic token caching for better performance

## Project Structure

```
satu-sehat/
├── config/
│   └── config.php          # SATUSEHAT API configuration
├── public/
│   ├── index.php           # Homepage - Module list display
│   ├── catalog.php         # API catalog with endpoint testing
│   ├── api_call.php        # API request handler
│   └── index.php
├── storage/
│   └── cache/              # Token cache directory
├── .env                    # Environment variables
└── README.md
```

## Requirements

- PHP 7.4+
- cURL extension
- JSON extension

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env`
3. Configure your SATUSEHAT credentials in `.env`
4. Ensure the `storage/cache/` directory is writable

## Configuration

Edit `config/config.php` or set environment variables:

```php
$satusehat = [
    'sandbox' => [
        'auth_url' => 'https://api-sandbox.satusehat.com',
        'base_url' => 'https://api-satusehat.com',
        'organization_id' => 'your-org-id',
        'client_id' => 'your-client-id',
        'client_secret' => 'your-client-secret'
    ],
    'production' => [
        // Production configuration read .env.example file
    ]
];
```

## Usage

### Homepage (`index.php`)
Displays all available API modules in a grid format. Click any module to navigate to the catalog.

### API Catalog (`catalog.php`)
- View all endpoints for a selected module
- Test API endpoints with custom parameters
- View responses in a modal popup
- Search for specific modules using the sidebar search

### Testing an API Endpoint
1. Select a module from the homepage or sidebar
2. Choose an endpoint from the list
3. Fill in required parameters (if any)
4. Click "Test API"
5. View the response in the modal that appears

## Available Modules

| Module | Description |
|--------|-------------|
| Patient | Patient management resources |
| Encounter | Patient visit records |
| Condition | Diagnosis and problem list |
| Observation | Vital signs and lab results |
| Procedure | Surgical interventions |
| AllergyIntolerance | Allergy records |
| CarePlan | Care plans and coordination |
| ClinicalImpression | Clinical assessments |
| Medication | Medication resources |
| MedicationRequest | Prescriptions |
| MedicationDispense | Dispensing records |
| MedicationStatement | Medication history |
| Specimen | Lab test specimens |
| DiagnosticReport | Lab results |
| ServiceRequest | Service orders |
| Appointment | Scheduling |
| Consent | Patient consent |
| Coverage | Insurance coverage |
| Organization | Healthcare organization |
| Immunization | Vaccination records |
etc...

## API Response Modal

When testing an API endpoint:
- The response appears in a modal on the same page
- No page redirect occurs
- Close the modal or press ESC to continue
- Error messages are displayed with clear formatting

## Screenshots

### Homepage - Module List
![Homepage Screenshot](screenshoot/screenshoot_1.png)

### API Catalog - Endpoint Testing
![API Catalog Screenshot](screenshoot/screenshoot_2.png)

## Security Notes

- Token is cached locally for performance
- HTTPS is required for API calls
- Rate limiting: 1 request per minute after failed authentication

## License

© 2026 Kemenkes RI (Ministry of Health Indonesia)

## Version

v1.0 - Modern UI with modal responses