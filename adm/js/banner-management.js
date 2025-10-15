document.addEventListener('DOMContentLoaded', function () {
    var editBannerModal = document.getElementById('editBannerModal');
    if (editBannerModal) {
        editBannerModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }

            var bannerId = button.getAttribute('data-id');
            var bannerTitle = button.getAttribute('data-title') || '';
            var bannerDescription = button.getAttribute('data-description') || '';
            var bannerLink = button.getAttribute('data-link') || '';
            var bannerStatus = button.getAttribute('data-status') === '1';
            var bannerImage = button.getAttribute('data-image') || '';

            var idField = document.getElementById('editBannerId');
            var titleField = document.getElementById('editBannerTitle');
            var descriptionField = document.getElementById('editBannerDescription');
            var linkField = document.getElementById('editBannerLink');
            var statusField = document.getElementById('editBannerStatus');
            var previewImage = document.getElementById('editBannerPreview');
            var previewFallback = document.getElementById('editBannerPreviewFallback');

            if (idField) {
                idField.value = bannerId || '';
            }
            if (titleField) {
                titleField.value = bannerTitle;
            }
            if (descriptionField) {
                descriptionField.value = bannerDescription;
            }
            if (linkField) {
                linkField.value = bannerLink;
            }
            if (statusField) {
                statusField.checked = bannerStatus;
            }

            if (previewImage) {
                if (bannerImage) {
                    previewImage.src = '../images/banner/' + bannerImage;
                    previewImage.classList.remove('d-none');
                    if (previewFallback) {
                        previewFallback.classList.add('d-none');
                    }
                } else {
                    previewImage.src = '';
                    previewImage.classList.add('d-none');
                    if (previewFallback) {
                        previewFallback.classList.remove('d-none');
                    }
                }
            }
        });

        editBannerModal.addEventListener('hidden.bs.modal', function () {
            var form = editBannerModal.querySelector('form');
            if (form) {
                form.reset();
            }
            var previewImage = document.getElementById('editBannerPreview');
            var previewFallback = document.getElementById('editBannerPreviewFallback');
            if (previewImage) {
                previewImage.src = '';
                previewImage.classList.add('d-none');
            }
            if (previewFallback) {
                previewFallback.classList.remove('d-none');
            }
        });
    }

    var deleteBannerModal = document.getElementById('deleteBannerModal');
    if (deleteBannerModal) {
        deleteBannerModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }

            var bannerId = button.getAttribute('data-id');
            var bannerTitle = button.getAttribute('data-title') || '';

            var idField = document.getElementById('deleteBannerId');
            var titleHolder = document.getElementById('deleteBannerTitle');

            if (idField) {
                idField.value = bannerId || '';
            }
            if (titleHolder) {
                titleHolder.textContent = bannerTitle;
            }
        });

        deleteBannerModal.addEventListener('hidden.bs.modal', function () {
            var form = deleteBannerModal.querySelector('form');
            if (form) {
                form.reset();
            }
            var titleHolder = document.getElementById('deleteBannerTitle');
            if (titleHolder) {
                titleHolder.textContent = '';
            }
        });
    }
});
