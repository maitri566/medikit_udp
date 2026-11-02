<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Delete subscriber
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM newsletter_subscribers WHERE id = $id");
    echo "<script>alert('Subscriber removed!');</script>";
}

$subscribers = $conn->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-newspaper"></i> Newsletter Subscribers (<?php echo $subscribers->num_rows; ?>)</h2>
    </div>
    <div class="panel-body">
        <?php if ($subscribers->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Subscribed On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sub = $subscribers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $sub['id']; ?></td>
                    <td><?php echo htmlspecialchars($sub['email']); ?></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($sub['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this subscriber?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <p>No subscribers yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
