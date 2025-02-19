<?php
session_start();
require_once 'database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php");
    exit();
}

// Get date range parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch data
$query = "
    SELECT 
        r.created_at,
        u.fullname,
        r.item_name,
        r.item_quantity,
        sc.name as center_name,
        r.points
    FROM tbl_remit r
    JOIN tbl_user u ON r.user_id = u.id
    JOIN tbl_sortation_centers sc ON r.sortation_center_id = sc.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Prepare data for JSON
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Get summary data
$summaryData = [
    'totalItems' => $conn->query("
        SELECT SUM(item_quantity) as count 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['count'] ?? 0,
    
    'totalPoints' => $conn->query("
        SELECT SUM(points) as total 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['total'] ?? 0,
    
    'activeUsers' => $conn->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['count'] ?? 0,
    
    'mostCommonItem' => $conn->query("
        SELECT item_name, COUNT(*) as frequency
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY item_name
        ORDER BY frequency DESC
        LIMIT 1
    ")->fetch_assoc()['item_name'] ?? 'N/A',
    
    'mostActiveCenter' => $conn->query("
        SELECT sc.name, COUNT(*) as visits
        FROM tbl_remit r
        JOIN tbl_sortation_centers sc ON r.sortation_center_id = sc.id
        WHERE DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY sc.id
        ORDER BY visits DESC
        LIMIT 1
    ")->fetch_assoc()['name'] ?? 'N/A'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report - EcoLens</title>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
</head>
<body>
    <script>
    // Prepare data from PHP
    const reportData = <?php echo json_encode($data); ?>;
    const summaryData = <?php echo json_encode($summaryData); ?>;
    const dateRange = {
        start: '<?php echo $start_date; ?>',
        end: '<?php echo $end_date; ?>'
    };

    // Create workbook
    const wb = XLSX.utils.book_new();
    
    // Add title and date range
    const headerData = [
        ['EcoLens Recycling Report'],
        ['Period:', dateRange.start, 'to', dateRange.end],
        [],
        ['Date', 'User', 'Item', 'Quantity', 'Center', 'Points']
    ];

    // Add main data
    reportData.forEach(row => {
        headerData.push([
            row.created_at,
            row.fullname,
            row.item_name,
            parseInt(row.item_quantity),
            row.center_name,
            parseInt(row.points)
        ]);
    });

    // Add summary section
    headerData.push(
        [],
        ['Summary Statistics'],
        ['Total Items', summaryData.totalItems],
        ['Total Points', summaryData.totalPoints],
        ['Active Users', summaryData.activeUsers],
        ['Most Common Item', summaryData.mostCommonItem],
        ['Most Active Center', summaryData.mostActiveCenter]
    );

    // Create worksheet
    const ws = XLSX.utils.aoa_to_sheet(headerData);

    // Set column widths
    ws['!cols'] = [
        {wch: 15}, // Date
        {wch: 25}, // User
        {wch: 20}, // Item
        {wch: 10}, // Quantity
        {wch: 25}, // Center
        {wch: 10}  // Points
    ];

    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Recycling Report");

    // Generate Excel file
    XLSX.writeFile(wb, `EcoLens_Report_${new Date().toISOString().split('T')[0]}.xlsx`);

    // Redirect back to analytics page
    window.location.href = 'analytics.php?start_date=' + dateRange.start + '&end_date=' + dateRange.end;
    </script>
</body>
</html>