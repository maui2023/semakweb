<!DOCTYPE html>
<html lang="en">
<head>
  <title>Web Check Status</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>
</head>
<body>
    <div class="container">
  <h2>Semakan Laman Web</h2>
  <p>https://sabily.info</p>   
<table id="customers">
<?php

// Define the website URL that you want to monitor
$url = "https://sabily.info";

// Use the PHP curl function to send a request to the website URL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Check the response code to determine whether the website is up or down
if ($httpCode >= 200 && $httpCode < 300) {
    $status = "UP";
} else {
    $status = "DOWN";
}

// Load the existing status data from the JSON file
$statusData = json_decode(file_get_contents('status.json'), true);

// Add the new website status to the status data array
$statusData[] = [
    'timestamp' => time(),
    'status' => $status,
];

// Save the updated status data to the JSON file
file_put_contents('status.json', json_encode($statusData));

// Calculate the count and percentage of "UP" and "DOWN" status updates
$totalCount = count($statusData);
$upCount = array_count_values(array_column($statusData, 'status'))['UP'] ?? 0;
$downCount = array_count_values(array_column($statusData, 'status'))['DOWN'] ?? 0;
$upPercentage = ($totalCount > 0) ? round($upCount / $totalCount * 100, 2) : 0;
$downPercentage = ($totalCount > 0) ? round($downCount / $totalCount * 100, 2) : 0;

// Define the number of results to display per page
$resultsPerPage = 10;

// Determine the current page number based on the "page" URL parameter
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $resultsPerPage;
$pageCount = ceil($totalCount / $resultsPerPage);

// Retrieve the status data for the current page
$pageData = array_slice($statusData, $offset, $resultsPerPage);

// Show the website status updates and percentage as a table
echo '<tr><th>Timestamp</th><th>Status</th></tr>';
foreach ($pageData as $status) {
    $timestamp = date('Y-m-d H:i:s', $status['timestamp']);
    $statusText = ($status['status'] == 'UP') ? '<span style="color:green;">UP</span>' : '<span style="color:red;">DOWN</span>';
    echo "<tr><td>{$timestamp}</td><td>{$statusText}</td></tr>";
}
echo '</table>';

// Show the pagination links at the bottom of the page
echo '<nav aria-label="Website Status Pagination">';
echo '<ul class="pagination">';

// Show the Previous button if not on the first page
if ($page > 1) {
    $previousPage = $page - 1;
    echo '<li class="page-item"><a class="page-link" href="?page=' . $previousPage . '">Previous</a></li>';
}

// Show up to 10 pages of results
$startPage = max(1, $page - 4);
$endPage = min($startPage + 9, $pageCount);
for ($i = $startPage; $i <= $endPage; $i++) {
    $isActive = ($i == $page) ? ' active' : '';
    echo "<li class='page-item{$isActive}'><a class='page-link' href='?page={$i}'>{$i}</a></li>";
}

// Show the Next button if not on the last page
if ($page < $pageCount) {
    $nextPage = $page + 1;
    echo '<li class="page-item"><a class="page-link" href="?page=' . $nextPage . '">Next</a></li>';
}

echo '</ul>';
echo '</nav>';

// Use a meta refresh tag to reload the page every 5 minutes
echo '<meta http-equiv="refresh" content="300">';
echo "<p>Website Status: UP ({$upCount}/{$upPercentage}%), DOWN ({$downCount}/{$downPercentage}%)</p>";

 $demo = $upPercentage;
?>  
  <div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="<?=$demo?>" aria-valuemin="0" aria-valuemax="100" style="width:<?=$demo?>%">
    <?=$demo?>%</div>
  </div>
</body>
</html>
