<link href="asset/css/carousel_user.css" rel="stylesheet">
    <?php
/**
 * MAT-MEE | Carousel Component
 * Path: MAT-MEE/components/Carousel.php
 *
 * Font sizes are stored in DB as rem strings (e.g. "3rem", "1.1rem").
 * Applied directly as inline style — fixed size, works at all breakpoints.
 */

$conn = DatabaseConnection::getInstance();

$slides = $conn->fetch(
    "SELECT * FROM carousel_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
);

$total = count($slides);
if ($total === 0) return;

function hexToRgba(string $hex, float $a): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return sprintf('rgba(%d,%d,%d,%.2f)',
        hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)), $a);
}
?>

<section class="mm-carousel" id="mmCarousel" aria-label="প্রধান ব্যানার">

    <div class="mm-slides">
        <?php foreach ($slides as $i => $s):
            $img     = 'upload/carousel/' . htmlspecialchars(basename($s['image_path']));
            $overlay = hexToRgba($s['overlay_color'] ?? '#000', (float)($s['overlay_opacity'] ?? 0.5));
            $color   = htmlspecialchars($s['text_color'] ?? '#ffffff');

            /* Alignment — must match admin preview flex logic:
               justify-content = horizontal (main axis in row flex)
               align-items     = vertical   (cross axis)            */
            $hAlign = match($s['text_position'] ?? 'left') {
                'center' => 'center',
                'right'  => 'flex-end',
                default  => 'flex-start'
            };
            $vAlign = match($s['text_valign'] ?? 'middle') {
                'top'    => 'flex-start',
                'bottom' => 'flex-end',
                default  => 'center'
            };
            $textAlign = htmlspecialchars($s['text_position'] ?? 'left');

            /* Font sizes: stored as rem strings, used directly */
            $titleSize = htmlspecialchars($s['title_size']    ?? '3rem');
            $subSize   = htmlspecialchars($s['subtitle_size'] ?? '1.1rem');
        ?>
        <div class="mm-slide <?= $i === 0 ? 'mm-active' : '' ?>" data-index="<?= $i ?>">
            <div class="mm-slide-bg" style="background-image:url('<?= $img ?>')"></div>
            <div class="mm-slide-overlay" style="background:<?= $overlay ?>"></div>

            <div class="mm-slide-content"
                 style="justify-content:<?= $hAlign ?>;align-items:<?= $vAlign ?>">
                <div class="mm-text" style="text-align:<?= $textAlign ?>">

                    <?php if (!empty($s['title'])): ?>
                    <h2 class="mm-title"
                        style="font-size:<?= $titleSize ?>;color:<?= $color ?>">
                        <?= htmlspecialchars($s['title']) ?>
                    </h2>
                    <?php endif; ?>

                    <?php if (!empty($s['subtitle'])): ?>
                    <p class="mm-subtitle"
                       style="font-size:<?= $subSize ?>;color:<?= $color ?>">
                        <?= htmlspecialchars($s['subtitle']) ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($s['button_text']) && !empty($s['button_link'])): ?>
                    <div class="mm-btns">
                        <a href="<?= htmlspecialchars($s['button_link']) ?>" class="mm-btn-primary">
                            <?= htmlspecialchars($s['button_text']) ?>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="products.php" class="mm-btn-outline">সব পণ্য দেখুন</a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Progress bar -->
    <div class="mm-progress"><div class="mm-progress-fill" id="mmProgress"></div></div>

    <!-- Arrows -->
    <button class="mm-arrow mm-prev" id="mmPrev" aria-label="আগের স্লাইড">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="mm-arrow mm-next" id="mmNext" aria-label="পরের স্লাইড">
        <i class="bi bi-chevron-right"></i>
    </button>

    <!-- Dots -->
    <?php if ($total > 1): ?>
    <div class="mm-dots" id="mmDots">
        <?php for ($i = 0; $i < $total; $i++): ?>
        <button class="mm-dot <?= $i === 0 ? 'mm-dot-active' : '' ?>"
                data-to="<?= $i ?>" aria-label="স্লাইড <?= $i+1 ?>"></button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</section>

<script>
(function(){
    const wrap   = document.getElementById('mmCarousel');
    if (!wrap) return;

    const slides = wrap.querySelectorAll('.mm-slide');
    const dots   = wrap.querySelectorAll('.mm-dot');
    const fill   = document.getElementById('mmProgress');
    const TOTAL  = slides.length;
    const DELAY  = 5000;
    let current  = 0;
    let timer    = null;

    function goTo(next) {
        if (next === current) return;
        slides[current].classList.remove('mm-active');
        dots[current]?.classList.remove('mm-dot-active');
        current = ((next % TOTAL) + TOTAL) % TOTAL;
        slides[current].classList.add('mm-active');
        dots[current]?.classList.add('mm-dot-active');
        resetProgress();
    }

    function startAuto() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), DELAY);
    }

    function resetProgress() {
        if (!fill) return;
        fill.style.transition = 'none';
        fill.style.width = '0%';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            fill.style.transition = `width ${DELAY}ms linear`;
            fill.style.width = '100%';
        }));
    }

    document.getElementById('mmPrev').addEventListener('click', () => { goTo(current - 1); startAuto(); });
    document.getElementById('mmNext').addEventListener('click', () => { goTo(current + 1); startAuto(); });
    dots.forEach(d => d.addEventListener('click', () => { goTo(parseInt(d.dataset.to)); startAuto(); }));

    /* Touch swipe */
    let tx = 0;
    wrap.addEventListener('touchstart', e => { tx = e.changedTouches[0].clientX; }, { passive:true });
    wrap.addEventListener('touchend',   e => {
        const diff = tx - e.changedTouches[0].clientX;
        if (Math.abs(diff) < 40) return;
        goTo(diff > 0 ? current + 1 : current - 1);
        startAuto();
    });

    /* Pause on hover */
    wrap.addEventListener('mouseenter', () => { clearInterval(timer); if (fill) fill.style.transition='none'; });
    wrap.addEventListener('mouseleave', () => { startAuto(); resetProgress(); });

    startAuto();
    resetProgress();
})();
</script>