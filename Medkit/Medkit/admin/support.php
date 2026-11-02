<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'reply') {
        $id = (int)$_POST['id'];
        $reply = sanitize($_POST['admin_reply']);
        $status = sanitize($_POST['ticket_status']);
        $stmt = $conn->prepare("UPDATE support_tickets SET admin_reply = ?, ticket_status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $reply, $status, $id);
        $stmt->execute();
        $msg = "Ticket updated!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM support_tickets WHERE id = $id");
        $msg = "Ticket deleted!";
    }
}

$tickets = $conn->query("SELECT * FROM support_tickets ORDER BY created_at DESC");
if (isset($msg)) echo "<script>alert('$msg');</script>";
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-headset"></i> Support Tickets (<?php echo $tickets->num_rows; ?>)</h2>
    </div>
    <div class="panel-body">
        <?php if ($tickets->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($t = $tickets->fetch_assoc()): ?>
                <tr style="<?php echo $t['ticket_status'] === 'open' ? 'background:#fff9e6;' : ''; ?>">
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                    <td><?php echo htmlspecialchars($t['email']); ?></td>
                    <td style="max-width:150px;"><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($t['message']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $t['ticket_status'] === 'open' ? 'pending' : 'delivered'; ?>"><?php echo ucfirst($t['ticket_status']); ?></span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewTicket(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($t['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($t['subject'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($t['message'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($t['admin_reply'] ?? '', ENT_QUOTES); ?>', '<?php echo $t['ticket_status']; ?>')"><i class="fas fa-eye"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this ticket?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-headset"></i><p>No support tickets yet.</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Ticket Detail Modal -->
<div class="modal-overlay" id="ticketModal">
    <div class="modal">
        <div class="modal-header"><h3>Ticket Details</h3><button class="close-modal" onclick="closeTicketModal()">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id" id="ticketId">
                <div style="margin-bottom:15px;">
                    <p><strong>Name:</strong> <span id="tName"></span></p>
                    <p><strong>Email:</strong> <span id="tEmail"></span></p>
                    <p><strong>Subject:</strong> <span id="tSubject"></span></p>
                    <p style="margin-top:10px;"><strong>Message:</strong></p>
                    <p id="tMessage" style="background:#f8f9fa; padding:15px; border-radius:8px; margin-top:5px;"></p>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="ticket_status" id="tStatus">
                        <option value="open">Open</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Admin Reply</label>
                    <textarea name="admin_reply" id="tReply" placeholder="Type your reply to the customer" style="min-height:100px;"></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-danger" onclick="closeTicketModal()">Cancel</button><button type="submit" class="btn btn-primary">Save Reply</button></div>
        </form>
    </div>
</div>

<script>
function viewTicket(id, name, email, subject, message, reply, status) {
    document.getElementById('ticketId').value = id;
    document.getElementById('tName').textContent = name;
    document.getElementById('tEmail').textContent = email;
    document.getElementById('tSubject').textContent = subject;
    document.getElementById('tMessage').textContent = message;
    document.getElementById('tReply').value = reply;
    document.getElementById('tStatus').value = status;
    document.getElementById('ticketModal').classList.add('show');
}
function closeTicketModal() { document.getElementById('ticketModal').classList.remove('show'); }
document.getElementById('ticketModal').addEventListener('click', function(e) { if (e.target === this) closeTicketModal(); });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
