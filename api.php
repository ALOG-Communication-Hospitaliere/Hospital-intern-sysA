<?php
/**
 * Hospital System - REST API
 * 
 * This file contains all the API endpoints for the hospital system
 * including patient management and document retrieval.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection parameters
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'hospital_sys';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

// Establish database connection
function connectDB() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    $maxRetries = 5;
    $retryCount = 0;
    $retryDelay = 2; // seconds
    
    while ($retryCount < $maxRetries) {
        try {
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            $retryCount++;
            if ($retryCount >= $maxRetries) {
                sendError("Database connection failed after $maxRetries attempts: " . $e->getMessage(), 500);
            }
            error_log("Database connection attempt $retryCount failed: " . $e->getMessage());
            sleep($retryDelay);
        }
    }
}

// Helper function to send JSON response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Helper function to send error response
function sendError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Basic routing
switch ($method) {
    case 'GET':
        if (preg_match('/^patients\/([^\/]+)\/documents\/([^\/]+)\/([^\/]+)$/', $path, $matches)) {
            // GET /patients/{nss}/documents/{type}/{date}
            $nss = $matches[1];
            $type = $matches[2];
            $date = $matches[3];
            $document = getPatientDocument($nss, $type, $date);
            if ($document) {
                sendResponse($document);
            } else {
                sendError("Document not found", 404);
            }
        } elseif (preg_match('/^patients\/([^\/]+)\/documents$/', $path, $matches)) {
            // GET /patients/{nss}/documents
            $nss = $matches[1];
            $documents = getAllPatientDocuments($nss);
            sendResponse($documents);
        } elseif (preg_match('/^patients\/([^\/]+)$/', $path, $matches)) {
            // GET /patients/{nss}
            $nss = $matches[1];
            $patient = getPatient($nss);
            if ($patient) {
                sendResponse($patient);
            } else {
                sendError("Patient not found", 404);
            }
        } elseif ($path === 'patients') {
            // GET /patients
            $patients = getAllPatients();
            sendResponse($patients);
        } else {
            sendError("Invalid endpoint", 404);
        }
        break;

    case 'POST':
        if (preg_match('/^patients\/([^\/]+)\/documents$/', $path, $matches)) {
            // POST /patients/{nss}/documents
            $nss = $matches[1];
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                sendError("Invalid JSON data");
            }
            $result = addPatientDocument(
                $nss,
                $data['type_document'] ?? '',
                $data['description'] ?? '',
                $data['date_document'] ?? ''
            );
            if ($result) {
                sendResponse(['id' => $result], 201);
            } else {
                sendError("Failed to add document");
            }
        } elseif ($path === 'patients') {
            // POST /patients
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                sendError("Invalid JSON data");
            }
            $result = addPatient(
                $data['numero_securite_sociale'] ?? '',
                $data['nom'] ?? '',
                $data['prenom'] ?? '',
                $data['date_naissance'] ?? '',
                $data['adresse'] ?? '',
                $data['telephone'] ?? '',
                $data['email'] ?? ''
            );
            if ($result) {
                sendResponse(['id' => $result], 201);
            } else {
                sendError("Failed to add patient");
            }
        } else {
            sendError("Invalid endpoint", 404);
        }
        break;

    default:
        sendError("Method not allowed", 405);
        break;
}

// Function to get a patient by social security number
function getPatient($numero_securite_sociale) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("SELECT * FROM patients WHERE numero_securite_sociale = :nss");
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error retrieving patient: " . $e->getMessage());
        return null;
    }
}

// Function to get all patients
function getAllPatients() {
    $conn = connectDB();
    
    try {
        $stmt = $conn->query("SELECT * FROM patients ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error retrieving patients: " . $e->getMessage());
        return [];
    }
}

// Reuse existing functions from main.php
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
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error retrieving document: " . $e->getMessage());
        return null;
    }
}

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

function addPatientDocument($numero_securite_sociale, $type_document, $description, $date_document) {
    $conn = connectDB();
    
    try {
        // Get patient ID from social security number
        $stmt = $conn->prepare("SELECT id FROM patients WHERE numero_securite_sociale = :nss");
        $stmt->bindParam(':nss', $numero_securite_sociale, PDO::PARAM_STR);
        $stmt->execute();
        
        $patient_id = $stmt->fetchColumn();
        
        if (!$patient_id) {
            return false; // Patient not found
        }
        
        if (empty($description)) {
            $description = 'No description provided';
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
        error_log("Error adding document: " . $e->getMessage());
        return false;
    }
}

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