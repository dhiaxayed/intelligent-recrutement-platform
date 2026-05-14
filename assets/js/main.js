/*
Person 4: Safa Khedhawria will contribute here.
Responsibility: native JavaScript enhancements, validation, UI interactions.
Existing code includes Person 2 profile validation and apply confirmation.
*/

(function () {
    // Keep frontend behavior native and dependency-free for this project.
    function isHttpUrl(value) {
        try {
            var url = new URL(value);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (error) {
            return false;
        }
    }

    function renderErrors(container, errors) {
        // Render validation messages in one place so the form markup stays clean.
        if (!container) {
            return;
        }

        if (!errors.length) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = '<ul>' + errors.map(function (error) {
            return '<li>' + error + '</li>';
        }).join('') + '</ul>';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Candidate profile validation mirrors the PHP checks for faster feedback.
        // The server still validates everything because JavaScript can be disabled or bypassed.
        var profileForm = document.querySelector('[data-profile-form]');

        if (profileForm) {
            profileForm.addEventListener('submit', function (event) {
                var errors = [];
                var phone = document.getElementById('phone');
                var linkedin = document.getElementById('linkedin');
                var github = document.getElementById('github');
                var cv = document.getElementById('cv');
                var errorBox = profileForm.querySelector('[data-client-errors]');
                var hasCv = profileForm.getAttribute('data-has-cv') === '1';

                // Required fields for a valid candidate profile.
                if (!phone || phone.value.trim() === '') {
                    errors.push('Phone is required.');
                }

                if (!linkedin || linkedin.value.trim() === '') {
                    errors.push('LinkedIn URL is required.');
                } else if (!isHttpUrl(linkedin.value.trim())) {
                    errors.push('Enter a valid LinkedIn URL starting with http:// or https://.');
                }

                if (github && github.value.trim() !== '' && !isHttpUrl(github.value.trim())) {
                    errors.push('Enter a valid GitHub URL starting with http:// or https://.');
                }

                if (cv && cv.files.length > 0) {
                    var file = cv.files[0];
                    var fileName = file.name.toLowerCase();
                    var isPdfExtension = fileName.endsWith('.pdf');
                    var isPdfMime = file.type === '' || file.type === 'application/pdf';

                    if (!isPdfExtension || !isPdfMime) {
                        errors.push('CV must be a PDF file.');
                    }
                } else if (!hasCv) {
                    // A CV is required only when the candidate does not already have one.
                    errors.push('Upload your CV as a PDF.');
                }

                renderErrors(errorBox, errors);

                if (errors.length) {
                    event.preventDefault();
                }
            });
        }

        // Applying creates a database row, so ask the candidate to confirm before submitting.
        document.querySelectorAll('[data-apply-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm('Are you sure you want to apply for this job?')) {
                    event.preventDefault();
                }
            });
        });
    });
})();
/* =====================================================
   Person 4 – Safa Khedhawria – Additions
   Recruiter confirmations + alert auto-hide + CV label
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ── Accept / Reject confirmation ──────────────────────────────────────
    // data-action="accept" ou "reject", data-name="Yasmine Ben Ali"
    document.querySelectorAll('[data-decision-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var action = form.getAttribute('data-action');   // 'accept' | 'reject'
            var name   = form.getAttribute('data-name') || 'ce candidat';
            var msg    = action === 'accept'
                ? 'Accepter ' + name + ' et envoyer l\'email d\'invitation ?'
                : 'Rejeter la candidature de ' + name + ' ?';

            if (!window.confirm(msg)) {
                event.preventDefault();
            }
        });
    });

    // ── Auto-hide flash messages after 5 s ───────────────────────────────
    document.querySelectorAll('.message').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.6s';
            el.style.opacity    = '0';
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 650);
        }, 5000);
    });

    // ── CV file input – show chosen filename ─────────────────────────────
    var cvInput = document.getElementById('cv');
    var cvLabel = document.getElementById('cv-filename');
    if (cvInput && cvLabel) {
        cvInput.addEventListener('change', function () {
            cvLabel.textContent = this.files.length
                ? '📎 ' + this.files[0].name
                : '';
        });
    }

    // ── Job description character counter ────────────────────────────────
    var descField   = document.getElementById('description');
    var descCounter = document.getElementById('desc-counter');
    if (descField && descCounter) {
        function updateCounter() {
            descCounter.textContent = descField.value.length + ' caractères';
        }
        descField.addEventListener('input', updateCounter);
        updateCounter();
    }

});