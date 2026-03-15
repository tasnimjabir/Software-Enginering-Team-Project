<?php
/**
 * MAT-MEE Admin | Carousel Manager
 * Path: MAT-MEE/Admin-matmee/carousel.php
 */

include 'header.php';

/* ─────────────────────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────────────────────── */
function jsonOut(array $d): never {
    header('Content-Type: application/json');
    echo json_encode($d);
    exit;
}

function uploadImage(): string|false {
    if (empty($_FILES['slide_image']['name']) || (int)$_FILES['slide_image']['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($_FILES['slide_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) return false;
    if ($_FILES['slide_image']['size'] > 5 * 1024 * 1024) return false;
    $dir = '../upload/carousel/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fn = 'slide_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    return move_uploaded_file($_FILES['slide_image']['tmp_name'], $dir . $fn) ? $dir . $fn : false;
}

function sanitiseSlideFields(): array {
    /* Font sizes stored as rem strings (e.g. "3rem", "1.1rem") */
    $tSize = preg_match('/^\d+(\.\d+)?rem$/', $_POST['title_size']    ?? '') ? $_POST['title_size']    : '3rem';
    $sSize = preg_match('/^\d+(\.\d+)?rem$/', $_POST['subtitle_size'] ?? '') ? $_POST['subtitle_size'] : '1.1rem';
    return [
        'title'           => trim($_POST['title']       ?? ''),
        'subtitle'        => trim($_POST['subtitle']    ?? ''),
        'button_text'     => trim($_POST['button_text'] ?? ''),
        'button_link'     => trim($_POST['button_link'] ?? ''),
        'title_size'      => $tSize,
        'subtitle_size'   => $sSize,
        'text_position'   => in_array($_POST['text_position']??'', ['left','center','right']) ? $_POST['text_position'] : 'left',
        'text_valign'     => in_array($_POST['text_valign']  ??'', ['top','middle','bottom'])  ? $_POST['text_valign']   : 'middle',
        'overlay_color'   => preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['overlay_color']??'') ? $_POST['overlay_color'] : '#000000',
        'text_color'      => preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['text_color']   ??'') ? $_POST['text_color']    : '#ffffff',
        'overlay_opacity' => min(1.0, max(0.0, (float)($_POST['overlay_opacity'] ?? 0.45))),
        'sort_order'      => (int)($_POST['sort_order'] ?? 0),
        'is_active'       => isset($_POST['is_active']) ? 1 : 0,
    ];
}

