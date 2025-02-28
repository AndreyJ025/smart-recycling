<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is a business
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'business') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Filter settings
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'all';

// Build the WHERE clause
$where_conditions = ["business_id = ?"]; 
$params = [$user_id];
$param_types = "i";

if ($status_filter !== 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if ($type_filter !== 'all') {
    $where_conditions[] = "request_type = ?";
    $params[] = $type_filter;
    $param_types .= "s";
}

if ($date_filter !== 'all') {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $where_conditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
    }
}

$where_clause = implode(' AND ', $where_conditions);

// Get total requests count for pagination
$count_query = "SELECT COUNT(*) as total FROM tbl_bulk_requests WHERE $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_requests = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_requests / $items_per_page);

// Get requests with pagination
$requests_query = "SELECT * FROM tbl_bulk_requests 
                  WHERE $where_clause 
                  ORDER BY created_at DESC 
                  LIMIT ? OFFSET ?";
$stmt = $conn->prepare($requests_query);
$param_types .= "ii";
$params[] = $items_per_page;
$params[] = $offset;
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Requests - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .bg-overlay {
            background: url('../assets/background.jpg');
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        .bg-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <main class="bg-overlay pt-24 pb-12 px-4">
        <div class="relative z-10">
            <div class="container mx-auto px-4 md:px-6">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">All Bulk Recycling Requests</h1>
                    <p class="text-green-100">View and manage all your recycling requests</p>
                </div>

                <!-- Filters -->
                <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-green-100 mb-2">Status</label>
                            <select name="status" class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white">
                                <option value="all" class="text-black" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" class="text-black" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" class="text-black" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="completed" class="text-black" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="rejected" class="text-black" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-100 mb-2">Type</label>
                            <select name="type" class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white">
                                <option value="all" class="text-black" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="pickup" class="text-black" <?php echo $type_filter === 'pickup' ? 'selected' : ''; ?>>Pickup</option>
                                <option value="drop-off" class="text-black" <?php echo $type_filter === 'drop-off' ? 'selected' : ''; ?>>Drop-off</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-100 mb-2">Time Period</label>
                            <select name="date" class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white">
                                <option value="all" class="text-black" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                                <option value="today" class="text-black" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" class="text-black" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" class="text-black" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-[#436d2e] hover:bg-opacity-90 text-white px-4 py-2 rounded-lg">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Materials</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Quotes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-100 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-800/30">
                                <?php foreach ($requests as $request): 
                                    // Get quote count
                                    $quote_count_query = "SELECT COUNT(*) as count FROM tbl_quotes WHERE request_id = ?";
                                    $stmt = $conn->prepare($quote_count_query);
                                    $stmt->bind_param("i", $request['id']);
                                    $stmt->execute();
                                    $quote_count = $stmt->get_result()->fetch_assoc()['count'];
                                    $stmt->close();
                                ?>
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        <?php echo date('M d, Y', strtotime($request['preferred_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        <?php echo ucfirst($request['request_type']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-white">
                                        <?php echo ucwords(str_replace(',', ', ', $request['material_types'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        <?php echo number_format($request['estimated_quantity']); ?> kg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-200 text-yellow-900',
                                            'approved' => 'bg-green-200 text-green-900',
                                            'completed' => 'bg-blue-200 text-blue-900',
                                            'rejected' => 'bg-red-200 text-red-900'
                                        ];
                                        $status_color = $status_colors[$request['status']] ?? 'bg-gray-200 text-gray-900';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_color; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($quote_count > 0): ?>
                                        <button onclick="viewQuotes(<?php echo $request['id']; ?>)" class="text-green-300 hover:text-white">
                                            View <?php echo $quote_count; ?> quote<?php echo $quote_count > 1 ? 's' : ''; ?>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-green-100/50">No quotes yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white/60">
                                        <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center space-x-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>&date=<?php echo $date_filter; ?>" 
                           class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-[#436d2e] text-white' : 'bg-white/10 text-white hover:bg-white/20'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-[#1b1b1b] py-6">
        <div class="container mx-auto px-4">
            <p class="text-center text-white/60">&copy; <?php echo date('Y'); ?> EcoLens. All rights reserved.</p>
        </div>
    </footer>

    <div id="quoteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-[#1b1b1b] rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-[#1b1b1b] border-b border-white/10 p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-1">Service Quotes</h2>
                        <p class="text-green-100 text-sm">Review and manage quotes for your request</p>
                    </div>
                    <button onclick="closeQuoteModal()" class="text-white/60 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div id="quoteContent" class="p-6"></div>
        </div>
    </div>
    
    <!-- Add the JavaScript before the closing </body> tag -->
    <script>
    async function viewQuotes(requestId) {
        try {
            const response = await fetch(`get-quotes.php?request_id=${requestId}`);
            const data = await response.json();
            
            if (data.error) {
                alert(data.error);
                return;
            }
    
            let content = `
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Request Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div>
                                <p class="text-sm text-green-200">Request Type</p>
                                <p class="text-white font-medium">${data.request.request_type}</p>
                            </div>
                            <div>
                                <p class="text-sm text-green-200">Materials</p>
                                <p class="text-white font-medium">${data.request.material_types.replace(',', ', ')}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <p class="text-sm text-green-200">Quantity</p>
                                <p class="text-white font-medium">${data.request.estimated_quantity} kg</p>
                            </div>
                            <div>
                                <p class="text-sm text-green-200">Preferred Date</p>
                                <p class="text-white font-medium">${new Date(data.request.preferred_date).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
            `;
    
            if (data.quotes.length === 0) {
                content += `
                    <div class="text-center py-8">
                        <p class="text-white/60">No quotes have been received yet for this request.</p>
                    </div>
                `;
            } else {
                data.quotes.forEach(quote => {
                    let statusBadge = '';
                    switch(quote.status) {
                        case 'pending':
                            statusBadge = '<span class="bg-yellow-600/20 text-yellow-400 px-3 py-1 rounded-full text-xs font-medium">Pending</span>';
                            break;
                        case 'accepted':
                            statusBadge = '<span class="bg-green-600/20 text-green-400 px-3 py-1 rounded-full text-xs font-medium">Accepted</span>';
                            break;
                        case 'rejected':
                            statusBadge = '<span class="bg-red-600/20 text-red-400 px-3 py-1 rounded-full text-xs font-medium">Rejected</span>';
                            break;
                    }
    
                    content += `
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h4 class="text-xl font-semibold text-white mb-1">${quote.center_name}</h4>
                                        <p class="text-sm text-green-100">${quote.center_address}</p>
                                    </div>
                                    ${statusBadge}
                                </div>
                                
                                <div class="grid grid-cols-2 gap-6 mb-6">
                                    <div class="bg-white/5 p-4 rounded-lg">
                                        <p class="text-sm text-green-200 mb-1">Service Price</p>
                                        <p class="text-2xl font-semibold text-white">₱${parseFloat(quote.price).toLocaleString()}</p>
                                    </div>
                                    <div class="bg-white/5 p-4 rounded-lg">
                                        <p class="text-sm text-green-200 mb-1">Reward Points</p>
                                        <p class="text-2xl font-semibold text-white">${quote.estimated_points.toLocaleString()}</p>
                                    </div>
                                </div>
                                
                                ${quote.notes ? `
                                    <div class="bg-white/5 p-4 rounded-lg mb-6">
                                        <p class="text-sm text-green-200 mb-1">Additional Notes</p>
                                        <p class="text-white">${quote.notes}</p>
                                    </div>
                                ` : ''}
                                
                                ${quote.status === 'pending' ? `
                                    <div class="grid grid-cols-2 gap-3">
                                        <button onclick="handleQuote(${quote.id}, 'accept')" 
                                                class="bg-[#436d2e] hover:bg-opacity-90 text-white py-2 rounded-lg transition-all">
                                            <i class="fas fa-check mr-2"></i>Accept Quote
                                        </button>
                                        <button onclick="handleQuote(${quote.id}, 'reject')"
                                                class="bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg transition-all">
                                            <i class="fas fa-times mr-2"></i>Reject Quote
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
            }
    
            content += '</div>';
            document.getElementById('quoteContent').innerHTML = content;
            document.getElementById('quoteModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading quotes');
        }
    }
    
    function closeQuoteModal() {
        document.getElementById('quoteModal').classList.add('hidden');
    }
    
    async function handleQuote(quoteId, action) {
        try {
            const formData = new FormData();
            formData.append('quote_id', quoteId);
            formData.append('action', action);
            formData.append('quote_action', true);
    
            const response = await fetch('quotes.php', {
                method: 'POST',
                body: formData
            });
    
            // Refresh the page to show updated status
            location.reload();
        } catch (error) {
            console.error('Error:', error);
            alert('Error processing quote');
        }
    }
    
    // Close modal when clicking outside
    document.getElementById('quoteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQuoteModal();
        }
    });
    </script>

</body>
</html>
<?php
$conn->close();
?>