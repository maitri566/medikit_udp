<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('User deleted!');</script>";
}

$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u ORDER BY u.created_at DESC");
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-users"></i> Registered Users (<?php echo $users->num_rows; ?>)</h2>
    </div>
    <div class="panel-body">
        <?php if ($users->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo $user['order_count']; ?></td>
                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>No registered users yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
