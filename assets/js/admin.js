/**
 * File Type Icons — Admin Script (Premium UI Controls)
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Coloris Color Picker
    if (typeof Coloris !== 'undefined') {
        Coloris({
            el: '.fti-color-picker',
            wrap: false,
            theme: 'default',
            themeMode: 'light',
            alpha: false,
            format: 'hex',
            swatches: [
                '#E53935', // pdf
                '#1565C0', // word
                '#2E7D32', // excel
                '#D84315', // powerpoint
                '#616161', // text
                '#8E24AA', // archives
                '#D81B60', // audio
                '#00897B', // images
                '#F57C00'  // video
            ]
        });
    }

    // 1. Size Synchronization (Slider and Numeric Field)
    const sizeSlider = document.getElementById('szsl');
    const sizeNumber = document.getElementById('szni');
    if (sizeSlider && sizeNumber) {
        sizeSlider.addEventListener('input', () => {
            sizeNumber.value = sizeSlider.value;
            updatePreviewSize(sizeSlider.value);
        });
        sizeNumber.addEventListener('input', () => {
            const val = Math.min(256, Math.max(8, parseInt(sizeNumber.value) || 8));
            sizeSlider.value = sizeNumber.value = val;
            updatePreviewSize(val);
        });
    }

    // 2. Icon Position (Visual Grid Selector)
    const positionButtons = document.querySelectorAll('.pbtn');
    const positionInput = document.getElementById('fti_icon_position');
    positionButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            positionButtons.forEach(b => b.classList.remove('on'));
            btn.classList.add('on');
            if (positionInput) {
                const pos = btn.getAttribute('data-value');
                positionInput.value = pos;
                updatePreviewPosition(pos);
            }
        });
    });

    // 3. Icon Style (Segmented Control)
    const styleButtons = document.querySelectorAll('.sbtn');
    const styleInput = document.getElementById('fti_icon_style');
    styleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            styleButtons.forEach(b => b.classList.remove('on'));
            btn.classList.add('on');
            if (styleInput) {
                styleInput.value = btn.getAttribute('data-value');
                updateAllPreviews();
                updatePreviewSvg();
            }
        });
    });

    // Live Link Preview Synchronizers
    const previewIconWrap = document.querySelector('.fti-preview-icon-wrap');
    const previewLink = document.querySelector('.fti-preview-link');
    const previewText = document.querySelector('.fti-preview-text');

    const updatePreviewSize = (size) => {
        if (previewIconWrap) {
            previewIconWrap.style.width = size + 'px';
            previewIconWrap.style.height = size + 'px';
        }
    };

    const updatePreviewPosition = (position) => {
        if (!previewLink || !previewIconWrap) return;
        
        previewLink.style.flexDirection = (position === 'above' || position === 'below') ? 'column' : 'row';
        previewIconWrap.style.margin = '';
        previewIconWrap.style.order = '';
        if (previewText) {
            previewText.style.order = '';
        }

        if (position === 'left') {
            previewIconWrap.style.marginRight = '6px';
            previewIconWrap.style.order = '1';
            if (previewText) previewText.style.order = '2';
        } else if (position === 'right') {
            previewIconWrap.style.marginLeft = '6px';
            previewIconWrap.style.order = '2';
            if (previewText) previewText.style.order = '1';
        } else if (position === 'above') {
            previewIconWrap.style.marginBottom = '4px';
            previewIconWrap.style.order = '1';
            if (previewText) previewText.style.order = '2';
        } else if (position === 'below') {
            previewIconWrap.style.marginTop = '4px';
            previewIconWrap.style.order = '2';
            if (previewText) previewText.style.order = '1';
        }
    };

    const adjustBrightness = (hex, percent) => {
        let num = parseInt(hex.replace("#", ""), 16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) + amt,
            G = (num >> 8 & 0x00FF) + amt,
            B = (num & 0x0000FF) + amt;
        return "#" + (0x1000000 + (R < 255 ? R < 0 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 0 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 0 ? 0 : B : 255)).toString(16).slice(1);
    };

    const getSvgWithColor = (template, styleVal, color) => {
        if (!template) return '';
        if (styleVal === '3') {
            const colorLight = adjustBrightness(color, 20);
            const colorDark = adjustBrightness(color, -30);
            return template.replace(/%%COLOR_LIGHT%%/g, colorLight).replace(/%%COLOR_DARK%%/g, colorDark);
        }
        return template.replace(/%%COLOR%%/g, color);
    };

    const updatePreviewSvg = () => {
        if (!previewIconWrap) return;
        const styleVal = styleInput ? styleInput.value : '1';
        const pdfPicker = document.querySelector('input[name="fti_icon_colors[pdf]"]');
        const color = pdfPicker ? pdfPicker.value : '#E53935';

        if (window.ftiAdmin && window.ftiAdmin.templates && window.ftiAdmin.templates[styleVal]) {
            const template = window.ftiAdmin.templates[styleVal]['pdf'];
            if (template) {
                previewIconWrap.innerHTML = getSvgWithColor(template, styleVal, color);
            }
        }
    };

    // Updates all SVG icon previews in the table
    const updateAllPreviews = () => {
        const styleVal = styleInput ? styleInput.value : '1';
        
        document.querySelectorAll('.ftrow').forEach(row => {
            const type = row.getAttribute('data-type');
            const picker = row.querySelector('.fti-color-picker');
            const previewContainer = row.querySelector('.fti-admin-preview-icon');
            if (!picker || !previewContainer || !type) return;

            const color = picker.value;
            if (window.ftiAdmin && window.ftiAdmin.templates && window.ftiAdmin.templates[styleVal]) {
                const template = window.ftiAdmin.templates[styleVal][type];
                if (template) {
                    previewContainer.innerHTML = getSvgWithColor(template, styleVal, color);
                }
            }
        });
    };

    // 4. File Type Row States (Toggle Switches)
    const checkboxes = document.querySelectorAll('.fti-type-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const row = cb.closest('.ftrow');
            if (row) {
                row.classList.toggle('off', !cb.checked);
            }
        });
    });

    // 5. Color Pickers and HEX Codes
    const colorPickers = document.querySelectorAll('.fti-color-picker');
    colorPickers.forEach(picker => {
        picker.addEventListener('input', (e) => {
            const target = e.target;
            const value = target.value;
            const row = target.closest('.ftrow');
            if (!row) return;

            const type = row.getAttribute('data-type');
            
            // Updates the displayed HEX value
            const hexEl = row.querySelector(`.chex[data-type="${type}"]`);
            if (hexEl) {
                hexEl.textContent = value.toUpperCase();
            }

            // Updates the dot color
            const dotEl = row.querySelector(`.cdot[data-type="${type}"]`);
            if (dotEl) {
                dotEl.style.backgroundColor = value;
            }

            // Updates the SVG preview icon
            const styleVal = styleInput ? styleInput.value : '1';
            const previewContainer = row.querySelector('.fti-admin-preview-icon');
            if (previewContainer && window.ftiAdmin && window.ftiAdmin.templates && window.ftiAdmin.templates[styleVal]) {
                const template = window.ftiAdmin.templates[styleVal][type];
                if (template) {
                    previewContainer.innerHTML = getSvgWithColor(template, styleVal, value);
                }
            }

            // If this is the PDF color picker, update the general settings preview
            if (target.name === 'fti_icon_colors[pdf]') {
                updatePreviewSvg();
            }
        });
    });

    // 6. Global Actions (Toolbar)
    const checkAllBtn = document.getElementById('fti-check-all');
    const uncheckAllBtn = document.getElementById('fti-uncheck-all');
    const resetColorsBtn = document.getElementById('fti-reset-colors');

    checkAllBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        checkboxes.forEach(cb => {
            cb.checked = true;
            cb.dispatchEvent(new Event('change'));
        });
    });

    uncheckAllBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.dispatchEvent(new Event('change'));
        });
    });

    resetColorsBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        colorPickers.forEach(picker => {
            const defaultColor = picker.getAttribute('data-default');
            if (defaultColor) {
                picker.value = defaultColor;
                picker.dispatchEvent(new Event('input'));
            }
        });
    });

    // 7. Saved Status Indicator
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('settings-updated') === 'true') {
        const msgEl = document.getElementById('svmsg');
        if (msgEl) {
            msgEl.textContent = (window.ftiAdmin && window.ftiAdmin.savedMsg) ? window.ftiAdmin.savedMsg : "Changes saved successfully";
            msgEl.style.opacity = '1';
            setTimeout(() => {
                msgEl.style.transition = 'opacity 1s ease';
                msgEl.style.opacity = '0';
                setTimeout(() => {
                    msgEl.textContent = "";
                    msgEl.style.transition = '';
                }, 1000);
            }, 4000);
        }
    }

    // Initialize Preview States on Load
    if (sizeNumber) updatePreviewSize(sizeNumber.value);
    if (positionInput) updatePreviewPosition(positionInput.value);
    updatePreviewSvg();
});
