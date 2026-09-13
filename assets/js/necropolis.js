require('@fortawesome/fontawesome-free/js/all.js');
const bootstrap = require('bootstrap');

$(function($){
    $('#condolence-btn').click(function(){
        $('.content-panel').children().each(function(){
            $(this).hide();
        })
        $('#condolences').show();
    });
    
    $('#btnCloseCondolences').click(function(){
        $('.content-panel').children().each(function(){
            $(this).hide();
        })
        $('#biography').show();
    });
    
    const $shareButton = $('#shareButton');

    $shareButton.on('click', async function() {
        // Check if the Web Share API is supported
        if (navigator.share) {
            try {
                await navigator.share({
                    title: document.title,
                    text: 'Перегляньте цю сторінку!',
                    url: window.location.href,
                });
                console.log('Поділились успішно.');
            } catch (err) {
                // Handle user cancellation or error
                if (err.name !== 'AbortError') {
                    console.error('Помилка:', err);
                }
            }
        } else {
            // Fallback for browsers that don't support native sharing
            copyToClipboard();
        }
    });

    // Simple clipboard fallback
    function copyToClipboard() {
        navigator.clipboard.writeText(window.location.href);
        alert('Посилання скопійовано до буфера обміну!');
    }
    
    const $modalEl = $('#condolenceModal');
    if (!$modalEl.length) return;

    const condolenceModal = new bootstrap.Modal($modalEl[0], {
        backdrop: 'static',
        keyboard: true
    });

    $(document).on('click', '[data-bs-target="#condolenceModal"], #btnOpenCondolenceModal', function (e) {
        e.preventDefault();
        condolenceModal.show();
    });

    $modalEl.on('shown.bs.modal', function () {
        $(this).find('textarea, input').first().trigger('focus');
    });

    $modalEl.on('hidden.bs.modal', function () {
        const $form = $('#condolenceForm');
        clearFormErrors($form);
        if ($form.length) $form[0].reset();
    });

    // AJAX відправка форми
    $(document).on('submit', '#condolenceForm', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#btnSubmitCondolence');
        const originalBtnHtml = $submitBtn.html();

        clearFormErrors($form);
        $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Надсилання...');

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: $form.serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (response) {
                if (response.success) {
                    // 1. Отримуємо значення з форми до її очищення
                    const rawName = $form.find('[name*="[name]"]').val()?.trim();
                    const name = rawName ? escapeHtml(rawName) : 'Анонім';
                    const rawDescription = $form.find('[name*="[description]"]').val()?.trim() || '';
                    const description = escapeHtml(rawDescription).replace(/\n/g, '<br>');

                    // 2. Формуємо дату та час
                    const now = new Date();
                    const formattedDate = now.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
                        now.toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });

                    // 3. Шаблон тимчасового блоку з червоним бейджем "На модерації"
                    const tempItemHtml = `
                    <li class="list-group-item bg-light border border-warning-subtle rounded my-2 p-3 opacity-75">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">${name}</span>
                            <span class="badge bg-danger">На модерації</span>
                        </div>
                        <div class="text-break">${description}</div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fa-regular fa-clock me-1"></i>${formattedDate}
                        </small>
                    </li>
                `;

                    // 4. Шукаємо контейнер для списку співчуттів
                    let $container = $('#condolencesList');
                    if (!$container.length) {
                        $container = $('#condolences').find('ul.list-group');
                    }

                    // Якщо списку ще немає (було "Співчуття відсутні"), створюємо новий
                    if (!$container.length) {
                        const $body = $('#condolences .content-panel-body');
                        $body.html('<ul class="list-group list-group-flush" id="condolencesList"></ul>');
                        $container = $('#condolencesList');
                    }

                    // 5. Додаємо елемент на початок списку
                    $container.prepend(tempItemHtml);

                    // 6. Закриваємо модалку та скидаємо форму
                    condolenceModal.hide();
                    $form[0].reset();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayFormErrors($form, xhr.responseJSON.errors);
                } else {
                    alert('Сталася помилка при відправці. Спробуйте ще раз.');
                }
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    function clearFormErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    }

    function displayFormErrors($form, errors) {
        $.each(errors, function (fieldName, messages) {
            let $input = $form.find(`[name*="[${fieldName}]"]`);
            if (!$input.length) $input = $form.find(`#${fieldName}`);

            if ($input.length) {
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`);
            }
        });
    }

    // Хелпер для захисту від XSS при динамічному додаванні тексту
    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // 3. Click Handler for #photos
    $('#photos').on('click', function (e) {
        e.preventDefault();

        const targetUrl = window.location.href.replace(/\/$/, '') + '/photos';

        $.ajax({
            url: targetUrl,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (!data || data.length === 0) {
                    alert('No photos available.');
                    return;
                }

                const $strip = $('#thumbnailsStrip').empty();
                let currentIndex = 0;

                // Function to update the main view
                function updateMainView(index) {
                    currentIndex = index;
                    const photo = data[index];
                    const fullImgUrl = `/necropolis/${photo.filename}`;

                    // Set Main Image
                    $('#mainPhoto').attr('src', fullImgUrl).data('full-url', fullImgUrl);

                    // Set Caption Info
                    const formattedDate = formatDate(photo.shootingDate);
                    if (photo.description) {
                        $('#captionDesc').text(photo.description).show();
                    } else {
                        $('#captionDesc').hide();
                    }

                    if (formattedDate) {
                        $('#captionDate').text(formattedDate).show();
                    } else {
                        $('#captionDate').hide();
                    }

                    // Counter header
                    $('#photoCounter').text(`Фото ${index + 1} з ${data.length}`);

                    // Active Thumbnail styling
                    $('.thumb-item').removeClass('active');
                    const $activeThumb = $(`.thumb-item[data-index="${index}"]`).addClass('active');

                    // Auto-scroll thumbnails strip to keep active thumbnail visible
                    if ($activeThumb.length) {
                        const strip = $strip[0];
                        const thumb = $activeThumb[0];
                        const scrollLeft = thumb.offsetLeft - (strip.clientWidth / 2) + (thumb.clientWidth / 2);
                        strip.scrollTo({ left: scrollLeft, behavior: 'smooth' });
                    }
                }

                // Build Thumbnails
                data.forEach(function (photo, idx) {
                    const thumbUrl = `/necropolis/thumbnails/${photo.filename}`;
                    const $thumb = $(`
            <div class="thumb-item" data-index="${idx}">
              <img src="${thumbUrl}" alt="Thumbnail ${idx + 1}" loading="lazy" />
            </div>
          `);

                    $thumb.on('click', function () {
                        updateMainView(idx);
                    });

                    $strip.append($thumb);
                });

                // Initialize with first photo
                updateMainView(0);

                // Show Modal
                const modal = new bootstrap.Modal(document.getElementById('photoGalleryModal'));
                modal.show();
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Failed to load photo gallery.');
            }
        });
    });

    // 4. Double click main photo to open original photo in full screen / new tab
    $(document).on('dblclick', '#mainPhotoWrapper', function () {
        const fullUrl = $('#mainPhoto').data('full-url');
        if (fullUrl) {
            window.open(fullUrl, '_blank');
        }
    });
});