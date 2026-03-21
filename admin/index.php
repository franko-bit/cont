<?php
session_start();
include '../backend/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../frontend/login.php");
    exit;
}

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// New users today
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
$stats['new_users_today'] = $stmt->fetch()['total'];

// Total lessons
$stmt = $pdo->query("SELECT COUNT(*) as total FROM lessons");
$stats['total_lessons'] = $stmt->fetch()['total'];

// Total exercises
$stmt = $pdo->query("SELECT COUNT(*) as total FROM exercises");
$stats['total_exercises'] = $stmt->fetch()['total'];

// Active users (last 7 days)
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT user_id) as total 
    FROM user_progress 
    WHERE last_attempt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stats['active_users'] = $stmt->fetch()['total'];

// Total XP earned
$stmt = $pdo->query("SELECT SUM(xp) as total FROM leaderboard");
$stats['total_xp'] = $stmt->fetch()['total'] ?? 0;

// Recent activities
$stmt = $pdo->query("
    SELECT ar.*, u.full_name, e.type, l.name as lesson_name
    FROM activity_responses ar
    JOIN users u ON ar.user_id = u.id
    JOIN exercises e ON ar.exercise_id = e.id
    JOIN lessons l ON e.lesson_id = l.id
    ORDER BY ar.attempted_at DESC
    LIMIT 10
");
$recent_activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Language Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold">Admin Panel</h2>
            </div>
            <nav class="mt-8">
                <a href="index.php" class="block py-2 px-4 bg-gray-700">
                    <i class="fas fa-dashboard mr-2"></i> Dashboard
                </a>
                <a href="manage-users.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
                <a href="manage-levels.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-layer-group mr-2"></i> Levels
                </a>
                <a href="manage-categories.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-folder mr-2"></i> Categories
                </a>
                <a href="manage-lessons.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-book mr-2"></i> Lessons
                </a>
                <a href="manage-badges.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-medal mr-2"></i> Badges
                </a>
                <a href="analytics.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-chart-line mr-2"></i> Analytics
                </a>
                <hr class="my-4 border-gray-600">
                <a href="../frontend/dashboard.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Site
                </a>
                <a href="../backend/logout.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">Total Users</p>
                                <p class="text-2xl font-bold"><?= $stats['total_users'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-user-plus text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">New Today</p>
                                <p class="text-2xl font-bold"><?= $stats['new_users_today'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-book text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">Total Lessons</p>
                                <p class="text-2xl font-bold"><?= $stats['total_lessons'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">Exercises</p>
                                <p class="text-2xl font-bold"><?= $stats['total_exercises'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full">
                                <i class="fas fa-fire text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">Active Users (7d)</p>
                                <p class="text-2xl font-bold"><?= $stats['active_users'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-full">
                                <i class="fas fa-star text-indigo-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500">Total XP</p>
                                <p class="text-2xl font-bold"><?= number_format($stats['total_xp']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold mb-4">User Growth (Last 7 Days)</h2>
                        <canvas id="userGrowthChart" height="200"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold mb-4">Activity Distribution</h2>
                        <canvas id="activityChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold mb-4">Recent Activities</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">User</th>
                                <th class="text-left py-2">Lesson</th>
                                <th class="text-left py-2">Exercise Type</th>
                                <th class="text-left py-2">Result</th>
                                <th class="text-left py-2">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activities as $activity): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2"><?= htmlspecialchars($activity['full_name']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($activity['lesson_name']) ?></td>
                                <td class="py-2"><?= $activity['type'] ?></td>
                                <td class="py-2">
                                    <?php if ($activity['is_correct']): ?>
                                        <span class="text-green-600">✓ Correct</span>
                                    <?php else: ?>
                                        <span class="text-red-600">✗ Incorrect</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2"><?= date('M d, H:i', strtotime($activity['attempted_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(getLast7Days()) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode(getUserGrowthData($pdo)) ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            }
        });

        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Translation', 'Multiple Choice', 'Typing', 'Listening', 'Matching', 'Sentence Building'],
                datasets: [{
                    data: <?= json_encode(getActivityDistribution($pdo)) ?>,
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'
                    ]
                }]
            }
        });
    </script>
</body>
</html>

<?php
function getLast7Days() {
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('M d', strtotime("-$i days"));
    }
    return $dates;
}

function getUserGrowthData($pdo) {
    $data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        $data[] = $stmt->fetch()['count'];
    }
    return $data;
}

function getActivityDistribution($pdo) {
    $types = ['translation', 'multiple_choice', 'typing', 'listening', 'matching', 'sentence_building'];
    $data = [];
    foreach ($types as $type) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exercises WHERE type = ?");
        $stmt->execute([$type]);
        $data[] = $stmt->fetch()['count'];
    }
    return $data;
}
?>