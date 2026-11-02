// Products will be loaded from PHP API
let products = [];
let cart = [];

// DOM elements
const hamburger = document.querySelector('.hamburger');
const nav = document.querySelector('nav');
const cartIcon = document.getElementById('cart-icon');
const closeCart = document.querySelector('.close-cart');
const cartSidebar = document.querySelector('.cart-sidebar');
const overlay = document.querySelector('.overlay');
const productGrid = document.getElementById('product-grid');
const cartItems = document.getElementById('cart-items');
const cartCount = document.getElementById('cart-count');
const cartTotalPrice = document.getElementById('cart-total-price');
const continueShoppingBtn = document.querySelector('.continue-shopping');
const filterButtons = document.querySelectorAll('.filter-btn');
const testimonialDots = document.querySelectorAll('.testimonial-dots .dot');
const testimonialSlides = document.querySelectorAll('.testimonial-slide');
const newsletterForm = document.getElementById('newsletter-form');
const contactForm = document.getElementById('contact-form');

// Fetch products from PHP API
async function fetchProducts(category = 'all', limit = 0) {
    try {
        let url = 'api/get_products.php';
        const params = new URLSearchParams();
        if (category !== 'all') params.append('category', category);
        if (limit > 0) params.append('limit', limit);
        const queryString = params.toString();
        if (queryString) url += '?' + queryString;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        }
    } catch (e) {
        console.error('Error fetching products:', e);
    }
    return [];
}

// Initialize: load all products to cache
async function initProducts() {
    products = await fetchProducts('all', 0);
}

// Mobile menu toggle
if (hamburger) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        nav.classList.toggle('active');
    });
}

// Cart sidebar toggle
if (cartIcon && closeCart && cartSidebar && overlay) {
    cartIcon.addEventListener('click', (e) => {
        e.preventDefault();
        cartSidebar.classList.add('active');
        overlay.classList.add('active');
    });

    closeCart.addEventListener('click', () => {
        cartSidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    overlay.addEventListener('click', () => {
        cartSidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
}

// Load products with limit
async function loadProducts(category = 'all', limit = 8) {
    if (!productGrid) return;

    productGrid.innerHTML = '<p style="text-align:center; padding:40px; color:#777;">Loading products...</p>';

    const filteredProducts = await fetchProducts(category, 0);

    const productsToShow = limit ? filteredProducts.slice(0, limit) : filteredProducts;

    productGrid.innerHTML = '';

    if (productsToShow.length === 0) {
        productGrid.innerHTML = '<p style="text-align:center; padding:40px; color:#777;">No products found.</p>';
    } else {
        productsToShow.forEach(product => {
            const productCard = createProductCard(product);
            productGrid.appendChild(productCard);
        });
    }

    // Add event listeners to add-to-cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
            const productId = parseInt(this.getAttribute('data-id'));
            addToCart(productId);
        });
    });

    // Show/hide view all/less buttons
    const viewAllButton = document.getElementById('view-all-btn');
    const viewLessButton = document.getElementById('view-less-btn');

    if (viewAllButton && viewLessButton) {
        if (limit && filteredProducts.length > limit) {
            viewAllButton.style.display = 'block';
            viewLessButton.style.display = 'none';
        } else {
            viewAllButton.style.display = 'none';
            viewLessButton.style.display = 'block';
        }
    }
}

// View all / view less buttons
document.addEventListener('DOMContentLoaded', () => {
    const viewAllButton = document.getElementById('view-all-btn');
    const viewLessButton = document.getElementById('view-less-btn');

    if (viewAllButton) {
        viewAllButton.addEventListener('click', () => {
            const activeFilter = document.querySelector('.filter-btn.active');
            const cat = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
            loadProducts(cat, null);
        });
    }
    if (viewLessButton) {
        viewLessButton.addEventListener('click', () => {
            const activeFilter = document.querySelector('.filter-btn.active');
            const cat = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
            loadProducts(cat, 8);
        });
    }

    loadProducts();
});

