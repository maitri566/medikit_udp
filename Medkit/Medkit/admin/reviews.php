<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Approve / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'approve') {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE product_reviews SET is_approved = 1 WHERE id = $id");
        $msg = "Review approved!";
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM product_reviews WHERE id = $id");
        $msg = "Review deleted!";
    }
}

$reviews = $conn->query("SELECT r.*, p.title as product_title, p.image as product_image FROM product_reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.is_approved ASC, r.created_at DESC");
if (isset($msg)) echo "<script>alert('$msg');</script>";
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-star"></i> Product Reviews (<?php echo $reviews->num_rows; ?>)</h2>
    </div>
    <div class="panel-body">
        <?php if ($reviews->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $reviews->fetch_assoc()): ?>
                <tr style="<?php echo $r['is_approved'] ? '' : 'background:#fff9e6;'; ?>">
                    <td>
                        <?php if ($r['product_image']): ?>
                            <img src="../<?php echo htmlspecialchars($r['product_image']); ?>" class="table-img" alt="">
                        <?php endif; ?>
                        <div style="font-size:0.8rem; margin-top:5px;"><?php echo htmlspecialchars($r['product_title'] ?? 'Unknown'); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" style="color:<?php echo $i <= $r['rating'] ? '#f4a261' : '#ddd'; ?>; font-size:0.8rem;"></i>
                        <?php endfor; ?>
                    </td>
                    <td style="max-width:300px;"><?php echo htmlspecialchars($r['comment']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $r['is_approved'] ? 'delivered' : 'pending'; ?>"><?php echo $r['is_approved'] ? 'Approved' : 'Pending'; ?></span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                    <td>
                        <?php if (!$r['is_approved']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this review?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-star"></i><p>No reviews yet.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
