<?php
// List cases from Filevine API
function listFilevineCases() {
    $apiUrl = "https://api.filevine.io/your-endpoint"; // Replace with correct endpoint
    $apiKey = "your_api_key"; // Replace with your API key
    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    // Use cURL to make the request
    $ch = curl_init($apiUrl . '/cases'); // Assuming this endpoint returns all cases
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true); // Return decoded JSON
}

$cases = listFilevineCases();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Cases</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Case Name</th>
                    <th>Generate Analysis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case): ?>
                <tr>
                    <td><?= $case['id']; ?></td>
                    <td><?= $case['name']; ?></td>
                    <td>
                        <a href="case_analysis.php?caseId=<?= $case['id']; ?>" class="btn btn-primary">Generate Analysis</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
