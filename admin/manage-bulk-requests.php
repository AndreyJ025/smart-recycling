<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

// Get all bulk requests with business and quote information
$requests_query = "SELECT br.*, 
                    u.business_name,
                    (SELECT COUNT(*) FROM tbl_quotes WHERE request_id = br.id) as quote_count,
                    (SELECT COUNT(*) FROM tbl_quotes WHERE request_id = br.id AND status = 'accepted') as accepted_quotes
                  FROM tbl_bulk_requests br
                  JOIN tbl_user u ON br.business_id = u.id
                  ORDER BY br.created_at DESC";
$result = $conn->query($requests_query);
$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bulk Requests - EcoLens Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-[Poppins] bg-[#1b1b1b]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <a href="admin-dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="pt-24 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Bulk Recycling Requests</h1>
                <p class="text-white/60">Manage and monitor business bulk recycling requests</p>
            </div>

            <!-- Requests Table -->
            <div class="bg-white/5 rounded-xl backdrop-blur-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-[#436d2e]/20">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Business</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Request Type</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Materials</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Quantity</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Preferred Date</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Quotes</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium"><?php echo htmlspecialchars($request['business_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white"><?php echo ucfirst($request['request_type']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white"><?php echo str_replace(',', ', ', ucwords($request['material_types'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white"><?php echo number_format($request['estimated_quantity']); ?> kg</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white"><?php echo date('M j, Y', strtotime($request['preferred_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-500">
                                            Pending
                                        </span>
                                    <?php elseif ($request['status'] === 'approved'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-500">
                                            Approved
                                        </span>
                                    <?php elseif ($request['status'] === 'completed'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-500">
                                            Completed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white">
                                        <?php if ($request['quote_count'] > 0): ?>
                                            <?php echo $request['quote_count']; ?> quote(s)
                                            <?php if ($request['accepted_quotes'] > 0): ?>
                                                <span class="text-green-400">(<?php echo $request['accepted_quotes']; ?> accepted)</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            No quotes yet
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white/60"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>