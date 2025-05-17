<?php
/**
 * Hospital System - Monolithic PHP Application
 * 
 * This file contains all the necessary functions for a hospital system
 * including patient management and document retrieval.
 * Modified to use description instead of XML for document content.
 */

// Database connection parameters
$db_host = 'localhost';
$db_name = 'hospital_sys';
$db_user = 'root';
$db_pass = '';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establish database connection
function connectDB() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    try {
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * Get patient document by social security number, document type and date
 * 
 * @param string $numero_securite_sociale Social security number
 * @param string $type_document Document type (e.g., 'analyse_sang', 'radiographie')
 * @param string $date_document Document date in YYYY-MM-DD format
 * @return array|null Document data or null if not found
 */
function getPatientDocument($numero_securite_sociale, $type_document, $date_document) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("
            SELECT pd.*, p.nom, p.prenom 
            FROM patient_documents pd
            JOIN patients p ON pd.patient_id = p.id
            WHERE pd.numero_securite_sociale = :nss
            AND pd.type_document = :type
            AND pd.date_document = :date
        ");
        
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type_document, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date_document, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    } catch(PDOException $e) {
        error_log("Error retrieving document: " . $e->getMessage());
        return null;
    }
}

/**
 * Add a new patient to the system
 * 
 * @param string $numero_securite_sociale Social security number
 * @param string $nom Last name
 * @param string $prenom First name
 * @param string $date_naissance Birth date in YYYY-MM-DD format
 * @param string $adresse Address
 * @param string $telephone Phone number
 * @param string $email Email address
 * @return int|false Patient ID if successful, false otherwise
 */
