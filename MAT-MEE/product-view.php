<?php 
ob_start(); // Start output buffering to prevent header issues
require_once 'components/config-page.php'; 
?>

<?php
/* ══════════════════════════════════════════════════════
   MAT-MEE  —  Product Detail Page
   URL: product-view.php?slug=linen-shirt
   or:  product-view.php?id=12
══════════════════════════════════════════════════════ */

/* ── Resolve product ── */
$product = null;

if (!empty($_GET['slug'])) {
    $slug = $_GET['slug'];
    $product = $conn->fetchOne(
        "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, c.id AS cat_id
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.slug = ?
         LIMIT 1",
        [$slug]
    );

} elseif (!empty($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $product = $conn->fetchOne(
        "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, c.id AS cat_id
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = ?
         LIMIT 1",
        [$pid]
    );
}

if (!$product) {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

/* ── Increment view counter ── */
$conn->execute("UPDATE products SET views = views + 1 WHERE id = ?", [(int)$product['id']]);

/* ── Gallery images (from product_images table) ── */
$galleryRes = $conn->fetch(
    "SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC",
    [(int)$product['id']]
);
$galleryImgs = [];
foreach ($galleryRes as $row) {
    $galleryImgs[] = $row['image'];
}
/* Always put main_image first if set */
$allImages = [];
if (!empty($product['main_image'])) {
    $allImages[] = $product['main_image'];
}
foreach ($galleryImgs as $gi) {
    if ($gi !== $product['main_image']) $allImages[] = $gi;
}
if (empty($allImages)) $allImages[] = 'image/placeholder.jpg';

/* ── Sizes for this product's category ── */
$sizes = [];
if (!empty($product['cat_id'])) {
    $sizeRes = $conn->fetch(
        "SELECT size_name FROM sizes WHERE category_id = ? ORDER BY id ASC",
        [(int)$product['cat_id']]
    );
    foreach ($sizeRes as $row) {
        $sizes[] = $row['size_name'];
    }
}

/* ── Pricing helpers ── */
$hasDiscount = !empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price'];
$finalPrice  = $hasDiscount ? (float)$product['discount_price'] : (float)$product['price'];
$discPct     = $hasDiscount ? round((1 - $product['discount_price'] / $product['price']) * 100) : 0;
$savings     = $hasDiscount ? ($product['price'] - $product['discount_price']) : 0;

/* ── Fetch metadata ── */
$generalInfo = $conn->fetchOne("SELECT value FROM metadata WHERE name = ?", ['general_info']);
$sizeGuide = $conn->fetchOne("SELECT value FROM metadata WHERE name = ?", ['size_guide']);

/* ── Fetch FAQs ── */
$faqs = $conn->fetch(
    "SELECT id, question, answer FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC"
);

/* ── Related products (same category, 4 random) ── */
$related = [];
if (!empty($product['cat_id'])) {
    $related = $conn->fetch(
        "SELECT id, name, slug, price, discount_price, main_image
         FROM products
         WHERE category_id = ?
           AND id != ?
         ORDER BY RAND() LIMIT 4",
        [(int)$product['cat_id'], (int)$product['id']]
    );
}

$pageTitle = htmlspecialchars($product['name']) . ' — MAT-MEE';
?>

<link rel="stylesheet" href="asset/css/product.css">

<!-- ░░░ PAGE ░░░ -->
<div class="pv-page">

  <!-- ── Breadcrumb ── -->
  <nav class="pv-breadcrumb">
    <div class="pv-wrap">
      <a href="index.php">Home</a>
      <span class="pv-bc-sep"><i class="bi bi-chevron-right"></i></span>
      <a href="shop.php">Shop</a>
      <?php if ($product['cat_name']): ?>
        <span class="pv-bc-sep"><i class="bi bi-chevron-right"></i></span>
        <a href="shop.php?category=<?= htmlspecialchars($product['cat_slug']) ?>"><?= htmlspecialchars($product['cat_name']) ?></a>
      <?php endif; ?>
      <span class="pv-bc-sep"><i class="bi bi-chevron-right"></i></span>
      <span class="pv-bc-cur"><?= htmlspecialchars($product['name']) ?></span>
    </div>
  </nav>

  <!-- ── Main section ── -->
  <section class="pv-main">
    <div class="pv-wrap">
      <div class="pv-grid">

        <!-- ════ LEFT  Info & Description ════ -->
        <div class="pv-left-panel">

          <!-- Category tag -->
          <?php if ($product['cat_name']): ?>
            <a class="pv-cat-tag" href="shop.php?category=<?= htmlspecialchars($product['cat_slug']) ?>">
              <i class="bi bi-tag"></i> <?= htmlspecialchars($product['cat_name']) ?>
            </a>
          <?php endif; ?>

          <!-- Title -->
          <h1 class="pv-title"><?= htmlspecialchars($product['name']) ?></h1>

          <!-- Views -->
          <p class="pv-views"><i class="bi bi-eye"></i> <?= number_format($product['views']) ?> views</p>

          <!-- Price block -->
          <div class="pv-price-block">
            <span class="pv-price-final">৳<?= number_format($finalPrice, 0) ?></span>
            <?php if ($hasDiscount): ?>
              <span class="pv-price-orig">৳<?= number_format($product['price'], 0) ?></span>
              <span class="pv-save-pill">Save ৳<?= number_format($savings, 0) ?></span>
            <?php endif; ?>
          </div>

          <!-- Divider -->
          <div class="pv-hr"></div>

          <!-- ── Size picker ── -->
          <?php if (!empty($sizes)): ?>
          <div class="pv-option-row">
            <label class="pv-opt-label">Size <strong id="pvSizeLbl"></strong></label>
            <div class="pv-size-wrap" id="pvSizeWrap">
              <?php foreach ($sizes as $sz): ?>
                <button class="pv-size-chip" data-val="<?= htmlspecialchars($sz) ?>"><?= htmlspecialchars($sz) ?></button>
              <?php endforeach; ?>
            </div>
            <p class="pv-opt-hint" id="pvSizeErr" style="display:none">
              <i class="bi bi-exclamation-circle"></i> Please select a size
            </p>
          </div>
          <?php endif; ?>

          <!-- ── Quantity ── -->
          <div class="pv-option-row">
            <label class="pv-opt-label">Quantity</label>
            <div class="pv-qty-box">
              <button class="pv-qty-btn" id="pvQtyDec" aria-label="Decrease"><i class="bi bi-dash-lg"></i></button>
              <input type="number" class="pv-qty-inp" id="pvQtyInp" value="1" min="1" max="99" readonly>
              <button class="pv-qty-btn" id="pvQtyInc" aria-label="Increase"><i class="bi bi-plus-lg"></i></button>
            </div>
          </div>

          <!-- ── Action buttons ── -->
          <div class="pv-actions">
            <button class="pv-btn-cart" id="pvBtnCart">
              <i class="bi bi-bag-plus-fill"></i>
              <span>Add to Cart</span>
            </button>
            <button class="pv-btn-buy" id="pvBtnBuy">
              <i class="bi bi-lightning-charge-fill"></i>
              <span>Buy Now</span>
            </button>
          </div>

          <!-- ── Trust strip ── -->
          <div class="pv-trust">
            <div class="pv-trust-item">
              <i class="bi bi-truck"></i><span>Free Delivery</span>
            </div>
            <div class="pv-trust-item">
              <i class="bi bi-arrow-counterclockwise"></i><span>7 Days Return</span>
            </div>
            <div class="pv-trust-item">
              <i class="bi bi-shield-fill-check"></i><span>Original Product</span>
            </div>
            <div class="pv-trust-item">
              <i class="bi bi-cash-stack"></i><span>Cash on Delivery</span>
            </div>
          </div>

          <!-- Share row -->
          <div class="pv-share-row">
            <span class="pv-share-lbl"><i class="bi bi-share"></i> Share:</span>
            <a class="pv-share-btn" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" aria-label="Facebook">
              <i class="bi bi-facebook"></i>
            </a>
            <button class="pv-share-btn" id="pvCopyBtn" title="Copy link" aria-label="Copy link">
              <i class="bi bi-link-45deg"></i>
            </button>
          </div>

          <!-- Description in left panel -->
          <div class="pv-desc-panel">
            <h3>Description</h3>
            <?php if (!empty($product['description'])): ?>
              <div class="pv-desc-body"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
            <?php else: ?>
              <p class="pv-empty-note">No description available for this product.</p>
            <?php endif; ?>
          </div>

        </div>
        <!-- /left panel -->

        <!-- ════ RIGHT  Gallery ════ -->
        <div class="pv-gallery" id="pvGallery">

          <!-- Discount ribbon -->
          <?php if ($hasDiscount): ?>
            <div class="pv-ribbon">-<?= $discPct ?>%</div>
          <?php endif; ?>

          <!-- Main image -->
          <div class="pv-main-img-box" id="pvMainBox">
            <img
              src="upload/products/<?= htmlspecialchars($allImages[0]) ?>"
              alt="<?= htmlspecialchars($product['name']) ?>"
              class="pv-main-img"
              id="pvMainImg"
            >
            <div class="pv-img-shimmer" id="pvShimmer"></div>
          </div>

          <!-- Thumbnail strip -->
          <?php if (count($allImages) > 1): ?>
          <div class="pv-thumb-strip" id="pvThumbStrip">
            <?php foreach ($allImages as $i => $img): ?>
              <button
                class="pv-thumb <?= $i === 0 ? 'active' : '' ?>"
                data-src="<?= htmlspecialchars($img) ?>"
                aria-label="Image <?= $i + 1 ?>"
              ><img src="upload/products/<?= htmlspecialchars($img) ?>" alt="" loading="lazy"></button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <!-- /gallery -->

      </div><!-- /grid -->
    </div>
  </section>

  <!-- ── Tabs Section ── -->
  <section class="pv-tabs-section">
    <div class="pv-wrap">
      <div class="pv-tabs">
        <button class="pv-tab active" data-panel="about">About</button>
        <?php if (!empty($sizeGuide['value'])): ?>
          <button class="pv-tab" data-panel="size">Size Guide</button>
        <?php endif; ?>
        <?php if (!empty($faqs)): ?>
          <button class="pv-tab" data-panel="faq">FAQ</button>
        <?php endif; ?>
        <span class="pv-tab-bar" id="pvTabBar"></span>
      </div>

      <!-- About Tab -->
      <div class="pv-panel active" id="pvPanelAbout">
        <div class="pv-panel-content">
          <?php if (!empty($generalInfo['value'])): ?>
            <div class="pv-general-info"><?= nl2br(htmlspecialchars($generalInfo['value'])) ?></div>
          <?php else: ?>
            <p class="pv-empty-note">General information not available.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Size Guide Tab -->
      <?php if (!empty($sizeGuide['value'])): ?>
      <div class="pv-panel" id="pvPanelSize">
        <div class="pv-panel-content">
          <?= nl2br(htmlspecialchars($sizeGuide['value'])) ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- FAQ Tab -->
      <?php if (!empty($faqs)): ?>
      <div class="pv-panel" id="pvPanelFaq">
        <div class="pv-faq-container">
          <?php foreach ($faqs as $faq): ?>
            <div class="pv-faq-item">
              <button class="pv-faq-question">
                <span><?= htmlspecialchars($faq['question']) ?></span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <div class="pv-faq-answer">
                <div class="pv-faq-answer-content">
                  <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── Related products ── -->
  <?php if (!empty($related)): ?>
  <section class="pv-related">
    <div class="pv-wrap">
      <h2 class="pv-section-heading">Related Products</h2>
      <div class="pv-related-grid">
        <?php foreach ($related as $r):
          $rHasDisc = !empty($r['discount_price']) && (float)$r['discount_price'] < (float)$r['price'];
          $rPrice   = $rHasDisc ? (float)$r['discount_price'] : (float)$r['price'];
          $rPct     = $rHasDisc ? round((1 - $r['discount_price'] / $r['price']) * 100) : 0;
        ?>
        <a class="pv-rel-card" href="product-view.php?slug=<?= htmlspecialchars($r['slug']) ?>">
          <div class="pv-rel-img">
            <?php if ($rHasDisc): ?>
              <span class="pv-rel-badge">-<?= $rPct ?>%</span>
            <?php endif; ?>
            <img src="upload/products/<?= htmlspecialchars($r['main_image'] ?: 'image/placeholder.jpg') ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
            <div class="pv-rel-hover-overlay"><i class="bi bi-eye"></i></div>
          </div>
          <div class="pv-rel-body">
            <p class="pv-rel-name"><?= htmlspecialchars($r['name']) ?></p>
            <div class="pv-rel-price">
              <span class="pv-rel-final">৳<?= number_format($rPrice, 0) ?></span>
              <?php if ($rHasDisc): ?>
                <span class="pv-rel-orig">৳<?= number_format($r['price'], 0) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div><!-- /pv-page -->

<!-- ░░ Hidden cart/buy form ░░ -->
<form id="pvCartForm" style="display:none" method="POST" action="cart.php">
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
  <input type="hidden" name="size"       id="pvFSize" value="">
  <input type="hidden" name="qty"        id="pvFQty"  value="1">
  <input type="hidden" name="action"     value="add">
</form>

<!-- ░░ Toast ░░ -->
<div class="pv-toast" id="pvToast"></div>

<script>
(function () {
  'use strict';

  /* ── state ── */
  var selSize    = '';
  var qty        = 1;
  var hasSizes   = <?= !empty($sizes) ? 'true' : 'false' ?>;
  var productId  = <?= (int)$product['id'] ?>;
  var productSlug = <?= json_encode($product['slug']) ?>;

  /* ── refs ── */
  var mainImg   = document.getElementById('pvMainImg');
  var shimmer   = document.getElementById('pvShimmer');
  var thumbs    = document.querySelectorAll('.pv-thumb');
  var sizeWrap  = document.getElementById('pvSizeWrap');
  var sizeLbl   = document.getElementById('pvSizeLbl');
  var sizeErr   = document.getElementById('pvSizeErr');
  var qtyInp    = document.getElementById('pvQtyInp');
  var qtyDec    = document.getElementById('pvQtyDec');
  var qtyInc    = document.getElementById('pvQtyInc');
  var btnCart   = document.getElementById('pvBtnCart');
  var btnBuy    = document.getElementById('pvBtnBuy');
  var cartForm  = document.getElementById('pvCartForm');
  var tabs      = document.querySelectorAll('.pv-tab');
  var tabBar    = document.getElementById('pvTabBar');
  var toast     = document.getElementById('pvToast');
  var copyBtn   = document.getElementById('pvCopyBtn');
  var faqQuestions = document.querySelectorAll('.pv-faq-question');

  /* ══ Gallery ══ */
  function swapImage(src) {
    shimmer.style.opacity = '1';
    mainImg.style.opacity = '0';
    var fullSrc = 'upload/products/' + src;
    var img = new Image();
    img.onload = function () {
      mainImg.src = fullSrc;
      mainImg.style.opacity = '1';
      shimmer.style.opacity = '0';
    };
    img.onerror = function () {
      mainImg.src = 'upload/products/image/placeholder.jpg';
      mainImg.style.opacity = '1';
      shimmer.style.opacity = '0';
    };
    img.src = fullSrc;
  }

  thumbs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      thumbs.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      swapImage(btn.dataset.src);
    });
  });

  /* ══ Size picker ══ */
  if (sizeWrap) {
    sizeWrap.addEventListener('click', function (e) {
      var chip = e.target.closest('.pv-size-chip');
      if (!chip) return;
      sizeWrap.querySelectorAll('.pv-size-chip').forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
      selSize = chip.dataset.val;
      if (sizeLbl) sizeLbl.textContent = '— ' + selSize;
      if (sizeErr) sizeErr.style.display = 'none';
    });
  }

  /* ══ Quantity ══ */
  qtyDec && qtyDec.addEventListener('click', function () {
    if (qty > 1) { qty--; qtyInp.value = qty; }
  });
  qtyInc && qtyInc.addEventListener('click', function () {
    if (qty < 99) { qty++; qtyInp.value = qty; }
  });

  /* ══ Validate ══ */
  function validate() {
    if (hasSizes && !selSize) {
      if (sizeErr) sizeErr.style.display = 'flex';
      sizeWrap && sizeWrap.classList.add('pv-shake');
      setTimeout(function () { sizeWrap && sizeWrap.classList.remove('pv-shake'); }, 500);
      return false;
    }
    return true;
  }

  /* ══ Add to cart — POST to cart.php ══ */
  btnCart && btnCart.addEventListener('click', function () {
    if (!validate()) return;
    document.getElementById('pvFSize').value = selSize;
    document.getElementById('pvFQty').value  = qty;
    cartForm.action = 'cart.php';
    cartForm.submit();
  });

  /* ══ Buy now — POST to buy-now.php ══ */
  btnBuy && btnBuy.addEventListener('click', function () {
    if (!validate()) return;
    document.getElementById('pvFSize').value = selSize;
    document.getElementById('pvFQty').value  = qty;
    cartForm.action = 'buy-now.php';
    cartForm.submit();
  });

  /* ══ Tabs ══ */
  function moveBar(tab) {
    tabBar.style.left  = tab.offsetLeft + 'px';
    tabBar.style.width = tab.offsetWidth + 'px';
  }
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      moveBar(tab);
      document.querySelectorAll('.pv-panel').forEach(function (p) { p.classList.remove('active'); });
      var panelId = 'pvPanel' + tab.dataset.panel.charAt(0).toUpperCase() + tab.dataset.panel.slice(1);
      var panel = document.getElementById(panelId);
      if (panel) panel.classList.add('active');
    });
  });
  var firstTab = document.querySelector('.pv-tab.active');
  if (firstTab) { setTimeout(function () { moveBar(firstTab); }, 30); }

  /* ══ FAQ Accordion ══ */
  faqQuestions.forEach(function (question) {
    question.addEventListener('click', function () {
      var item = this.closest('.pv-faq-item');
      var answer = item.querySelector('.pv-faq-answer');
      var isOpen = item.classList.contains('open');
      
      document.querySelectorAll('.pv-faq-item').forEach(function (q) {
        q.classList.remove('open');
        q.querySelector('.pv-faq-answer').style.maxHeight = '0';
      });
      
      if (!isOpen) {
        item.classList.add('open');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });

  /* ══ Copy link ══ */
  copyBtn && copyBtn.addEventListener('click', function () {
    navigator.clipboard && navigator.clipboard.writeText(window.location.href).then(function () {
      showToast('Link copied!', 'ok');
    });
  });

  /* ══ Toast ══ */
  var toastTimer;
  function showToast(msg, type) {
    toast.textContent = msg;
    toast.className   = 'pv-toast pv-toast-show pv-toast-' + (type || 'ok');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.className = 'pv-toast'; }, 3000);
  }

})();
</script>

<?php 
ob_end_flush(); // Flush output buffer
require_once 'components/page_close.php'; 
?>