/* ─────────────────────────────────────────────────────────────
   AJAX ENDPOINTS
───────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $action = trim($_POST['action'] ?? '');
    $sid    = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        jsonOut(['success' => (bool)$db->execute("DELETE FROM carousel_slides WHERE id=?", [$sid])]);
    }
    if ($action === 'reorder') {
        $items = json_decode($_POST['orders'] ?? '[]', true);
        if (is_array($items)) {
            foreach ($items as $it)
                $db->execute("UPDATE carousel_slides SET sort_order=? WHERE id=?", [(int)$it['order'], (int)$it['id']]);
        }
        jsonOut(['success' => true]);
    }
    jsonOut(['success' => false, 'message' => 'Unknown action.']);
}

/* ─────────────────────────────────────────────────────────────
   FORM POST (add / update → redirect keeping slide open)
───────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    $sid    = (int)($_POST['id'] ?? 0);
    $f      = sanitiseSlideFields();
    $up     = uploadImage();
    $img    = $up !== false ? $up : trim($_POST['existing_image'] ?? '');

    if (!$img) {
        header('Location: carousel.php?' . http_build_query(['msg'=>'Please choose an image.','type'=>'err','edit'=>$sid?:0]));
        exit;
    }

    $p = [$f['title'],$f['subtitle'],$f['button_text'],$f['button_link'],$img,
          $f['title_size'],$f['subtitle_size'],$f['text_position'],$f['text_valign'],
          $f['overlay_opacity'],$f['overlay_color'],$f['text_color'],$f['sort_order'],$f['is_active']];

    if ($action === 'add') {
        $db->execute("INSERT INTO carousel_slides
            (title,subtitle,button_text,button_link,image_path,title_size,subtitle_size,
             text_position,text_valign,overlay_opacity,overlay_color,text_color,sort_order,is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $p);
        header('Location: carousel.php?' . http_build_query(['msg'=>'Slide created.','edit'=>$db->getLastId()]));
        exit;
    }
    if ($action === 'update') {
        $db->execute("UPDATE carousel_slides SET
            title=?,subtitle=?,button_text=?,button_link=?,image_path=?,title_size=?,subtitle_size=?,
            text_position=?,text_valign=?,overlay_opacity=?,overlay_color=?,text_color=?,sort_order=?,is_active=?
            WHERE id=?", array_merge($p, [$sid]));
        header('Location: carousel.php?' . http_build_query(['msg'=>'Slide updated.','edit'=>$sid]));
        exit;
    }
}

/* ─────────────────────────────────────────────────────────────
   PAGE DATA
───────────────────────────────────────────────────────────── */
$slides    = $db->fetch("SELECT * FROM carousel_slides ORDER BY sort_order ASC, id ASC") ?: [];
$flash     = $_GET['msg']  ?? '';
$flashType = $_GET['type'] ?? 'ok';
$isNew     = isset($_GET['new']);
$editId    = $isNew ? null : (isset($_GET['edit']) ? (int)$_GET['edit'] : ($slides[0]['id'] ?? null));

$editSlide = null;
if (!$isNew && $editId !== null) {
    foreach ($slides as $s) { if ((int)$s['id'] === $editId) { $editSlide = $s; break; } }
}
$slidesJson = json_encode(array_values($slides), JSON_HEX_TAG | JSON_HEX_AMP);
?>
<link rel="stylesheet" href="carousel_admin.css">
<main class="content cm-page">

<!-- TOP BAR -->
<div class="cm-topbar">
    <div class="cm-topbar-left">
        <i class="bi bi-images"></i>
        <div><h1>Carousel Manager</h1><p>Manage homepage slider</p></div>
    </div>
    <a href="carousel.php?new=1" class="cm-btn cm-btn-primary">
        <i class="bi bi-plus-lg"></i> New Slide
    </a>
</div>

<?php if ($flash): ?>
<div class="cm-flash cm-flash-<?= $flashType==='err'?'err':'ok' ?>" id="flashMsg">
    <i class="bi bi-<?= $flashType==='err'?'exclamation-triangle-fill':'check-circle-fill' ?>"></i>
    <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<!-- FILMSTRIP -->
<div class="cm-strip-wrap">
    <button class="cm-strip-arrow" id="stripL" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="cm-strip" id="strip">
        <?php if (empty($slides)): ?>
            <div class="cm-strip-empty">No slides yet — click <strong>New Slide</strong> to begin.</div>
        <?php else: foreach ($slides as $i => $s): ?>
            <div class="cm-thumb <?= !$isNew&&(int)$s['id']===$editId?'active':'' ?> <?= !$s['is_active']?'dimmed':'' ?>"
                 data-id="<?= (int)$s['id'] ?>" title="<?= htmlspecialchars($s['title']?:'Untitled') ?>">
                <div class="cm-thumb-img" style="background-image:url('<?= htmlspecialchars($s['image_path']) ?>')"></div>
                <div class="cm-thumb-foot">
                    <span class="cm-thumb-name"><?= htmlspecialchars(mb_substr($s['title']?:'Untitled',0,18)) ?></span>
                    <?php if (!$s['is_active']): ?><i class="bi bi-eye-slash"></i><?php endif; ?>
                </div>
                <span class="cm-thumb-n"><?= $i+1 ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
    <button class="cm-strip-arrow" id="stripR" type="button"><i class="bi bi-chevron-right"></i></button>
</div>