// Create product card
function createProductCard(product) {
    const productCard = document.createElement('div');
    productCard.className = 'product-card';
    
    const fullStars = Math.floor(product.rating);
    const halfStar = product.rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    let starsHTML = '';
    for (let i = 0; i < fullStars; i++) starsHTML += '<i class="fas fa-star"></i>';
    if (halfStar) starsHTML += '<i class="fas fa-star-half-alt"></i>';
    for (let i = 0; i < emptyStars; i++) starsHTML += '<i class="far fa-star"></i>';
    
    productCard.innerHTML = `
        <div class="product-img">
            <img src="${product.image}" alt="${product.title}" loading="lazy">
            ${product.discount ? `<span class="discount-tag">${product.discount}</span>` : ''}
            <div class="product-icons">
                <span class="product-icon"><i class="far fa-heart"></i></span>
            </div>
        </div>
        <div class="product-details">
            <p class="product-category">${product.category.charAt(0).toUpperCase() + product.category.slice(1)}</p>
            <h3 class="product-title">${product.title}</h3>
            <div class="product-rating">${starsHTML}</div>
            <div class="product-price">
                <span class="current-price">₹${product.price.toFixed(2)}</span>
                ${product.oldPrice ? `<span class="old-price">₹${product.oldPrice.toFixed(2)}</span>` : ''}
            </div>
            <p class="product-description">${product.description}</p>
            <button class="add-to-cart" data-id="${product.id}">Add to Cart</button>
        </div>
    `;
    return productCard;
}

// Filter products
if (filterButtons.length > 0) {
    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-filter');
            loadProducts(category);
        });
    });
}

// Add to cart function
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) {
        // If not in cache, fetch all and try again
        fetchProducts('all', 0).then(allProducts => {
            products = allProducts;
            const p = products.find(p => p.id === productId);
            if (p) {
                addToCartItem(p);
            }
        });
        return;
    }
    addToCartItem(product);
}

function addToCartItem(product) {
    const existingProduct = cart.find(item => item.id === product.id);
    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    updateCart();
    if (cartSidebar && overlay) {
        cartSidebar.classList.add('active');
        overlay.classList.add('active');
    }
}

