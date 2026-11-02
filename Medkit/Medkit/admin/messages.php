<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Mark as read
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $id");
    redirect('messages.php');
}

// Delete message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    echo "<script>alert('Message deleted!');</script>";
}

$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-envelope"></i> Contact Messages (<?php echo $messages->num_rows; ?>)</h2>
    </div>
    <div class="panel-body">
        <?php if ($messages->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                <tr style="<?php echo $msg['is_read'] ? '' : 'background:#fff9e6;'; ?>">
                    <td><?php echo $msg['id']; ?></td>
                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><?php echo htmlspecialchars($msg['subject'] ?: '-'); ?></td>
                    <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($msg['message']); ?></td>
                    <td>
                        <?php if ($msg['is_read']): ?>
                            <span class="status-badge status-delivered">Read</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">Unread</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></td>
                    <td>
                        <?php if (!$msg['is_read']): ?>
                            <a href="?read=<?php echo $msg['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-envelope"></i>
            <p>No messages found</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
