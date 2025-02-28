<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is a business
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'business') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get business name
$user_query = "SELECT business_name FROM tbl_user WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$business_name = $user_data['business_name'] ?? "Business User"; // Added fallback if business_name is null
$stmt->close();


// Get recent bulk requests
$requests_query = "SELECT * FROM tbl_bulk_requests WHERE business_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($requests_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests_result = $stmt->get_result();
$recent_requests = [];
while ($row = $requests_result->fetch_assoc()) {
    $recent_requests[] = $row;
}
$stmt->close();

// Get quote statistics
$quotes_query = "SELECT 
                    COUNT(q.id) AS total_quotes,
                    SUM(CASE WHEN q.status = 'accepted' THEN 1 ELSE 0 END) AS accepted_quotes,
                    SUM(CASE WHEN q.status = 'pending' THEN 1 ELSE 0 END) AS pending_quotes
                FROM tbl_quotes q
                JOIN tbl_bulk_requests b ON q.request_id = b.id
                WHERE b.business_id = ?";
$stmt = $conn->prepare($quotes_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quotes_result = $stmt->get_result();
$quote_stats = $quotes_result->fetch_assoc();
$stmt->close();

// Get recycling statistics
$recycling_query = "SELECT 
                      COUNT(*) AS total_requests,
                      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_requests,
                      SUM(estimated_quantity) AS total_quantity
                    FROM tbl_bulk_requests 
                    WHERE business_id = ?";
$stmt = $conn->prepare($recycling_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recycling_result = $stmt->get_result();
$recycling_stats = $recycling_result->fetch_assoc();
// Convert NULL to 0 for calculations
$recycling_stats['total_quantity'] = $recycling_stats['total_quantity'] ?? 0;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Business Dashboard - EcoLens</title>
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
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="profile.php" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                        <a href="../index.php" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
                <!-- Mobile menu (hidden by default) -->
                <div id="mobile-menu" class="hidden pt-4 pb-2">
                    <a href="dashboard.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a href="profile.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="../index.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main content with proper padding for fixed navbar -->
        <main class="bg-overlay pt-24 pb-12 px-4">
            <div class="relative z-10">
                <div class="container mx-auto px-4 md:px-6">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white mb-2">Business Dashboard</h1>
                        <p class="text-green-100">Welcome back, <?php echo htmlspecialchars($business_name); ?></p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        <a href="bulk-request.php" class="bg-white/10 backdrop-blur-sm p-6 rounded-xl flex items-center space-x-4 hover:bg-white/15 transition">
                            <div class="bg-green-700 p-3 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">New Bulk Request</h3>
                                <p class="text-green-100">Schedule pickup or drop-off</p>
                            </div>
                        </a>
                        
                        <a href="quotes.php" class="bg-white/10 backdrop-blur-sm p-6 rounded-xl flex items-center space-x-4 hover:bg-white/15 transition">
                            <div class="bg-green-700 p-3 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">View Quotes</h3>
                                <p class="text-green-100">Manage received quotes</p>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="bg-white/10 backdrop-blur-sm p-6 rounded-xl flex items-center space-x-4 hover:bg-white/15 transition">
                            <div class="bg-green-700 p-3 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">Reports</h3>
                                <p class="text-green-100">View sustainability metrics</p>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Statistics Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-white mb-2">Recycling Overview</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-green-100">Total Requests</span>
                                    <span class="text-white font-semibold"><?php echo $recycling_stats['total_requests'] ?? 0; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Completed</span>
                                    <span class="text-white font-semibold"><?php echo $recycling_stats['completed_requests'] ?? 0; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Total Quantity</span>
                                    <span class="text-white font-semibold"><?php echo number_format($recycling_stats['total_quantity']); ?> kg</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-white mb-2">Quote Statistics</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-green-100">Total Quotes</span>
                                    <span class="text-white font-semibold"><?php echo $quote_stats['total_quotes'] ?? 0; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Pending Review</span>
                                    <span class="text-white font-semibold"><?php echo $quote_stats['pending_quotes'] ?? 0; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Accepted</span>
                                    <span class="text-white font-semibold"><?php echo $quote_stats['accepted_quotes'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-white mb-2">Environmental Impact</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-green-100">CO₂ Saved</span>
                                    <span class="text-white font-semibold">~<?php echo number_format($recycling_stats['total_quantity'] * 2.5); ?> kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Trees Saved</span>
                                    <span class="text-white font-semibold">~<?php echo number_format($recycling_stats['total_quantity'] / 100); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-100">Water Saved</span>
                                    <span class="text-white font-semibold">~<?php echo number_format($recycling_stats['total_quantity'] * 20); ?> L</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Requests -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-semibold text-white mb-4">Recent Bulk Requests</h2>
                        
                        <?php if (empty($recent_requests)): ?>
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl text-center">
                            <p class="text-green-100">You haven't submitted any bulk recycling requests yet.</p>
                            <a href="bulk-request.php" class="inline-block mt-3 bg-[#436d2e] text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition-all">
                                Create Your First Request
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
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
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-green-800/30">
                                        <?php foreach ($recent_requests as $request): ?>
                                        <?php 
                                        // Get quote count for this request
                                        $quote_count_query = "SELECT COUNT(*) as count FROM tbl_quotes WHERE request_id = ?";
                                        $stmt = $conn->prepare($quote_count_query);
                                        $stmt->bind_param("i", $request['id']);
                                        $stmt->execute();
                                        $quote_count_result = $stmt->get_result()->fetch_assoc();
                                        $quote_count = $quote_count_result['count'];
                                        $stmt->close();
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <?php echo date('M d, Y', strtotime($request['preferred_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <?php echo ucfirst($request['request_type']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <?php echo ucwords(str_replace(',', ', ', $request['material_types'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <?php echo number_format($request['estimated_quantity']); ?> kg
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-900">
                                                    Pending
                                                </span>
                                                <?php elseif ($request['status'] === 'approved'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-900">
                                                    Approved
                                                </span>
                                                <?php elseif ($request['status'] === 'completed'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-200 text-blue-900">
                                                    Completed
                                                </span>
                                                <?php elseif ($request['status'] === 'rejected'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-900">
                                                    Rejected
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <?php if ($quote_count > 0): ?>
                                                <button onclick="viewQuotes(<?php echo $request['id']; ?>)" class="text-green-300 hover:text-white">
                                                    View <?php echo $quote_count; ?> quote<?php echo $quote_count > 1 ? 's' : ''; ?>
                                                </button>
                                                <?php else: ?>
                                                <span class="text-green-100/50">No quotes yet</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="all-requests.php" class="text-green-200 hover:text-white">
                                View all requests →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-[#1b1b1b] py-6">
            <div class="container mx-auto px-4">
                <p class="text-center text-white/60">&copy; <?php echo date('Y'); ?> EcoLens. All rights reserved.</p>
            </div>
        </footer>
        
        <script>
            // Toggle mobile menu
            document.getElementById('mobile-menu-button').addEventListener('click', function() {
                const mobileMenu = document.getElementById('mobile-menu');
                mobileMenu.classList.toggle('hidden');
            });
        </script>
                
        <!-- Quote Modal -->
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
                    <div class="mb-6 bg-white/5 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-white mb-2">Request Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-green-100">Type: <span class="text-white">${data.request.request_type}</span></p>
                                <p class="text-green-100">Materials: <span class="text-white">${data.request.material_types.replace(',', ', ')}</span></p>
                            </div>
                            <div>
                                <p class="text-green-100">Quantity: <span class="text-white">${data.request.estimated_quantity} kg</span></p>
                                <p class="text-green-100">Date: <span class="text-white">${new Date(data.request.preferred_date).toLocaleDateString()}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                `;
        
                data.quotes.forEach(quote => {
                    let statusBadge = '';
                    switch(quote.status) {
                        case 'pending':
                            statusBadge = '<span class="bg-yellow-600/20 text-yellow-400 px-2 py-1 rounded-full text-xs">Pending</span>';
                            break;
                        case 'accepted':
                            statusBadge = '<span class="bg-green-600/20 text-green-400 px-2 py-1 rounded-full text-xs">Accepted</span>';
                            break;
                        case 'rejected':
                            statusBadge = '<span class="bg-red-600/20 text-red-400 px-2 py-1 rounded-full text-xs">Rejected</span>';
                            break;
                    }
        
                    content += `
                        <div class="bg-white/5 rounded-lg p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-white">${quote.center_name}</h4>
                                    <p class="text-sm text-green-100">${quote.center_address}</p>
                                </div>
                                ${statusBadge}
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-green-200">Price</p>
                                    <p class="text-xl font-semibold text-white">₱${parseFloat(quote.price).toLocaleString()}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-green-200">Points</p>
                                    <p class="text-xl font-semibold text-white">${quote.estimated_points}</p>
                                </div>
                            </div>
                            ${quote.notes ? `
                                <div class="bg-white/10 p-3 rounded-lg mb-4">
                                    <p class="text-sm text-green-100">${quote.notes}</p>
                                </div>
                            ` : ''}
                            ${quote.status === 'pending' ? `
                                <div class="grid grid-cols-2 gap-3">
                                    <button onclick="handleQuote(${quote.id}, 'accept')" 
                                            class="bg-green-700 hover:bg-green-800 text-white py-2 rounded-lg transition">
                                        Accept Quote
                                    </button>
                                    <button onclick="handleQuote(${quote.id}, 'reject')"
                                            class="bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg transition">
                                        Reject Quote
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
        
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