<!-- WORKSPACE -->
<div class="cm-workspace">

    <!-- PREVIEW COLUMN -->
    <div class="cm-prev-col">
        <div class="cm-prev-bar">
            <i class="bi bi-display"></i>
            <span>Live Preview</span>
            <span class="cm-prev-ratio-tag">Matches front-end carousel</span>
        </div>

        <!-- 16:9 preview -->
        <div class="cm-prev-outer">
            <div class="cm-prev-wrap" id="prevWrap">
                <div class="cm-prev-bg"      id="prevBg"></div>
                <div class="cm-prev-overlay" id="prevOv"></div>
                <div class="cm-prev-stripe"></div>
                <div class="cm-prev-content" id="prevContent">
                    <div class="cm-prev-text" id="prevText">
                        <div class="cm-prev-title" id="prevTitle"></div>
                        <div class="cm-prev-sub"   id="prevSub"></div>
                        <div class="cm-prev-btn"   id="prevBtn"></div>
                    </div>
                </div>
                <div class="cm-prev-ph" id="prevPh">
                    <i class="bi bi-image"></i><span>No image</span>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="cm-prev-foot">
            <div class="cm-prev-nav">
                <button class="cm-btn cm-btn-ghost cm-btn-sm" id="prevNavL" type="button">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="cm-prev-counter" id="prevCounter">—</span>
                <button class="cm-btn cm-btn-ghost cm-btn-sm" id="prevNavR" type="button">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <button class="cm-btn cm-btn-danger cm-btn-sm" id="btnDel" type="button"
                    data-id="<?= (int)($editSlide['id']??0) ?>"
                    style="<?= $editSlide?'':'visibility:hidden' ?>">
                <i class="bi bi-trash3"></i> Delete
            </button>
        </div>
    </div>

    <!-- EDIT PANEL -->
    <div class="cm-edit-col">
        <div class="cm-edit-head">
            <div class="cm-edit-head-l">
                <i class="bi bi-<?= ($isNew||!$editSlide)?'plus-circle':'pencil-square' ?>"></i>
                <span><?php
                    if ($isNew)          echo 'New Slide';
                    elseif ($editSlide)  echo htmlspecialchars($editSlide['title']?:'Untitled');
                    else                 echo 'Select a slide';
                ?></span>
            </div>
            <?php if ($isNew||$editSlide): ?>
            <span class="cm-badge <?= $isNew?'cm-badge-new':'cm-badge-edit' ?>">
                <?= $isNew?'Creating':'Editing' ?>
            </span>
            <?php endif; ?>
        </div>

        <div class="cm-edit-body">
            <?php if (!$editSlide && !$isNew): ?>
            <div class="cm-ph">
                <i class="bi bi-hand-index-thumb"></i>
                <p>Click a slide in the strip above<br>or create a new one.</p>
            </div>

            <?php else: $s = $editSlide ?? []; ?>
            <form method="POST" enctype="multipart/form-data" id="theForm" novalidate>
                <input type="hidden" name="action"         value="<?= $editSlide?'update':'add' ?>">
                <input type="hidden" name="id"             value="<?= (int)($s['id']??0) ?>">
                <input type="hidden" name="existing_image" id="fExist" value="<?= htmlspecialchars($s['image_path']??'') ?>">

                <!-- Image -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-image"></i> Slide Image</div>
                    <div class="cm-dz" id="dz">
                        <img id="imgPrev" src="<?= htmlspecialchars($s['image_path']??'') ?>" alt=""
                             style="<?= empty($s['image_path'])?'display:none;':'' ?>position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                        <div id="dzPh" class="cm-dz-ph" <?= !empty($s['image_path'])?'style="display:none"':'' ?>>
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Click or drag to upload</span>
                            <small>JPG · PNG · WEBP · Max 5 MB</small>
                        </div>
                    </div>
                    <input type="file" name="slide_image" id="imgFile"
                           accept="image/jpeg,image/png,image/webp" style="display:none">
                </div>

                <!-- Text -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-type-h1"></i> Text Content</div>
                    <div class="cm-field">
                        <label class="cm-lbl" for="fTitle">Headline</label>
                        <input class="cm-inp" type="text" id="fTitle" name="title"
                               value="<?= htmlspecialchars($s['title']??'') ?>" placeholder="e.g. New Collection Arrived">
                    </div>
                    <div class="cm-field">
                        <label class="cm-lbl" for="fSub">Subtitle</label>
                        <textarea class="cm-inp" id="fSub" name="subtitle" rows="2" placeholder="Short tagline…"><?= htmlspecialchars($s['subtitle']??'') ?></textarea>
                    </div>
                    <div class="cm-row">
                        <div class="cm-field">
                            <label class="cm-lbl" for="fBtnTxt">Button Label</label>
                            <input class="cm-inp" type="text" id="fBtnTxt" name="button_text"
                                   value="<?= htmlspecialchars($s['button_text']??'') ?>" placeholder="Shop Now">
                        </div>
                        <div class="cm-field">
                            <label class="cm-lbl" for="fBtnUrl">Button URL</label>
                            <input class="cm-inp" type="text" id="fBtnUrl" name="button_link"
                                   value="<?= htmlspecialchars($s['button_link']??'') ?>" placeholder="products.php">
                        </div>
                    </div>
                </div>

                <!-- Typography -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-fonts"></i> Typography</div>
                    <?php
                        $tsStr = $s['title_size']    ?? '3rem';
                        $ssStr = $s['subtitle_size'] ?? '1.1rem';
                        $tsVal = (float)preg_replace('/[^0-9.]/', '', $tsStr) ?: 3.0;
                        $ssVal = (float)preg_replace('/[^0-9.]/', '', $ssStr) ?: 1.1;
                    ?>
                    <div class="cm-field">
                        <label class="cm-lbl">Headline Size <strong id="vTS"><?= $tsStr ?></strong></label>
                        <input class="cm-range" type="range" id="rTS" min="1.5" max="6" step="0.1" value="<?= $tsVal ?>">
                        <input type="hidden" id="fTS" name="title_size" value="<?= $tsStr ?>">
                    </div>
                    <div class="cm-field">
                        <label class="cm-lbl">Subtitle Size <strong id="vSS"><?= $ssStr ?></strong></label>
                        <input class="cm-range" type="range" id="rSS" min="0.7" max="2.5" step="0.05" value="<?= $ssVal ?>">
                        <input type="hidden" id="fSS" name="subtitle_size" value="<?= $ssStr ?>">
                    </div>
                </div>

                <!-- Position -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-layout-text-sidebar-reverse"></i> Text Position</div>
                    <?php $hPos=$s['text_position']??'left'; $vPos=$s['text_valign']??'middle'; ?>
                    <div class="cm-row">
                        <div class="cm-field">
                            <label class="cm-lbl">Horizontal</label>
                            <div class="cm-seg">
                                <?php foreach(['left'=>'Left','center'=>'Ctr','right'=>'Right'] as $v=>$l): ?>
                                <button type="button" class="cm-seg-b <?= $hPos===$v?'active':'' ?>"
                                        data-val="<?= $v ?>" data-tgt="fHP"><?= $l ?></button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="text_position" id="fHP" value="<?= htmlspecialchars($hPos) ?>">
                        </div>
                        <div class="cm-field">
                            <label class="cm-lbl">Vertical</label>
                            <div class="cm-seg">
                                <?php foreach(['top'=>'Top','middle'=>'Mid','bottom'=>'Bot'] as $v=>$l): ?>
                                <button type="button" class="cm-seg-b <?= $vPos===$v?'active':'' ?>"
                                        data-val="<?= $v ?>" data-tgt="fVP"><?= $l ?></button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="text_valign" id="fVP" value="<?= htmlspecialchars($vPos) ?>">
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-palette2"></i> Colors &amp; Overlay</div>
                    <?php
                        $ovCol=$s['overlay_color']??'#000000';
                        $txCol=$s['text_color']??'#ffffff';
                        $opVal=isset($s['overlay_opacity'])?(float)$s['overlay_opacity']:0.45;
                    ?>
                    <div class="cm-row">
                        <div class="cm-field">
                            <label class="cm-lbl">Overlay Color</label>
                            <div class="cm-cp">
                                <input type="color" id="fOC" name="overlay_color" value="<?= htmlspecialchars($ovCol) ?>">
                                <span id="vOC"><?= htmlspecialchars($ovCol) ?></span>
                            </div>
                        </div>
                        <div class="cm-field">
                            <label class="cm-lbl">Text Color</label>
                            <div class="cm-cp">
                                <input type="color" id="fTC" name="text_color" value="<?= htmlspecialchars($txCol) ?>">
                                <span id="vTC"><?= htmlspecialchars($txCol) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="cm-field" style="margin-top:10px">
                        <label class="cm-lbl">Overlay Opacity <strong><span id="vOP"><?= round($opVal*100) ?></span>%</strong></label>
                        <input class="cm-range" type="range" id="rOP" min="0" max="1" step="0.01" value="<?= $opVal ?>">
                        <input type="hidden" id="fOP" name="overlay_opacity" value="<?= $opVal ?>">
                    </div>
                </div>

                <!-- Settings -->
                <div class="cm-sec">
                    <div class="cm-sec-hd"><i class="bi bi-gear-fill"></i> Settings</div>
                    <div class="cm-row">
                        <div class="cm-field">
                            <label class="cm-lbl" for="fOrd">Sort Order</label>
                            <input class="cm-inp" type="number" id="fOrd" name="sort_order"
                                   value="<?= (int)($s['sort_order']??0) ?>" min="0">
                        </div>
                        <div class="cm-field cm-field-center">
                            <label class="cm-tog-wrap">
                                <input type="checkbox" id="fAct" name="is_active"
                                       <?= (!isset($s['is_active'])||$s['is_active'])?'checked':'' ?>>
                                <span class="cm-tog-tr"><span class="cm-tog-th"></span></span>
                                <span class="cm-tog-lbl">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="cm-form-foot">
                    <a href="carousel.php" class="cm-btn cm-btn-outline">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                    <button type="submit" class="cm-btn cm-btn-save" id="btnSave">
                        <i class="bi bi-<?= $editSlide?'floppy':'plus-lg' ?>"></i>
                        <?= $editSlide?'Save Changes':'Create Slide' ?>
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /cm-workspace -->
</main>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
'use strict';

