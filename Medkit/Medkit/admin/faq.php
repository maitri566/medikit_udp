<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $question = sanitize($_POST['question']);
        $answer = sanitize($_POST['answer']);
        $category = sanitize($_POST['category']);
        $stmt = $conn->prepare("INSERT INTO faq (question, answer, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $question, $answer, $category);
        $stmt->execute();
        $msg = "FAQ added!";
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $question = sanitize($_POST['question']);
        $answer = sanitize($_POST['answer']);
        $category = sanitize($_POST['category']);
        $stmt = $conn->prepare("UPDATE faq SET question=?, answer=?, category=? WHERE id=?");
        $stmt->bind_param("sssi", $question, $answer, $category, $id);
        $stmt->execute();
        $msg = "FAQ updated!";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM faq WHERE id = $id");
        $msg = "FAQ deleted!";
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE faq SET is_active = 1 - is_active WHERE id = $id");
    }
}

$faqs = $conn->query("SELECT * FROM faq ORDER BY category ASC, display_order ASC");
if (isset($msg)) echo "<script>alert('$msg');</script>";
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-question-circle"></i> Manage FAQs (<?php echo $faqs->num_rows; ?>)</h2>
        <button class="btn btn-primary" onclick="openFaqModal('add')"><i class="fas fa-plus"></i> Add FAQ</button>
    </div>
    <div class="panel-body">
        <?php if ($faqs->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>ID</th><th>Question</th><th>Category</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($f = $faqs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $f['id']; ?></td>
                    <td style="max-width:400px;"><?php echo htmlspecialchars($f['question']); ?></td>
                    <td><span class="status-badge status-delivered"><?php echo ucfirst($f['category']); ?></span></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $f['is_active'] ? 'btn-primary' : 'btn-danger'; ?>">
                                <?php echo $f['is_active'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openFaqModal('edit', <?php echo $f['id']; ?>, '<?php echo htmlspecialchars($f['question'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($f['answer'], ENT_QUOTES); ?>', '<?php echo $f['category']; ?>')"><i class="fas fa-edit"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this FAQ?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-question-circle"></i><p>No FAQs yet. Click "Add FAQ" to create one.</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- FAQ Modal -->
<div class="modal-overlay" id="faqModal">
    <div class="modal">
        <div class="modal-header"><h3 id="faqModalTitle">Add FAQ</h3><button class="close-modal" onclick="closeFaqModal()">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" id="faqAction" value="add">
                <input type="hidden" name="id" id="faqId" value="">
                <div class="form-group"><label>Question</label><input type="text" name="question" id="faqQuestion" required></div>
                <div class="form-group"><label>Answer</label><textarea name="answer" id="faqAnswer" required style="min-height:100px;"></textarea></div>
                <div class="form-group"><label>Category</label>
                    <select name="category" id="faqCategory">
                        <option value="general">General</option>
                        <option value="ordering">Ordering</option>
                        <option value="payment">Payment</option>
                        <option value="delivery">Delivery</option>
                        <option value="returns">Returns</option>
                        <option value="products">Products</option>
                        <option value="support">Support</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-danger" onclick="closeFaqModal()">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<script>
function openFaqModal(mode, id, q, a, cat) {
    document.getElementById('faqModalTitle').textContent = mode === 'edit' ? 'Edit FAQ' : 'Add FAQ';
    document.getElementById('faqAction').value = mode;
    if (mode === 'edit') {
        document.getElementById('faqId').value = id;
        document.getElementById('faqQuestion').value = q;
        document.getElementById('faqAnswer').value = a;
        document.getElementById('faqCategory').value = cat;
    }
    document.getElementById('faqModal').classList.add('show');
}
function closeFaqModal() { document.getElementById('faqModal').classList.remove('show'); }
document.getElementById('faqModal').addEventListener('click', function(e) { if (e.target === this) closeFaqModal(); });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
