<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = sanitize($_POST['title']);
        $category = sanitize($_POST['category']);
        $price = (float)$_POST['price'];
        $old_price = (float)$_POST['old_price'];
        $rating = (float)$_POST['rating'];
        $image = sanitize($_POST['image']);
        $discount = sanitize($_POST['discount']);
        $description = sanitize($_POST['description']);
        $stock = (int)$_POST['stock'];
        
        $stmt = $conn->prepare("INSERT INTO products (title, category, price, old_price, rating, image, discount, description, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdddsssi", $title, $category, $price, $old_price, $rating, $image, $discount, $description, $stock);
        $stmt->execute();
        $msg = "Product added successfully!";
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $title = sanitize($_POST['title']);
        $category = sanitize($_POST['category']);
        $price = (float)$_POST['price'];
        $old_price = (float)$_POST['old_price'];
        $rating = (float)$_POST['rating'];
        $image = sanitize($_POST['image']);
        $discount = sanitize($_POST['discount']);
        $description = sanitize($_POST['description']);
        $stock = (int)$_POST['stock'];
        
        $stmt = $conn->prepare("UPDATE products SET title=?, category=?, price=?, old_price=?, rating=?, image=?, discount=?, description=?, stock=? WHERE id=?");
        $stmt->bind_param("ssdddsssii", $title, $category, $price, $old_price, $rating, $image, $discount, $description, $stock, $id);
        $stmt->execute();
        $msg = "Product updated successfully!";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Product deleted successfully!";
    }
}

// Get products
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($categoryFilter) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}
$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$catList = [];
while ($c = $categories->fetch_assoc()) {
    $catList[] = $c['name'];
}

if (isset($msg)) echo "<script>alert('$msg');</script>";
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-box"></i> Manage Products (<?php echo $products->num_rows; ?>)</h2>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" id="productSearch" class="search-box" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <select id="categoryFilter" class="search-box">
                <option value="">All Categories</option>
                <?php foreach ($catList as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" onclick="openModal('add')"><i class="fas fa-plus"></i> Add Product</button>
        </div>
    </div>
    <div class="panel-body">
        <?php if ($products->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Old Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><img src="../<?php echo htmlspecialchars($p['image']); ?>" class="table-img" alt=""></td>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><?php echo htmlspecialchars($p['category']); ?></td>
                    <td>₹<?php echo number_format($p['price'], 2); ?></td>
                    <td>₹<?php echo number_format($p['old_price'], 2); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openModal('edit', <?php echo $p['id']; ?>)"><i class="fas fa-edit"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box"></i>
            <p>No products found</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="productModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Add Product</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="" id="productForm">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Product Title</label>
                    <input type="text" name="title" id="f_title" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="f_category" required>
                            <?php foreach ($catList as $cat): ?>
                                <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" id="f_stock" value="100" required min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" name="price" id="f_price" step="0.01" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Old Price (₹)</label>
                        <input type="number" name="old_price" id="f_old_price" step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Rating (0-5)</label>
                        <input type="number" name="rating" id="f_rating" step="0.1" value="4.5" min="0" max="5">
                    </div>
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="text" name="discount" id="f_discount" placeholder="20% OFF">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Image Path (e.g. images/product.jpg)</label>
                    <input type="text" name="image" id="f_image" placeholder="images/product.jpg" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="f_description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search and filter
document.getElementById('productSearch').addEventListener('input', function() {
    const params = new URLSearchParams(window.location.search);
    params.set('search', this.value);
    window.location.search = params.toString();
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    const params = new URLSearchParams(window.location.search);
    if (this.value) params.set('category', this.value);
    else params.delete('category');
    window.location.search = params.toString();
});

// Modal functions
function openModal(mode, id) {
    const modal = document.getElementById('productModal');
    document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Edit Product' : 'Add Product';
    document.getElementById('formAction').value = mode;
    
    if (mode === 'edit' && id) {
        fetch('../api/get_products.php?limit=0')
            .then(r => r.json())
            .then(data => {
                const product = data.data.find(p => p.id === id);
                if (product) {
                    document.getElementById('formId').value = product.id;
                    document.getElementById('f_title').value = product.title;
                    document.getElementById('f_category').value = product.category;
                    document.getElementById('f_price').value = product.price;
                    document.getElementById('f_old_price').value = product.oldPrice;
                    document.getElementById('f_rating').value = product.rating;
                    document.getElementById('f_image').value = product.image;
                    document.getElementById('f_discount').value = product.discount;
                    document.getElementById('f_description').value = product.description;
                    document.getElementById('f_stock').value = product.stock;
                }
            });
    }
    
    modal.classList.add('show');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('show');
    document.getElementById('productForm').reset();
    document.getElementById('formId').value = '';
}

// Close on overlay click
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