/* ═══════════════════════════════════════════════════
   DATA
═══════════════════════════════════════════════════ */
const ALL_SLIDES = <?= $slidesJson ?>;
let previewIdx = (function(){
    <?php if ($editSlide): ?>
    const i = ALL_SLIDES.findIndex(s => parseInt(s.id) === <?= (int)$editSlide['id'] ?>);
    return i >= 0 ? i : 0;
    <?php else: ?>return 0;<?php endif; ?>
}());

/* ═══════════════════════════════════════════════════
   DOM
═══════════════════════════════════════════════════ */
const strip       = document.getElementById('strip');
const stripL      = document.getElementById('stripL');
const stripR      = document.getElementById('stripR');
const prevBg      = document.getElementById('prevBg');
const prevOv      = document.getElementById('prevOv');
const prevContent = document.getElementById('prevContent');
const prevText    = document.getElementById('prevText');
const prevTitle   = document.getElementById('prevTitle');
const prevSub     = document.getElementById('prevSub');
const prevBtn     = document.getElementById('prevBtn');
const prevPh      = document.getElementById('prevPh');
const prevCounter = document.getElementById('prevCounter');
const prevNavL    = document.getElementById('prevNavL');
const prevNavR    = document.getElementById('prevNavR');
const btnDel      = document.getElementById('btnDel');
const theForm     = document.getElementById('theForm');
const btnSave     = document.getElementById('btnSave');
const imgPrev     = document.getElementById('imgPrev');
const dz          = document.getElementById('dz');
const imgFile     = document.getElementById('imgFile');
const dzPh        = document.getElementById('dzPh');

