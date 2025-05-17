<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système Hospitalier</title>
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #f1f3f4;
            --text-color: #202124;
            --error-color: #d93025;
            --success-color: #0f9d58;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: block;
            width: 100%;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #0d62c9;
        }

        .error-message {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .success-message {
            background-color: rgba(15, 157, 88, 0.1);
            color: var(--success-color);
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            display: none;
        }

        .invalid {
            border-color: var(--error-color) !important;
        }

        .file-input-container {
            position: relative;
        }

        .file-input-label {
            display: block;
            background-color: var(--secondary-color);
            color: var(--text-color);
            text-align: center;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            border: 1px dashed #ccc;
        }

        .file-input-label:hover {
            background-color: #e8eaed;
        }

        #file-name {
            margin-top: 5px;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Système Hospitalier</h1>
        <form id="hospital-form">
            <div class="form-group">
                <label for="hospital">Choisissez un hôpital :</label>
                <select id="hospital" name="hospital" required>
                    <option value="" disabled selected>Sélectionner un hôpital</option>
                    <option value="hopital-saint-antoine">Hôpital Saint-Antoine</option>
                    <option value="hopital-bichat">Hôpital Bichat</option>
                    <option value="hopital-cochin">Hôpital Cochin</option>
                    <option value="hopital-europeen-georges-pompidou">Hôpital Européen Georges-Pompidou</option>
                    <option value="hopital-necker">Hôpital Necker</option>
                    <option value="hopital-pitie-salpetriere">Hôpital Pitié-Salpêtrière</option>
                    <option value="hopital-robert-debre">Hôpital Robert-Debré</option>
                    <option value="hopital-tenon">Hôpital Tenon</option>
                </select>
                <p class="error-message" id="hospital-error">Veuillez sélectionner un hôpital</p>
            </div>

            <div class="form-group">
               <label for="numero_securite_sociale">Numéro de Sécurité Sociale:</label>
                <input type="text" id="numero_securite_sociale" name="numero_securite_sociale" required>
                
            </div>

            <div class="form-group">
                <label for="appointment-date">Date du rendez-vous :</label>
                <input type="date" id="appointment-date" name="appointment-date" required>
                <p class="error-message" id="date-error">Veuillez sélectionner une date</p>
            </div>

                <label for="type_document">Type de Document:</label>
                <select id="type_document" name="type_document" required>
                    <option value="">-- Select Type --</option>
                    <option value="analyse_sang">Analyse de sang</option>
                    <option value="radiographie">Radiographie</option>
                    <option value="echographie">Échographie</option>
                    <option value="consultation">Consultation</option>
                    <option value="ordonnance">Ordonnance</option>
                </select>

            <button type="submit" class="btn" id="submit-btn">Soumettre</button>
        </form>

        <div class="success-message" id="success-message">
            Votre formulaire a été soumis avec succès ! Nous vous contacterons bientôt.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('hospital-form');
            const hospitalSelect = document.getElementById('hospital');
            const socialSecurityInput = document.getElementById('social-security');
            const dateInput = document.getElementById('appointment-date');
            const fileInput = document.getElementById('file-upload');
            const fileName = document.getElementById('file-name');
            const successMessage = document.getElementById('success-message');

            // Set minimum date to today
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();
            dateInput.min = `${yyyy}-${mm}-${dd}`;

            // Format SSN
            socialSecurityInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                let formatted = '';
                if (value.length >= 1) formatted += value.substring(0, 1) + ' ';
                if (value.length >= 3) formatted += value.substring(1, 3) + ' ';
                if (value.length >= 5) formatted += value.substring(3, 5) + ' ';
                if (value.length >= 7) formatted += value.substring(5, 7) + ' ';
                if (value.length >= 10) formatted += value.substring(7, 10) + ' ';
                if (value.length >= 13) formatted += value.substring(10, 13) + ' ';
                if (value.length >= 15) formatted += value.substring(13, 15);
                else if (value.length > 13) formatted += value.substring(13);
                e.target.value = formatted.trim();
                validateSocialSecurity();
            });

            fileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    document.getElementById('file-error').style.display = 'none';
                } else {
                    fileName.textContent = '';
                }
            });

            function validateHospital() {
                const error = document.getElementById('hospital-error');
                if (!hospitalSelect.value) {
                    error.style.display = 'block';
                    hospitalSelect.classList.add('invalid');
                    return false;
                }
                error.style.display = 'none';
                hospitalSelect.classList.remove('invalid');
                return true;
            }

            function validateSocialSecurity() {
                const error = document.getElementById('ss-error');
                const digits = socialSecurityInput.value.replace(/\D/g, '');
                if (digits.length !== 15) {
                    error.style.display = 'block';
                    socialSecurityInput.classList.add('invalid');
                    return false;
                }
                error.style.display = 'none';
                socialSecurityInput.classList.remove('invalid');
                return true;
            }

            function validateDate() {
                const error = document.getElementById('date-error');
                if (!dateInput.value) {
                    error.style.display = 'block';
                    dateInput.classList.add('invalid');
                    return false;
                }
                error.style.display = 'none';
                dateInput.classList.remove('invalid');
                return true;
            }

            function validateFile() {
                const error = document.getElementById('file-error');
                if (!fileInput.files.length) {
                    error.style.display = 'block';
                    return false;
                }
                error.style.display = 'none';
                return true;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const isValid =
                    validateHospital() &&
                    validateSocialSecurity() &&
                    validateDate() &&
                    validateFile();

                if (isValid) {
                    successMessage.style.display = 'block';
                    form.reset();
                    fileName.textContent = '';
                } else {
                    successMessage.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
