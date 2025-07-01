<?php
header('Content-Type: application/json');

// --- 0. Option to use DynamoDB or Sample Data ---
// Default: use local sample data unless ?source=dynamodb is set
$useDynamoDB = (isset($_GET['source']) && $_GET['source'] === 'dynamodb');

// --- 1. Get Filters from Frontend ---
$timeRange = $_GET['time_range'] ?? '24h';
$organizationFilter = $_GET['organization'] ?? 'all';

// Calculate the start Unix timestamp for the requested time range
$endTime = time();
$startTime = 0;
switch ($timeRange) {
    case '1h': $startTime = strtotime('-1 hour', $endTime); break;
    case '12h': $startTime = strtotime('-12 hours', $endTime); break;
    case '24h': $startTime = strtotime('-24 hours', $endTime); break;
    case '7d': $startTime = strtotime('-7 days', $endTime); break;
    case '30d': $startTime = strtotime('-30 days', $endTime); break;
    default: $startTime = strtotime('-24 hours', $endTime); break;
}

$eventData = [];

if ($useDynamoDB) {
    // --- 2A. Fetch from DynamoDB ---
    require __DIR__ . '/../vendor/autoload.php';

    $client = new Aws\DynamoDb\DynamoDbClient([
        'region' => 'ap-southeast-1',
        'version' => 'latest',
        'profile' => 'default'
    ]);

    $filterExpression = '#timestamp BETWEEN :start AND :end';
    $expressionAttributeNames = ['#timestamp' => 'timestamp'];
    $expressionAttributeValues = [
        ':start' => ['S' => date('Y-m-d\TH:i:s.000\Z', $startTime)],
        ':end' => ['S' => date('Y-m-d\TH:i:s.000\Z', $endTime)]
    ];

    if ($organizationFilter !== 'all') {
        $filterExpression .= ' AND #org = :org';
        $expressionAttributeNames['#org'] = 'customer_id';
        $expressionAttributeValues[':org'] = ['S' => $organizationFilter];
    }

    try {
        $result = $client->scan([
            'TableName' => 'Alert_data',
            'FilterExpression' => $filterExpression,
            'ExpressionAttributeNames' => $expressionAttributeNames,
            'ExpressionAttributeValues' => $expressionAttributeValues
        ]);
        // Flatten each DynamoDB item
        $flattened = [];
        foreach ($result['Items'] as $item) {
            $flattened[] = dynamodbItemToArray($item);
        }
        echo json_encode($flattened);
        exit;
    } catch (Aws\DynamoDb\Exception\DynamoDbException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DynamoDB error: ' . $e->getMessage()]);
        exit;
    }
} else {
    // --- 2B. Use Sample Data from external file ---
    require_once __DIR__ . '/sample_event_data.php';

    // Remove this block:
    // $rawDataSample = [
    //     // ... (sample data as before)
    // ];

    // Continue with the duplication/randomization logic if you want more data:
    $allRawData = [];
    $now = time();
    $intervalSeconds = 30 * 24 * 60 * 60; // 30 days in seconds

    for ($k = 0; $k < 50; $k++) { // Generate 50 copies of the sample data
        foreach ($rawDataSample as $sampleEntry) {
            $cloneEntry = $sampleEntry;
            // Randomize the timestamp of the cloned entry within the last 30 days
            $randomPastSeconds = rand(0, $intervalSeconds);
            $randomTimestamp = $now - $randomPastSeconds;
            $cloneEntry['ts']['S'] = date('Y-m-d\TH:i:s.000\Z', $randomTimestamp);

            // Randomize customer_id to ensure distribution for org filter
            $customers = ['zait', 'customerA', 'customerB', 'customerC'];
            $cloneEntry['customer_id']['S'] = $customers[array_rand($customers)];

            $allRawData[] = $cloneEntry;
        }
    }


    // --- 3. Process and Filter Data ---
    foreach ($allRawData as $rawEvent) {
        // Extract timestamp and convert to Unix timestamp for comparison
        $eventTimestampStr = $rawEvent['ts']['S'];
        // Handle the 'Z' for UTC and parse correctly
        $eventTimestamp = strtotime($eventTimestampStr);

        // Apply time filter
        if ($eventTimestamp < $startTime || $eventTimestamp > $endTime) {
            continue; // Skip events outside the selected time range
        }

        // Apply organization filter
        $customerId = $rawEvent['customer_id']['S'] ?? 'unknown';
        if ($organizationFilter !== 'all' && $customerId !== $organizationFilter) {
            continue; // Skip events not belonging to the selected organization
        }

        // Extract other relevant fields using null coalescing for safety
        $message = $rawEvent['message']['S'] ?? 'No message';
        $sourceIp = $rawEvent['source']['M']['ip']['S'] ?? 'N/A';
        $urlOriginal = $rawEvent['url']['M']['original']['S'] ?? 'N/A';
        $httpStatusCode = $rawEvent['http']['M']['response']['M']['status_code']['S'] ?? 'N/A';
        $hostName = $rawEvent['host']['M']['hostname']['S'] ?? 'N/A';
        $tags = array_map(function($tag) { return $tag['S']; }, $rawEvent['tags']['L'] ?? []);
        $filePath = $rawEvent['log']['M']['file']['M']['path']['S'] ?? 'N/A';
        $userAgent = $rawEvent['user_agent']['M']['original']['S'] ?? 'N/A';
        $userName = $rawEvent['user']['M']['name']['S'] ?? 'N/A';
        $method = $rawEvent['http']['M']['request']['M']['method']['S'] ?? 'N/A';
        $bytesTransferred = $rawEvent['http']['M']['response']['M']['body']['M']['bytes']['S'] ?? 'N/A';


        // --- Infer Rule and Severity ---
        $rule = 'General Log';
        $severity = 'Informational';

        // Rule and Severity Inference Logic
        if (strpos($message, 'failed login') !== false || strpos($message, 'authentication failure') !== false) {
            $rule = 'Brute Force/Login Failure';
            $severity = 'High';
        } elseif (strpos($urlOriginal, 'etc/passwd') !== false || strpos($urlOriginal, 'php?id=1%27') !== false || strpos($urlOriginal, 'admin/setup.php') !== false) {
            $rule = 'Web Attack (SQLi/LFI/RCE)';
            $severity = 'Critical';
        } elseif (in_array('sqli-attempt', $tags)) {
            $rule = 'SQL Injection Attempt';
            $severity = 'Critical';
        } elseif ($httpStatusCode === '400') {
            $rule = 'Bad Request / Malformed Request';
            $severity = 'Medium';
        } elseif ($httpStatusCode === '401' || $httpStatusCode === '403') {
            $rule = 'Unauthorized Access Attempt';
            $severity = 'High';
        } elseif (strpos($message, 'Out of memory') !== false) {
            $rule = 'System Resource Exhaustion';
            $severity = 'High';
        } elseif (strpos($message, 'malicious file detected') !== false || strpos($filePath, 'malware') !== false) {
            $rule = 'Malware Detected';
            $severity = 'Critical';
        } elseif (strpos($message, 'port scan') !== false) {
            $rule = 'Port Scan Activity';
            $severity = 'High';
        } elseif (strpos($message, 'data exfiltration') !== false) {
            $rule = 'Data Exfiltration';
            $severity = 'Critical';
        } elseif (strpos($message, 'DoS') !== false || strpos($message, 'high volume of requests') !== false) {
            $rule = 'Denial of Service (DoS)';
            $severity = 'Critical';
        } elseif (strpos($message, 'anomaly') !== false || strpos($message, 'unusual network traffic') !== false) {
            $rule = 'Unusual Network Traffic';
            $severity = 'Medium';
        } elseif (strpos($message, 'file modification') !== false || strpos($message, 'file integrity') !== false) {
            $rule = 'File Integrity Monitoring Alert';
            $severity = 'High';
        } elseif (strpos($message, 'policy violation') !== false) {
            $rule = 'Security Policy Violation';
            $severity = 'Medium';
        }

        // Overwrite severity if status code suggests it for general logs not caught by specific rules
        if ($severity === 'Informational') {
            if (in_array($httpStatusCode, ['400', '401', '403'])) {
                $severity = 'Medium';
            } elseif (in_array($httpStatusCode, ['500', '502', '503', '504'])) {
                $severity = 'High'; // Server errors can indicate issues
            }
        }


        // --- Construct the event in the expected frontend format ---
        $eventData[] = [
            'timestamp' => date('Y-m-d H:i:s', $eventTimestamp), // Format for display
            'rule' => $rule,
            'reason' => $message, // Use the full message as the reason
            'severity' => $severity,
            // Include other fields for the child row details
            'customer_id' => $customerId,
            'source_ip' => $sourceIp,
            'url_original' => $urlOriginal,
            'http_status_code' => $httpStatusCode,
            'host_name' => $hostName,
            'tags' => implode(', ', $tags), // Convert array to string for display
            'file_path' => $filePath,
            'user_agent' => $userAgent,
            'user_name' => $userName,
            'http_method' => $method,
            'bytes_transferred' => $bytesTransferred
        ];
    }
}

// Output as JSON
echo json_encode($eventData);

function dynamodbItemToArray($item) {
    if (isset($item['M'])) {
        $result = [];
        foreach ($item['M'] as $key => $value) {
            $result[$key] = dynamodbItemToArray($value);
        }
        return $result;
    } elseif (isset($item['L'])) {
        return array_map('dynamodbItemToArray', $item['L']);
    } elseif (isset($item['S'])) {
        return $item['S'];
    } elseif (isset($item['N'])) {
        return $item['N'];
    } elseif (isset($item['BOOL'])) {
        return $item['BOOL'];
    } elseif (isset($item['NULL'])) {
        return null;
    }
    return $item;
}
?>