const F = {
    title : document.getElementById('fTitle'),
    sub   : document.getElementById('fSub'),
    btn   : document.getElementById('fBtnTxt'),
    ts    : document.getElementById('fTS'),
    ss    : document.getElementById('fSS'),
    hp    : document.getElementById('fHP'),
    vp    : document.getElementById('fVP'),
    oc    : document.getElementById('fOC'),
    tc    : document.getElementById('fTC'),
    op    : document.getElementById('fOP'),
};
const hasForm = !!F.title;

/* ═══════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════ */
function hexToRgba(hex, a) {
    hex = (hex||'#000').replace('#','');
    if (hex.length===3) hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    const n = parseInt(hex,16)||0;
    return `rgba(${(n>>16)&255},${(n>>8)&255},${n&255},${a})`;
}

async function apiFetch(params) {
    const r = await fetch('carousel.php', {
        method  : 'POST',
        headers : { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' },
        body    : new URLSearchParams(params).toString()
    });
    return r.json();
}

function showFlash(msg, type='ok') {
    let el = document.getElementById('inlineFlash');
    if (!el) {
        el = document.createElement('div'); el.id='inlineFlash';
        document.querySelector('.cm-topbar').after(el);
    }
    el.className = `cm-flash cm-flash-${type}`;
    el.innerHTML = `<i class="bi bi-${type==='err'?'exclamation-triangle-fill':'check-circle-fill'}"></i> ${msg}`;
    clearTimeout(el._t);
    el._t = setTimeout(() => el.remove(), 3500);
}

/* ═══════════════════════════════════════════════════
   RENDER PREVIEW  — font sizes are rem strings (e.g. "3rem")
═══════════════════════════════════════════════════ */
function renderPreview(d) {
    if (!prevBg) return;

    /* background */
    const hasImg = !!(d.image_path && d.image_path.trim());
    prevBg.style.backgroundImage = hasImg ? `url('${d.image_path}')` : 'none';
    prevPh.style.display = hasImg ? 'none' : 'flex';

    /* overlay */
    prevOv.style.background = hexToRgba(d.overlay_color||'#000000', parseFloat(d.overlay_opacity??0.45));

    /* text */
    const tc = d.text_color || '#ffffff';

    prevTitle.textContent    = d.title    || '';
    prevTitle.style.fontSize = d.title_size    || '3rem';
    prevTitle.style.color    = tc;

    prevSub.textContent    = d.subtitle || '';
    prevSub.style.fontSize = d.subtitle_size || '1.1rem';
    prevSub.style.color    = tc;
    prevSub.style.display  = d.subtitle ? '' : 'none';

    if (d.button_text) {
        prevBtn.textContent = d.button_text;
        prevBtn.style.cssText = `display:inline-flex;color:${tc};border-color:${tc}`;
    } else {
        prevBtn.style.display = 'none';
    }

    /* alignment — mirrors mm-slide-content (display:flex, row)
       justify-content = horizontal (main axis)
       align-items     = vertical   (cross axis)               */
    const hMap = {left:'flex-start', center:'center', right:'flex-end'};
    const vMap = {top:'flex-start',  middle:'center', bottom:'flex-end'};
    prevContent.style.justifyContent = hMap[d.text_position||'left']  || 'flex-start';
    prevContent.style.alignItems     = vMap[d.text_valign  ||'middle'] || 'center';
    prevText.style.textAlign         = d.text_position || 'left';
}

function renderFromForm() {
    if (!hasForm) return;
    renderPreview({
        image_path      : (imgPrev?.style.display!=='none') ? imgPrev?.src : '',
        overlay_color   : F.oc?.value  || '#000000',
        overlay_opacity : F.op?.value  || 0.45,
        text_color      : F.tc?.value  || '#ffffff',
        title           : F.title?.value  || '',
        subtitle        : F.sub?.value    || '',
        button_text     : F.btn?.value    || '',
        title_size      : F.ts?.value  || '3rem',
        subtitle_size   : F.ss?.value  || '1.1rem',
        text_position   : F.hp?.value  || 'left',
        text_valign     : F.vp?.value  || 'middle',
    });
}

/* ═══════════════════════════════════════════════════
   PREVIEW NAV (slide counter + arrows)
═══════════════════════════════════════════════════ */
function updateCounter() {
    if (prevCounter)
        prevCounter.textContent = ALL_SLIDES.length ? `${previewIdx+1} / ${ALL_SLIDES.length}` : '—';
}

function setActiveThumb(id) {
    strip?.querySelectorAll('.cm-thumb').forEach(t =>
        t.classList.toggle('active', parseInt(t.dataset.id)===id));
}

function previewGoto(idx) {
    if (!ALL_SLIDES.length) return;
    previewIdx = ((idx % ALL_SLIDES.length) + ALL_SLIDES.length) % ALL_SLIDES.length;
    renderPreview(ALL_SLIDES[previewIdx]);
    setActiveThumb(parseInt(ALL_SLIDES[previewIdx].id));
    updateCounter();
    strip?.querySelector(`[data-id="${ALL_SLIDES[previewIdx].id}"]`)
         ?.scrollIntoView({inline:'nearest', behavior:'smooth'});
}

prevNavL?.addEventListener('click', () => previewGoto(previewIdx - 1));
prevNavR?.addEventListener('click', () => previewGoto(previewIdx + 1));

/* ═══════════════════════════════════════════════════
   STRIP SCROLL + THUMB CLICK
   Clicking a thumb always opens that slide in the editor
   (full page nav). Only the bottom prev/next arrows do
   preview-only browsing without reloading the form.
═══════════════════════════════════════════════════ */
stripL?.addEventListener('click', () => strip.scrollBy({left:-240, behavior:'smooth'}));
stripR?.addEventListener('click', () => strip.scrollBy({left: 240, behavior:'smooth'}));

strip?.addEventListener('click', e => {
    const th = e.target.closest('.cm-thumb');
    if (!th || isDragging) return;
    const id = parseInt(th.dataset.id);
    /* Always navigate — loads the correct slide into the form */
    window.location = `carousel.php?edit=${id}`;
});

/* ═══════════════════════════════════════════════════
   SORTABLE DRAG-AND-DROP REORDER
═══════════════════════════════════════════════════ */
/* ═══════════════════════════════════════════════════
   SORTABLE DRAG-AND-DROP REORDER
   We track whether an actual drag occurred so the click
   handler (which navigates) is not suppressed by Sortable.
═══════════════════════════════════════════════════ */
let isDragging = false;

if (strip && typeof Sortable !== 'undefined') {
    Sortable.create(strip, {
        animation  : 140,
        filter     : '.cm-strip-empty',
        ghostClass : 'cm-thumb-ghost',
        onStart()  { isDragging = true; },
        async onEnd(evt) {
            /* Use setTimeout so the click event fires first, then we reset */
            setTimeout(() => { isDragging = false; }, 0);
            if (evt.oldIndex === evt.newIndex) return;
            const orders = [...strip.querySelectorAll('.cm-thumb')]
                .map((el,i) => ({id: el.dataset.id, order: i+1}));
            try {
                const d = await apiFetch({action:'reorder', orders: JSON.stringify(orders)});
                showFlash(d.success ? 'Order saved.' : 'Reorder failed.', d.success?'ok':'err');
            } catch { showFlash('Network error.','err'); }
        }
    });
}

/* ═══════════════════════════════════════════════════
   DELETE
═══════════════════════════════════════════════════ */
btnDel?.addEventListener('click', async () => {
    if (!confirm('Delete this slide permanently?')) return;
    const id = btnDel.dataset.id;
    btnDel.disabled = true;
    try {
        const d = await apiFetch({action:'delete', id});
        if (d.success) window.location = 'carousel.php?msg=Slide+deleted.';
        else { showFlash(d.message||'Delete failed.','err'); btnDel.disabled=false; }
    } catch { showFlash('Network error.','err'); btnDel.disabled=false; }
});

/* ═══════════════════════════════════════════════════
   IMAGE UPLOAD & DRAG-DROP
═══════════════════════════════════════════════════ */
if (hasForm) {
    dz?.addEventListener('click', e => { if (e.target!==imgPrev) imgFile?.click(); });

    imgFile?.addEventListener('change', function(){
        if (!this.files?.[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            imgPrev.src=e.target.result; imgPrev.style.display='block';
            if (dzPh) dzPh.style.display='none';
            renderFromForm();
        };
        reader.readAsDataURL(this.files[0]);
    });

    ['dragenter','dragover'].forEach(ev =>
        dz?.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dz-over'); }));
    ['dragleave','drop'].forEach(ev =>
        dz?.addEventListener(ev, () => dz.classList.remove('dz-over')));
    dz?.addEventListener('drop', e => {
        e.preventDefault();
        const f = e.dataTransfer.files[0];
        if (!f?.type.startsWith('image/')) return;
        const dt = new DataTransfer(); dt.items.add(f); imgFile.files=dt.files;
        imgFile.dispatchEvent(new Event('change'));
    });

    /* Live preview listeners */
    [F.title, F.sub, F.btn].forEach(el => el?.addEventListener('input', renderFromForm));

    document.getElementById('rTS')?.addEventListener('input', function(){
        const v = this.value + 'rem';
        F.ts.value = v;
        document.getElementById('vTS').textContent = v;
        renderFromForm();
    });
    document.getElementById('rSS')?.addEventListener('input', function(){
        const v = this.value + 'rem';
        F.ss.value = v;
        document.getElementById('vSS').textContent = v;
        renderFromForm();
    });
    document.getElementById('rOP')?.addEventListener('input', function(){
        F.op.value=this.value;
        document.getElementById('vOP').textContent=Math.round(this.value*100);
        renderFromForm();
    });
    F.oc?.addEventListener('input', function(){
        document.getElementById('vOC').textContent=this.value; renderFromForm();
    });
    F.tc?.addEventListener('input', function(){
        document.getElementById('vTC').textContent=this.value; renderFromForm();
    });

    /* Segmented buttons (delegated) */
    document.addEventListener('click', e => {
        const btn = e.target.closest('.cm-seg-b');
        if (!btn) return;
        btn.closest('.cm-seg').querySelectorAll('.cm-seg-b').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const tgt = document.getElementById(btn.dataset.tgt);
        if (tgt) tgt.value = btn.dataset.val;
        renderFromForm();
    });

    /* Save: loading state */
    theForm?.addEventListener('submit', () => {
        if (btnSave) { btnSave.disabled=true; btnSave.innerHTML='<i class="bi bi-hourglass-split spin"></i> Saving…'; }
    });

    renderFromForm(); /* initial render */
}

/* ═══════════════════════════════════════════════════
   BOOT
═══════════════════════════════════════════════════ */
if (!hasForm && ALL_SLIDES.length) renderPreview(ALL_SLIDES[previewIdx]);
updateCounter();

setTimeout(()=>{
    strip?.querySelector('.cm-thumb.active')?.scrollIntoView({inline:'nearest',behavior:'smooth'});
}, 80);

/* Auto-dismiss PHP flash */
const flashMsg = document.getElementById('flashMsg');
if (flashMsg) setTimeout(()=>{
    flashMsg.style.transition='opacity 0.5s'; flashMsg.style.opacity='0';
    setTimeout(()=>flashMsg.remove(), 500);
}, 3000);

})();
</script>