
$(function ($) {
    const MAX_FLOWERS = 24;
    let currentStep = 1;
    let placedFlowers = []; // [{ id, flowerId, src, x, y, rotation, scale }]
    let flowerIdCounter = 0;
    let activeFlowerId = null;

    const $canvas = $('#flowerCanvas');
    const $counter = $('#flowerCounter');

    // -------------------------------------------------------------
    // STEP NAVIGATION
    // -------------------------------------------------------------
    $('#btnNextStep').on('click', function () {
        if (currentStep === 1) {
            if (placedFlowers.length === 0) {
                alert('Будь ласка, додайте хоча б одну квітку!');
                return;
            }
            goToStep(2);
        } else if (currentStep === 2) {
            goToStep(3);
        }
    });

    $('#btnPrevStep').on('click', function () {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });

    function goToStep(step) {
        currentStep = step;

        // Перемикання видимого контенту кроків
        $('.wizard-step-content').addClass('d-none');
        $(`#wizardStep${step}`).removeClass('d-none');

        // Оновлення стилів графічного степера
        updateStepperUI(step);

        // Керування кнопками дій
        $('#btnPrevStep').toggleClass('d-none', step === 1);
        $('#btnNextStep').toggleClass('d-none', step === 3);
        $('#btnSaveBouquet').toggleClass('d-none', step !== 3);

        if (step === 2) renderStep2Preview();
        if (step === 3) renderStep3ScaledBouquet();
    }

    function updateStepperUI(step) {
        // 1. Оновлюємо довжину з'єднувальної лінії (0%, 50%, 100%)
        const progressWidths = { 1: '0%', 2: '50%', 3: '100%' };
        $('#stepperProgressBar').css('width', progressWidths[step]);

        // 2. Оновлюємо стан кружечків та підписів
        for (let i = 1; i <= 3; i++) {
            const $node = $(`#stepNode${i}`);
            const $circle = $node.find('.step-circle');
            const $label = $node.find('.step-label');

            if (i === step) {
                // АКТИВНИЙ КРОК (Синій)
                $circle.removeClass('bg-light bg-success border text-muted')
                    .addClass('bg-primary text-white shadow-sm');
                $label.removeClass('text-muted text-success fw-normal')
                    .addClass('fw-bold text-primary');
            } else if (i < step) {
                // пройдений крок (Зелений з гачком або цифрою)
                $circle.removeClass('bg-light bg-primary border text-muted')
                    .addClass('bg-success text-white');
                $label.removeClass('text-muted text-primary fw-bold')
                    .addClass('fw-semibold text-success');
            } else {
                // МАЙБУТНІЙ КРОК (Сірий)
                $circle.removeClass('bg-primary bg-success text-white shadow-sm')
                    .addClass('bg-light border border-2 text-muted');
                $label.removeClass('fw-bold fw-semibold text-primary text-success')
                    .addClass('text-muted fw-normal');
            }
        }
    }

// Клік по самих кружечках для швидкого переходу (якщо попередні кроки заповнені)
    $('.step-item').on('click', function () {
        const targetStep = $(this).data('step');
        if (targetStep < currentStep) {
            goToStep(targetStep);
        } else if (targetStep === 2 && currentStep === 1 && placedFlowers.length > 0) {
            goToStep(2);
        } else if (targetStep === 3 && placedFlowers.length > 0) {
            goToStep(3);
        }
    });

    // -------------------------------------------------------------
    // STEP 1: ADD / MOVE / ROTATE / DELETE FLOWERS
    // -------------------------------------------------------------
    $('.palette-flower').on('click', function () {
        if (placedFlowers.length >= MAX_FLOWERS) {
            alert(`Максимальна кількість квіток: ${MAX_FLOWERS}`);
            return;
        }

        const src = $(this).attr('src');
        const typeId = $(this).data('id');
        addFlowerToCanvas(typeId, src, 150, 150);
    });

    function addFlowerToCanvas(typeId, src, x, y) {
        $('.canvas-placeholder').addClass('d-none');
        flowerIdCounter++;
        const uniqueId = `flower_${flowerIdCounter}`;

        const flowerData = {
            id: uniqueId,
            typeId: typeId,
            src: src,
            x: x,
            y: y,
            rotation: 0,
            scale: 1.0
        };

        placedFlowers.push(flowerData);
        updateCounter();

        const $flowerEl = $(`
            <div class="placed-flower position-absolute cursor-move" id="${uniqueId}" style="left: ${x}px; top: ${y}px; transform: rotate(0deg); touch-action: none;">
                <img src="${src}" style="width: 80px; height: auto; pointer-events: none;" alt="flower">
                <div class="flower-controls d-none">
                    <button type="button" class="btn btn-danger btn-sm btn-delete position-absolute top-0 start-100 translate-middle p-0 rounded-circle" style="width:20px;height:20px;line-height:1;">&times;</button>
                    <div class="rotate-handle position-absolute top-100 start-50 translate-middle-x bg-primary rounded-circle cursor-grab" style="width:12px;height:12px;margin-top:5px;"></div>
                </div>
            </div>
        `);

        $canvas.append($flowerEl);
        attachFlowerEvents($flowerEl, flowerData);
    }

    function attachFlowerEvents($el, item) {
        // Selection
        $el.on('pointerdown', function (e) {
            e.stopPropagation();
            $('.placed-flower').removeClass('border border-primary').find('.flower-controls').addClass('d-none');
            $el.addClass('border border-primary').find('.flower-controls').removeClass('d-none');
            activeFlowerId = item.id;

            // Dragging
            let startX = e.clientX - item.x;
            let startY = e.clientY - item.y;

            function onMove(ev) {
                item.x = ev.clientX - startX;
                item.y = ev.clientY - startY;
                $el.css({ left: item.x + 'px', top: item.y + 'px' });
            }

            function onUp() {
                $(document).off('pointermove', onMove);
                $(document).off('pointerup', onUp);
            }

            $(document).on('pointermove', onMove);
            $(document).on('pointerup', onUp);
        });

        // Deletion
        $el.find('.btn-delete').on('click', function (e) {
            e.stopPropagation();
            $el.remove();
            placedFlowers = placedFlowers.filter(f => f.id !== item.id);
            updateCounter();
            if (placedFlowers.length === 0) $('.canvas-placeholder').removeClass('d-none');
        });

        // Rotation
        $el.find('.rotate-handle').on('pointerdown', function (e) {
            e.stopPropagation();
            const rect = $el[0].getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            function onRotate(ev) {
                const rad = Math.atan2(ev.clientY - centerY, ev.clientX - centerX);
                let deg = Math.round(rad * (180 / Math.PI)) - 90;
                item.rotation = deg;
                $el.css('transform', `rotate(${deg}deg)`);
            }

            function onRotateEnd() {
                $(document).off('pointermove', onRotate);
                $(document).off('pointerup', onRotateEnd);
            }

            $(document).on('pointermove', onRotate);
            $(document).on('pointerup', onRotateEnd);
        });
    }

    function updateCounter() {
        $counter.text(`${placedFlowers.length} / ${MAX_FLOWERS}`);
    }

    // Deselect flower when clicking canvas background
    $canvas.on('click', function () {
        $('.placed-flower').removeClass('border border-primary').find('.flower-controls').addClass('d-none');
        activeFlowerId = null;
    });

    // -------------------------------------------------------------
    // STEP 2: RIBBON TEXT
    // -------------------------------------------------------------
    function renderStep2Preview() {
        const $container = $('#step2CanvasContainer').empty();
        const $clonedCanvas = $canvas.clone().attr('id', 'step2CanvasMirror').css({ height: '100%', width: '100%' });
        $clonedCanvas.find('.flower-controls, .canvas-placeholder').remove();
        $clonedCanvas.find('.placed-flower').removeClass('border border-primary');

        // Add Ribbon overlay
        const ribbonText = $('#ribbonTextInput').val() || '';
        const ribbonColor = $('#ribbonColorSelect').val() || 'black';

        const $ribbon = $(`
            <div id="bouquetRibbonOverlay" class="position-absolute bottom-0 start-50 translate-middle-x mb-4 px-4 py-1 text-center font-serif text-white rounded shadow-sm bg-${ribbonColor}" style="min-width: 200px; max-width: 80%; z-index: 20;">
                <span id="ribbonTextSpan">${escapeHtml(ribbonText)}</span>
            </div>
        `);

        $container.append($clonedCanvas).append($ribbon);
    }

    $('#ribbonTextInput').on('input', function () {
        $('#ribbonTextSpan').text($(this).val());
    });

    $('#ribbonColorSelect').on('change', function () {
        const color = $(this).val();
        $('#bouquetRibbonOverlay').removeClass('bg-black bg-gold bg-white text-dark text-white')
            .addClass(`bg-${color}`)
            .addClass(color === 'white' ? 'text-dark' : 'text-white');
    });

    // -------------------------------------------------------------
    // STEP 3: 256x256 SCALED BOUQUET POSITIONING & SAVE
    // -------------------------------------------------------------
    let bouquetPos = { x: 0, y: 0 };

    function renderStep3ScaledBouquet() {
        const $inner = $('#scaledBouquetInner').empty();
        const originalWidth = $canvas.width();
        const originalHeight = $canvas.height();

        // Scale ratio to fit Step 1 canvas into 256x256
        const scaleX = 256 / originalWidth;
        const scaleY = 256 / originalHeight;

        // Render scaled down composite
        placedFlowers.forEach(f => {
            const scaledX = f.x * scaleX;
            const scaledY = f.y * scaleY;
            const $img = $(`
                <img src="${f.src}" class="position-absolute" style="left:${scaledX}px; top:${scaledY}px; width:${80 * scaleX}px; transform:rotate(${f.rotation}deg); pointer-events:none;">
            `);
            $inner.append($img);
        });

        // Add Ribbon to 256x256 preview
        const ribbonText = $('#ribbonTextInput').val() || '';
        if (ribbonText) {
            $inner.append(`
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 px-2 py-0 text-center text-white bg-dark rounded" style="font-size: 10px; max-width: 90%;">
                    ${escapeHtml(ribbonText)}
                </div>
            `);
        }

        // Enable dragging of 256x256 wrapper inside photo area
        const $wrapper = $('#scaledBouquetWrapper');
        $wrapper.on('pointerdown', function (e) {
            e.preventDefault();
            const parentOffset = $('#memorialPhotoArea').offset();

            function onMove(ev) {
                let left = ev.pageX - parentOffset.left - 128; // Center offset
                let top = ev.pageY - parentOffset.top - 128;

                // Restrict to container
                left = Math.max(0, Math.min(left, $('#memorialPhotoArea').width() - 256));
                top = Math.max(0, Math.min(top, $('#memorialPhotoArea').height() - 256));

                bouquetPos = { x: Math.round(left), y: Math.round(top) };
                $wrapper.css({ left: `${left}px`, top: `${top}px`, transform: 'none' });
            }

            function onUp() {
                $(document).off('pointermove', onMove);
                $(document).off('pointerup', onUp);
            }

            $(document).on('pointermove', onMove);
            $(document).on('pointerup', onUp);
        });
    }

    // -------------------------------------------------------------
    // SAVE ACTION (AJAX POST JSON)
    // -------------------------------------------------------------
    $('#btnSaveBouquet').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Збереження...');

        // Build JSON Payload
        const payload = {
            graveId: $('#graveIdInput').val() || null,
            ribbon: {
                text: $('#ribbonTextInput').val()?.trim() || '',
                color: $('#ribbonColorSelect').val() || 'black'
            },
            bouquetPosition: {
                x: bouquetPos.x,
                y: bouquetPos.y,
                containerWidth: $('#memorialPhotoArea').width(),
                containerHeight: $('#memorialPhotoArea').height()
            },
            flowers: placedFlowers.map(f => ({
                typeId: f.typeId,
                x: Math.round(f.x),
                y: Math.round(f.y),
                rotation: f.rotation
            }))
        };

        $.ajax({
            url: '/necropolis/bouquet/save', // Replace with your endpoint
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (response) {
                alert('Ваш букет успішно покладено!');
                window.location.reload();
            },
            error: function () {
                alert('Помилка при збереженні. Спробуйте ще раз.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Зберегти');
            }
        });
    });

    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }
});