function addPatient($numero_securite_sociale, $nom, $prenom, $date_naissance, $adresse, $telephone, $email) {
    $conn = connectDB();
    
    try {
        // Check if patient with this social security number already exists
        $check = $conn->prepare("SELECT id FROM patients WHERE numero_securite_sociale = :nss");
        $check->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $check->execute();
        
        if ($check->fetchColumn()) {
            return false; // Patient already exists
        }
        
        $stmt = $conn->prepare("
            INSERT INTO patients 
            (numero_securite_sociale, nom, prenom, date_naissance, adresse, telephone, email)
            VALUES (:nss, :nom, :prenom, :birth, :adresse, :tel, :email)
        ");
        
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindParam(':birth', $date_naissance, PDO::PARAM_STR);
        $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
        $stmt->bindParam(':tel', $telephone, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        
        $stmt->execute();
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        error_log("Error adding patient: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a document to a patient
 * 
 * @param string $numero_securite_sociale Social security number
 * @param string $type_document Document type
 * @param string $description Document description
 * @param string $date_document Document date in YYYY-MM-DD format
 * @return int|false Document ID if successful, false otherwise
 */
function addPatientDocument($numero_securite_sociale, $type_document, $description, $date_document) {
    $conn = connectDB();
    
    try {
        // Get patient ID from social security number - first check if patient exists
        $stmt = $conn->prepare("SELECT id FROM patients WHERE numero_securite_sociale = :nss");
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->execute();
        
        $patient_id = $stmt->fetchColumn();
        
        if (!$patient_id) {
            error_log("Patient not found with SSN: " . $numero_securite_sociale);
            return false; // Patient not found
        }
        
        // If description is null or empty, set a default value
        if (empty($description)) {
            $description = 'No description provided'; // Provide a default value
        }
        
        $stmt = $conn->prepare("
            INSERT INTO patient_documents 
            (patient_id, numero_securite_sociale, type_document, description, date_document)
            VALUES (:patient_id, :nss, :type, :desc, :date)
        ");
        
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type_document, PDO::PARAM_STR);
        $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date_document, PDO::PARAM_STR);
        
        $stmt->execute();
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        // Log the error message for debugging
        error_log("Error adding document: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all documents for a patient
 * 
 * @param string $numero_securite_sociale Social security number
 * @return array List of documents
 */
function getAllPatientDocuments($numero_securite_sociale) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("
            SELECT pd.*, p.nom, p.prenom 
            FROM patient_documents pd
            JOIN patients p ON pd.patient_id = p.id
            WHERE pd.numero_securite_sociale = :nss
            ORDER BY pd.date_document DESC
        ");
        
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error retrieving documents: " . $e->getMessage());
        return [];
    }
}

// Handle form submissions
$message = '';
$error = '';
$document = null;
$all_documents = [];

// Handle document retrieval
if (isset($_POST['action']) && $_POST['action'] == 'get_document') {
    $nss = $_POST['numero_securite_sociale'] ?? '';
    $type = $_POST['type_document'] ?? '';
    $date = $_POST['date_document'] ?? '';
    
    if (!empty($nss) && !empty($type) && !empty($date)) {
        $document = getPatientDocument($nss, $type, $date);
        
        if (!$document) {
            $error = "Document not found for the given criteria.";
        } 
    } else {
        $error = "Please fill all required fields.";
    }
}

// Handle patient addition
if (isset($_POST['action']) && $_POST['action'] == 'add_patient') {
    $nss = $_POST['numero_securite_sociale'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';
    
    if (!empty($nss) && !empty($nom) && !empty($prenom) && !empty($date_naissance)) {
        $adresse = $_POST['adresse'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        
        $result = addPatient($nss, $nom, $prenom, $date_naissance, $adresse, $telephone, $email);
        
        if ($result) {
            $message = "success";
        } else {
            $error = "Failed to add patient. The social security number might already exist.";
        }
    } else {
        $error = "Please fill all required fields.";
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'add_document') {
    $nss = $_POST['numero_securite_sociale'] ?? '';
    $type = $_POST['type_document'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date_document'] ?? '';
    
    if (!empty($nss) && !empty($type) && !empty($date)) {
        // Add more validation if needed
        if (strlen($nss) < 5) {
            $error = "Invalid Social Security Number format.";
        } else {
            $result = addPatientDocument($nss, $type, $description, $date);
            
            if ($result) {
                $message = "Success";
                
                // Fetch all documents for this patient to show after adding
                $all_documents = getAllPatientDocuments($nss);
            } else {
                $error = "Failed to add document. Please check if the patient exists.";
            }
        }
    } else {
        $error = "Please fill all required fields (SSN, Type, and Date).";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système Hospitalier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            margin-right: 5px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            background-color: #f8f8f8;
        }
        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
            font-weight: bold;
        }
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .tab-content.active {
            display: block;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        pre {
            background-color: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Système Hospitalier</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" data-tab="get-document">Chercher document</div>
            <div class="tab" data-tab="add-patient">Ajouter Patient</div>
            <div class="tab" data-tab="add-document">Ajouter Document</div>
        </div>
        
        <!-- Get Document Tab -->
        <div class="tab-content active" id="get-document">
            <h2>Chercher document</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="get_document">
                
                <label for="numero_securite_sociale">Numéro de Sécurité Sociale:</label>
                <input type="text" id="numero_securite_sociale" name="numero_securite_sociale" required>
                
                <label for="type_document">Type de Document:</label>
                <select id="type_document" name="type_document" required>
                    <option value="">-- Select Type --</option>
                    <option value="analyse_sang">Analyse de sang</option>
                    <option value="radiographie">Radiographie</option>
                    <option value="echographie">Échographie</option>
                    <option value="consultation">Consultation</option>
                    <option value="ordonnance">Ordonnance</option>
                </select>
                
                <label for="date_document">Date du Document:</label>
                <input type="date" id="date_document" name="date_document" required>
                
                <button type="submit">Cherecher</button>
            </form>
            
            <?php if ($document): ?>
                <h3>Document Trouvé</h3>
                <table>
                    <tr>
                        <th>Patient</th>
                        <td><?php echo htmlspecialchars($document['prenom'] . ' ' . $document['nom']); ?></td>
                    </tr>
                    <tr>
                        <th>Numéro de Sécurité Sociale</th>
                        <td><?php echo htmlspecialchars($document['numero_securite_sociale']); ?></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td><?php echo htmlspecialchars($document['type_document']); ?></td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td><?php echo htmlspecialchars($document['date_document']); ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo htmlspecialchars($document['description']); ?></td>
                    </tr>
                </table>
                
            <?php endif; ?>
        </div>
        
        <!-- Add Patient Tab -->
        <div class="tab-content" id="add-patient">
            <h2>Ajouter Patient</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_patient">
                
                <label for="numero_securite_sociale">Numéro de Sécurité Sociale:</label>
                <input type="text" id="numero_securite_sociale" name="numero_securite_sociale" required>
                
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" required>
                
                <label for="prenom">Prénom:</label>
                <input type="text" id="prenom" name="prenom" required>
                
                <label for="date_naissance">Date de Naissance:</label>
                <input type="date" id="date_naissance" name="date_naissance" required>
                
                <label for="adresse">Adresse:</label>
                <textarea id="adresse" name="adresse" rows="3"></textarea>
                
                <label for="telephone">Téléphone:</label>
                <input type="text" id="telephone" name="telephone">
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
                
                <button type="submit">Ajouter</button>
            </form>
        </div>
        
        <!-- Add Document Tab -->
        <div class="tab-content" id="add-document">
            <h2>Ajuter Document</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_document">
                
                <label for="numero_securite_sociale">Numéro de Sécurité Sociale:</label>
                <input type="text" id="numero_securite_sociale" name="numero_securite_sociale" required>
                
                <label for="type_document">Type de Document:</label>
                <select id="type_document" name="type_document" required>
                    <option value="">-- Select Type --</option>
                    <option value="analyse_sang">Analyse de sang</option>
                    <option value="radiographie">Radiographie</option>
                    <option value="echographie">Échographie</option>
                    <option value="consultation">Consultation</option>
                    <option value="ordonnance">Ordonnance</option>
                </select>
                
                <label for="description">Description (optional):</label>
                <textarea id="description" name="description" rows="2"></textarea>
                
                <label for="date_document">Date du Document:</label>
                <input type="date" id="date_document" name="date_document" required>
                
                <button type="submit">Add Document</button>
            </form>
            
            <?php if (!empty($all_documents)): ?>
                <h3>Patient Documents</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_documents as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['type_document']); ?></td>
                                <td><?php echo htmlspecialchars($doc['date_document']); ?></td>
                                <td><?php echo htmlspecialchars($doc['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <script>
            // Tab switching functionality
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // Remove active class from all tabs and contents
                        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                        
                        // Add active class to current tab
                        this.classList.add('active');
                        
                        // Show corresponding content
                        const tabId = this.getAttribute('data-tab');
                        document.getElementById(tabId).classList.add('active');
                    });
                });
            });
        </script>
    </div>
</body>
</html>