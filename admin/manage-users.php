<?php
session_start();
include '../backend/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $current_page = basename($_SERVER['PHP_SELF']);
    header("Location: ../frontend/signin.php?redirect_to=../../admin/" . $current_page);
    exit;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                break;
                
            case 'toggle_admin':
                $stmt = $pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                break;
                
            case 'reset_progress':
                $stmt = $pdo->prepare("DELETE FROM user_progress WHERE user_id = ?");
                $stmt->execute([$_POST['user_id']]);
                $stmt = $pdo->prepare("DELETE FROM leaderboard WHERE user_id = ?");
                $stmt->execute([$_POST['user_id']]);
                break;
        }
    }
}

// Get users with stats
$stmt = $pdo->query("
    SELECT 
        u.*,
        COALESCE(lb.xp, 0) as total_xp,
        COUNT(DISTINCT up.lesson_id) as lessons_completed,
        COUNT(DISTINCT ub.badge_id) as badges_earned
    FROM users u
    LEFT JOIN leaderboard lb ON u.id = lb.user_id
    LEFT JOIN user_progress up ON u.id = up.user_id AND up.completed = 1
    LEFT JOIN user_badges ub ON u.id = ub.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar (same as index.php) -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold">Admin Panel</h2>
            </div>
            <nav class="mt-8">
                <a href="index.php" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-dashboard mr-2"></i> Dashboard
                </a>
                <a href="manage-users.php" class="block py-2 px-4 bg-gray-700">
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
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold">Manage Users</h1>
                    <button onclick="openAddUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Add New User
                    </button>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">XP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lessons</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Badges</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $user['id'] ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="<?= $user['avatar_url'] ?? 'https://via.placeholder.com/40' ?>" 
                                             class="w-8 h-8 rounded-full mr-3">
                                        <?= htmlspecialchars($user['full_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-6 py-4"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td class="px-6 py-4"><?= number_format($user['total_xp']) ?></td>
                                <td class="px-6 py-4"><?= $user['lessons_completed'] ?></td>
                                <td class="px-6 py-4"><?= $user['badges_earned'] ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($user['is_admin']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Yes</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="editUser(<?= $user['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleAdmin(<?= $user['id'] ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                    <button onclick="resetProgress(<?= $user['id'] ?>)" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-redo-alt"></i>
                                    </button>
                                    <button onclick="deleteUser(<?= $user['id'] ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4">Add New User</h2>
            <form method="POST" action="manage-users.php">
                <input type="hidden" name="action" value="add">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="full_name" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_admin" class="mr-2">
                        <span>Make Admin</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function editUser(id) {
            window.location.href = `edit-user.php?id=${id}`;
        }

        function toggleAdmin(id) {
            if (confirm('Toggle admin status for this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_admin">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetProgress(id) {
            if (confirm('Reset all progress for this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="reset_progress">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>