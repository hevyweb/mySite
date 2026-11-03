require('@fortawesome/fontawesome-free/js/all.js');
const bootstrap = require('bootstrap');

$(function($){
    $('#condolence-btn').click(function(){
        $('.content-panel').children().each(function(){$(this).hide();
        })
        $('#condolences').show();
    });

    $('#btnCloseCondolences').click(function(){
        $('.content-panel').children().each(function(){$(this).hide();
        })
        $('#biography').show();
    });

    const $spinner =$('#qr-spinner');
    const $qrContainer =$('#qr-code-container');
    const $qrImg =$('#qr-code-img');
    const $errorMessage =$('#qr-error-message');

    // 1. QR-код: створюємо інстанс модалки лише в момент кліку
    $('.js-qr-btn').click(function (e) {
        e.preventDefault();

        const url = $(this).attr('href');

        $qrContainer.addClass('d-none');$errorMessage.addClass('d-none');
        $spinner.removeClass('d-none');$qrImg.attr('src', '');

        const modalEl = document.getElementById('qrCodeModal');
        if (modalEl) {
            const qrModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            qrModal.show();
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.qrcode) {
                    $qrImg.attr('src', 'data:image/png;base64,' + response.qrcode);$qrContainer.removeClass('d-none');
                } else {
                    $errorMessage.text('Некоректний формат відповіді сервера.').removeClass('d-none');
                }
            },
            error: function (xhr, status, error) {
                $errorMessage.text('Помилка під час завантаження QR-коду (' + xhr.status + ').').removeClass('d-none');
            },
            complete: function () {
                $spinner.addClass('d-none');
            }
        });
    });

    const $shareButton =$('#shareButton');

    $shareButton.on('click', async function() {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: document.title,
                    text: 'Перегляньте цю сторінку!',
                    url: window.location.href,
                });
                console.log('Поділились успішно.');
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Помилка:', err);
                }
            }
        } else {
            copyToClipboard();
        }
    });

    function copyToClipboard() {
        navigator.clipboard.writeText(window.location.href);
        alert('Посилання скопійовано до буфера обміну!');
    }

    const $modalEl =$('#condolenceModal');
    if ($modalEl.length) {
        // Задаємо опції модалки через атрибути, щоб уникнути помилок ініціалізації конструктора
        $modalEl.attr('data-bs-backdrop', 'static');$modalEl.attr('data-bs-keyboard', 'true');

        $(document).on('click', '[data-bs-target="#condolenceModal"], #btnOpenCondolenceModal', function (e) {
            e.preventDefault();
            const condolenceModal = bootstrap.Modal.getOrCreateInstance($modalEl[0]);
            condolenceModal.show();
        });

        $modalEl.on('shown.bs.modal', function () {$(this).find('textarea, input').first().trigger('focus');
        });

        $modalEl.on('hidden.bs.modal', function () {
            const $form =$('#condolenceForm');
            clearFormErrors($form);
            if ($form.length)$form[0].reset();
        });
    }

    // AJAX відправка форми
    $(document).on('submit', '#condolenceForm', function (e) {
        e.preventDefault();

        const $form =$(this);
        const $submitBtn =$('#btnSubmitCondolence');
        const originalBtnHtml = $submitBtn.html();

        clearFormErrors($form);$submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Надсилання...');

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: $form.serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (response) {
                if (response.success) {
                    const rawName = $form.find('[name*="[name]"]').val()?.trim();
                    const name = rawName ? escapeHtml(rawName) : 'Анонім';
                    const rawDescription = $form.find('[name*="[description]"]').val()?.trim() || '';
                    const description = escapeHtml(rawDescription).replace(/\n/g, '<br>');

                    const now = new Date();
                    const formattedDate = now.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
                        now.toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });

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

                    let $container =$('#condolencesList');
                    if (!$container.length) {
                        $container =$('#condolences').find('ul.list-group');
                    }

                    if (!$container.length) {
                        const $body =$('#condolences .content-panel-body');
                        $body.html('<ul class="list-group list-group-flush" id="condolencesList"></ul>');
                        $container =$('#condolencesList');
                    }

                    $container.prepend(tempItemHtml);

                    const condolenceModal = bootstrap.Modal.getInstance($modalEl[0]);
                    if (condolenceModal) condolenceModal.hide();
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
        $form.find('.is-invalid').removeClass('is-invalid');$form.find('.invalid-feedback').remove();
    }

    function displayFormErrors($form, errors) {$.each(errors, function (fieldName, messages) {
        let $input =$form.find(`[name*="[${fieldName}]"]`);
        if (!$input.length) $input =$form.find(`#${fieldName}`);

        if ($input.length) {
            $input.addClass('is-invalid');$input.after(`<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`);
        }
    });
    }

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

    // 3. Галерея фото: ініціалізація інстансу всередині успішного AJAX-запиту
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

                const $strip =$('#thumbnailsStrip').empty();
                let currentIndex = 0;

                function updateMainView(index) {
                    currentIndex = index;
                    const photo = data[index];
                    const fullImgUrl = `/necropolis/${photo.filename}`;

                    $('#mainPhoto').attr('src', fullImgUrl).data('full-url', fullImgUrl);

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

                    $('#photoCounter').text(`Фото ${index + 1} з ${data.length}`);

                    $('.thumb-item').removeClass('active');
                    const $activeThumb =$(`.thumb-item[data-index="${index}"]`).addClass('active');

                    if ($activeThumb.length) {
                        const strip = $strip[0];
                        const thumb = $activeThumb[0];
                        const scrollLeft = thumb.offsetLeft - (strip.clientWidth / 2) + (thumb.clientWidth / 2);
                        strip.scrollTo({ left: scrollLeft, behavior: 'smooth' });
                    }
                }

                data.forEach(function (photo, idx) {
                    const thumbUrl = `/necropolis/thumbnails/${photo.filename}`;
                    const $thumb =$(`
            <div class="thumb-item" data-index="${idx}">
              <img src="${thumbUrl}" alt="Thumbnail ${idx + 1}" loading="lazy" />
            </div>
          `);

                    $thumb.on('click', function () {
                        updateMainView(idx);
                    });

                    $strip.append($thumb);
                });

                updateMainView(0);

                const photoModalEl = document.getElementById('photoGalleryModal');
                if (photoModalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(photoModalEl);
                    modal.show();
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Failed to load photo gallery.');
            }
        });
    });

    $(document).on('dblclick', '#mainPhotoWrapper', function () {
        const fullUrl = $('#mainPhoto').data('full-url');
        if (fullUrl) {
            window.open(fullUrl, '_blank');
        }
    });
});