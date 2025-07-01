<?php

/**
 * Script to find loyal customers from two log files.
 * Usage: php loyal_customers.php day1.txt day2.txt
 */

// Exit early if required arguments are missing
if ($argc < 3) {
    echo "Usage: php loyal_customers.php day1.txt day2.txt\n";
    exit(1);
}

// Retrieve filenames from command line arguments
$day1File = $argv[1];
$day2File = $argv[2];

/**
 * Parses a log file and returns an associative array:
 * customerId => [pageId => true, ...]
 *
 * This strategy is used because:
 * - It ensures fast lookup and uniqueness using associative arrays.
 * - It allows us to efficiently count how many unique pages each customer visited.
 * 
 * Performance Note:
 * - Reading line-by-line avoids high memory usage for large files.
 * - Using customerId as the key keeps grouping fast (O(1) insertions).
 */
function parseLogFile($filePath)
{
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        die("Could not open file: $filePath\n");
    }

    $customerPages = [];

    while (($line = fgets($handle)) !== false) {
        // Split the line into parts: timestamp, pageId, customerId
        $parts = preg_split('/\s+/', trim($line));
        if (count($parts) < 3) continue;

        [$timestamp, $pageId, $customerId] = $parts;

        // Initialize customer entry if it doesn't exist
        if (!isset($customerPages[$customerId])) {
            $customerPages[$customerId] = [];
        }

        // Use pageId as a key to ensure it's counted uniquely
        $customerPages[$customerId][$pageId] = true;
    }

    fclose($handle);
    return $customerPages;
}

// Parse the logs of both days
$day1Data = parseLogFile($day1File);
$day2Data = parseLogFile($day2File);

// Initialize list to store loyal customer IDs
$loyalCustomers = [];

/**
 * Strategy:
 * - Loop through all customers from Day 1.
 * - Check if the same customer exists in Day 2.
 * - Ensure they visited at least two unique pages on both days.
 *
 * Why this strategy?
 * - Simple and readable.
 * - No complex structures or unnecessary overhead.
 *
 * Performance:
 * - Fast lookups using associative arrays (hashmaps).
 * - Overall complexity is linear O(n), suitable for large logs.
 */
foreach ($day1Data as $customerId => $pagesDay1) {
    if (isset($day2Data[$customerId])) {
        $pagesDay2 = $day2Data[$customerId];
        if (count($pagesDay1) >= 2 && count($pagesDay2) >= 2) {
            $loyalCustomers[] = $customerId;
        }
    }
}

// Display the result
echo "Loyal Customers ID:\n";

if (empty($loyalCustomers)) {
    echo "Loyal customers not available.\n";
} else {
    foreach ($loyalCustomers as $cid) {
        echo "$cid\n";
    }
}
