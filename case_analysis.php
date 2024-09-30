<?php
require 'vendor/autoload.php'; // For Guzzle and PHPWord

use GuzzleHttp\Client;
use PhpOffice\PhpWord\PhpWord;

function getFilevineCaseData($caseId) {
    // Make API call to Filevine to get case details
    $apiUrl = "https://api.filevine.io/your-endpoint"; // Replace with correct endpoint
    $apiKey = "your_api_key"; // Replace with your API key
    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    // Use cURL to make the request
    $ch = curl_init($apiUrl . '/cases/' . $caseId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true); // Return decoded JSON
}

function analyzeWithChatGPT($inputText) {
    $client = new Client();
    $apiKey = 'your_openai_api_key'; 

    try {
        $response = $client->post('https://api.openai.com/v1/completions', [
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4', // Use gpt-4-turbo
                'prompt' => $inputText,
                'max_tokens' => 1000
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return $responseBody['choices'][0]['text'] ?? 'No analysis available.';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

// Generate DOCX for case analysis
function generateCaseAnalysis($caseId) {
    $caseData = getFilevineCaseData($caseId);
    $policeReport = $caseData['policeReport'] ?? 'No police report available.';
    $medicalRecords = $caseData['medicalRecords'] ?? [];
    $pleadings = $caseData['pleadings'] ?? 'No pleadings available.';

    // Generate the analysis using ChatGPT
    $policeReportAnalysis = analyzeWithChatGPT("Analyze this police report: $policeReport");
    $medicalRecordsAnalysis = '';
    foreach ($medicalRecords as $record) {
        $medicalRecordsAnalysis .= analyzeWithChatGPT("Analyze this medical record: " . json_encode($record)) . "\n";
    }
    $pleadingsAnalysis = analyzeWithChatGPT("Analyze this pleading: $pleadings");

    // Create Word document
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    // Title
    $section->addTitle("Detailed Case Analysis", 1);
    $section->addText("Case ID: " . $caseId);
    $section->addText("Generated on: " . date('Y-m-d'));

    // Police Report Analysis Section
    $section->addTitle("Police Report Analysis", 2);
    $section->addText($policeReportAnalysis);

    // Medical Records Analysis Section
    $section->addTitle("Medical Records Analysis", 2);
    $section->addText($medicalRecordsAnalysis);

    // Pleadings Analysis Section
    $section->addTitle("Pleadings Analysis", 2);
    $section->addText($pleadingsAnalysis);

    // Save to .docx file
    $fileName = 'case_analysis_' . $caseId . '.docx';
    $phpWord->save($fileName, 'Word2007');

    return $fileName; // Return the file name
}

// Generate the case analysis and download as .docx
if (isset($_GET['caseId'])) {
    $caseId = $_GET['caseId'];
    $docxFile = generateCaseAnalysis($caseId);

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $docxFile . '"');
    readfile($docxFile);
}
?>