// Update cart UI
function updateCart() {
    if (!cartItems || !cartCount || !cartTotalPrice) return;
    
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    cartItems.innerHTML = '';
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart" style="text-align:center; padding:30px; color:#999;">Your cart is empty</p>';
    } else {
        cart.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-img">
                    <img src="${item.image}" alt="${item.title}">
                </div>
                <div class="cart-item-details">
                    <h4 class="cart-item-title">${item.title}</h4>
                    <p class="cart-item-price">₹${item.price.toFixed(2)}</p>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn decrease" data-id="${item.id}">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-id="${item.id}">
                        <button class="quantity-btn increase" data-id="${item.id}">+</button>
                    </div>
                </div>
                <span class="cart-item-remove" data-id="${item.id}"><i class="fas fa-trash"></i></span>
            `;
            cartItems.appendChild(cartItem);
        });
        
        document.querySelectorAll('.cart-item-remove').forEach(button => {
            button.addEventListener('click', function () {
                const productId = parseInt(this.getAttribute('data-id'));
                removeFromCart(productId);
            });
        });
        
        document.querySelectorAll('.quantity-btn.decrease').forEach(button => {
            button.addEventListener('click', function () {
                const productId = parseInt(this.getAttribute('data-id'));
                decreaseQuantity(productId);
            });
        });
        
        document.querySelectorAll('.quantity-btn.increase').forEach(button => {
            button.addEventListener('click', function () {
                const productId = parseInt(this.getAttribute('data-id'));
                increaseQuantity(productId);
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function () {
                const productId = parseInt(this.getAttribute('data-id'));
                const quantity = parseInt(this.value);
                updateQuantity(productId, quantity);
            });
        });
    }
    
    const totalPrice = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    cartTotalPrice.textContent = `₹${totalPrice.toFixed(2)}`;
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

function increaseQuantity(productId) {
    const item = cart.find(item => item.id === productId);
    if (item) { item.quantity += 1; updateCart(); }
}

function decreaseQuantity(productId) {
    const item = cart.find(item => item.id === productId);
    if (item && item.quantity > 1) {
        item.quantity -= 1; updateCart();
    } else if (item && item.quantity === 1) {
        removeFromCart(productId);
    }
}

function updateQuantity(productId, quantity) {
    if (quantity < 1) return;
    const item = cart.find(item => item.id === productId);
    if (item) { item.quantity = quantity; updateCart(); }
}

// Testimonial slider
function showTestimonial(index) {
    if (testimonialSlides.length === 0 || testimonialDots.length === 0) return;
    testimonialSlides.forEach((slide, i) => {
        slide.style.display = i === index ? 'block' : 'none';
    });
    testimonialDots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
    });
}

if (testimonialDots.length > 0 && testimonialSlides.length > 0) {
    showTestimonial(0);
    testimonialDots.forEach((dot, index) => {
        dot.addEventListener('click', () => showTestimonial(index));
    });
    let currentTestimonial = 0;
    setInterval(() => {
        currentTestimonial = (currentTestimonial + 1) % testimonialSlides.length;
        showTestimonial(currentTestimonial);
    }, 5000);
}

// Newsletter form - POST to PHP API
if (newsletterForm) {
    newsletterForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        try {
            const formData = new FormData();
            formData.append('email', email);
            const response = await fetch('api/newsletter.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            alert(data.message);
            if (data.success) this.reset();
        } catch (err) {
            alert('Error subscribing. Please try again.');
        }
    });
}

// Contact form - POST to PHP API
document.addEventListener('DOMContentLoaded', () => {
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = contactForm.querySelector('input[placeholder="Your Name"]').value.trim();
            const email = contactForm.querySelector('input[placeholder="Your Email"]').value.trim();
            const subject = contactForm.querySelector('input[placeholder="Subject"]')?.value?.trim() || '';
            const message = contactForm.querySelector('textarea[placeholder="Your Message"]').value.trim();
            
            if (!name) { alert('Name is required.'); return; }
            if (!email) { alert('Email is required.'); return; }
            if (!message) { alert('Message is required.'); return; }
            
            try {
                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('subject', subject);
                formData.append('message', message);
                const response = await fetch('api/contact.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                alert(data.message);
                if (data.success) contactForm.reset();
            } catch (err) {
                alert('Error sending message. Please try again.');
            }
        });
    }
});

// Checkout button
document.addEventListener('DOMContentLoaded', () => {
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            // Save cart to sessionStorage for checkout page
            sessionStorage.setItem('checkout_cart', JSON.stringify(cart));
            window.location.href = 'checkout.php';
        });
    }
});

// Category page loaders
async function loadCategoryProducts(categoryName) {
    const productGrid = document.getElementById('product-grid');
    if (!productGrid) return;

    productGrid.innerHTML = '<p style="text-align:center; padding:40px; color:#777;">Loading products...</p>';
    
    const catProducts = await fetchProducts(categoryName, 0);
    
    productGrid.innerHTML = '';
    
    if (catProducts.length === 0) {
        productGrid.innerHTML = '<p style="text-align:center; padding:40px; color:#777;">No products found in this category.</p>';
        return;
    }
    
    // Update products cache
    products = [...new Map([...products, ...catProducts].map(p => [p.id, p])).values()];
    
    catProducts.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        
        const fullStars = Math.floor(product.rating);
        const halfStar = product.rating % 1 !== 0;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
        let starsHTML = '';
        for (let i = 0; i < fullStars; i++) starsHTML += '<i class="fas fa-star"></i>';
        if (halfStar) starsHTML += '<i class="fas fa-star-half-alt"></i>';
        for (let i = 0; i < emptyStars; i++) starsHTML += '<i class="far fa-star"></i>';
        
        productCard.innerHTML = `
            <div class="product-img">
                <img src="${product.image}" alt="${product.title}" loading="lazy">
                ${product.discount ? `<span class="discount-tag">${product.discount}</span>` : ''}
            </div>
            <div class="product-details">
                <p class="product-category">${product.category.charAt(0).toUpperCase() + product.category.slice(1)}</p>
                <h3 class="product-title">${product.title}</h3>
                <div class="product-rating">${starsHTML}</div>
                <div class="product-price">
                    <span class="current-price">₹${product.price.toFixed(2)}</span>
                    ${product.oldPrice ? `<span class="old-price">₹${product.oldPrice.toFixed(2)}</span>` : ''}
                </div>
                <p class="product-description">${product.description}</p>
                <button class="add-to-cart" data-id="${product.id}">Add to Cart</button>
            </div>
        `;
        productGrid.appendChild(productCard);
    });

    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
            const productId = parseInt(this.getAttribute('data-id'));
            addToCart(productId);
        });
    });
}

// Page-specific loaders
document.addEventListener('DOMContentLoaded', async () => {
    await initProducts();
    
    const path = window.location.pathname.toLowerCase();
    
    if (path.includes('ayurvedic')) {
        loadCategoryProducts('ayurved');
    } else if (path.includes('personal-care')) {
        loadCategoryProducts('personal-care');
    } else if (path.includes('food-drinks')) {
        loadCategoryProducts('Food & Drinks');
    } else if (path.includes('medical-devices')) {
        loadCategoryProducts('devices');
    } else if (path.includes('nutrition')) {
        loadCategoryProducts('Nutrition');
    } else if (path.includes('protein')) {
        loadCategoryProducts('Protein');
    } else if (path.includes('home-care-hygiene')) {
        loadCategoryProducts('Home Care & Hygiene');
    } else if (path.includes('new-launches')) {
        loadCategoryProducts('New Launches');
    }
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(() => {
                window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
            }, 100);
        }
    }
});
