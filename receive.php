<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Documents Médicaux</title>
    <style>
        :root {
            --primary-color: #3b82f6;
            --text-color: #1f2937;
            --border-color: #e5e7eb;
            --bg-color: #f9fafb;
            --card-color: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.5;
            padding: 16px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-item {
            flex: 1;
            min-width: 180px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.875rem;
            color: #4b5563;
        }

        input, select, button {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.875rem;
            background-color: var(--card-color);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #2563eb;
        }

        .table-container {
            background-color: var(--card-color);
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            background-color: #f3f4f6;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-top: 16px;
        }

        .page-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            background-color: var(--card-color);
            cursor: pointer;
            border-radius: 4px;
            min-width: 36px;
            text-align: center;
        }

        .page-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow: auto;
        }

        .modal-content {
            background-color: var(--card-color);
            max-width: 700px;
            margin: 40px auto;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            width: auto;
            padding: 0;
            color: #6b7280;
        }

        .modal-body {
            padding: 16px;
        }

        .document-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-group {
            margin-bottom: 12px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .document-content {
            padding: 16px;
            background-color: #f9fafb;
            border-radius: 4px;
            margin-top: 16px;
            font-size: 0.875rem;
        }

        .action-btns {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .action-cell {
            white-space: nowrap;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            padding: 4px 8px;
            font-size: 0.75rem;
            width: auto;
        }
        
        .action-btn:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .document-info {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filter-item {
                width: 100%;
            }
            
            td, th {
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Documents Médicaux</h1>
            <div id="document-count">12 documents</div>
        </header>

        <div class="filters">
            <div class="filter-item">
                <label for="filter-nom">Nom</label>
                <input type="text" id="filter-nom" placeholder="Rechercher par nom">
            </div>
            <div class="filter-item">
                <label for="filter-prenom">Prénom</label>
                <input type="text" id="filter-prenom" placeholder="Rechercher par prénom">
            </div>
            <div class="filter-item">
                <label for="filter-ss">N° Sécurité Sociale</label>
                <input type="text" id="filter-ss" placeholder="N° SS">
            </div>
            <div class="filter-item">
                <label for="filter-type">Type</label>
                <select id="filter-type">
                    <option value="">Tous les types</option>
                    <option value="Analyse">Analyse</option>
                    <option value="Radiographie">Radiographie</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Ordonnance">Ordonnance</option>
                    <option value="Echographie">Échographie</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="filter-date">Date</label>
                <input type="date" id="filter-date">
            </div>
            <div class="filter-item" style="display: flex; flex-direction: column; justify-content: flex-end;">
                <button class="btn" id="apply-filters">Filtrer</button>
            </div>
        </div>

        <div class="table-container">
            <table id="documents-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>N° Sécurité Sociale</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="documents-list">
                    <!-- Rows will be populated dynamically -->
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">»</button>
        </div>
    </div>

    <!-- Modal for document details -->
    <div class="modal" id="document-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails du Document</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="document-info">
                    <div class="info-group">
                        <div class="info-label">NOM</div>
                        <div id="detail-nom">DUPONT</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">PRÉNOM</div>
                        <div id="detail-prenom">Marie</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">N° SÉCURITÉ SOCIALE</div>
                        <div id="detail-ss">189057512345678</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">TYPE DE DOCUMENT</div>
                        <div id="detail-type">Analyse de sang</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">DATE</div>
                        <div id="detail-date">15/05/2025</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">ÉTABLISSEMENT</div>
                        <div id="detail-hospital">Hôpital Saint-Antoine</div>
                    </div>
                </div>

                <div class="document-content" id="detail-content">
                    <!-- Document content will be inserted here -->
                </div>

                
            </div>
        </div>
    </div>

    <script>
        // Sample data for documents with added nom/prenom fields
        const documentsData = [
            {
                id: 1,
                nom: "DUPONT",
                prenom: "Marie",
                patient_id: "PAT2025-1245",
                numero_securite_sociale: "189057512345678",
                type_document: "Analyse de sang",
                description: "Contrôle trimestriel",
                date_document: "15/05/2025",
                hospital: "Hôpital Saint-Antoine",
                content: `<p>Analyse de sang réalisée le 15/05/2025 à l'Hôpital Saint-Antoine dans le cadre d'un contrôle trimestriel.</p>
                    
                    <p><strong>Résultats:</strong></p>
                    <ul>
                        <li>Globules rouges: 4.8 millions/mm³ (Norme: 4.5-5.5)</li>
                        <li>Globules blancs: 7500/mm³ (Norme: 4000-10000)</li>
                        <li>Plaquettes: 250,000/mm³ (Norme: 150,000-400,000)</li>
                        <li>Hémoglobine: 14.2 g/dL (Norme: 13-17)</li>
                        <li>Glycémie à jeun: 0.95 g/L (Norme: 0.7-1.1)</li>
                        <li>Cholestérol total: 1.8 g/L (Norme: 1.5-2.0)</li>
                    </ul>

                    <p><strong>Interprétation:</strong><br>
                    Résultats dans les normes. Aucune anomalie détectée.</p>
                    
                    <p><strong>Recommandation:</strong><br>
                    Prochain contrôle prévu dans 3 mois.</p>`
            },
            {
                id: 2,
                nom: "DUPONT",
                prenom: "Marie",
                patient_id: "PAT2025-1245",
                numero_securite_sociale: "189057512345678",
                type_document: "Radiographie",
                description: "Radiographie thoracique",
                date_document: "14/05/2025",
                hospital: "Hôpital Bichat",
                content: `<p>Radiographie thoracique réalisée le 14/05/2025 à l'Hôpital Bichat.</p>
                    
                    <p><strong>Indication:</strong> Contrôle annuel</p>

                    <p><strong>Technique:</strong> Radiographie du thorax de face et de profil</p>

                    <p><strong>Résultats:</strong><br>
                    Parenchymes pulmonaires clairs. Absence d'opacité ou de nodule suspect. 
                    Coupoles diaphragmatiques bien dessinées. Absence d'épanchement pleural.
                    Silhouette cardiaque de taille normale. Absence d'anomalie médiastinale.</p>

                    <p><strong>Conclusion:</strong><br>
                    Radiographie thoracique normale.</p>`
            },
            {
                id: 3,
                nom: "DUPONT",
                prenom: "Marie",
                patient_id: "PAT2025-1245",
                numero_securite_sociale: "189057512345678",
                type_document: "Consultation",
                description: "Suivi annuel cardiologie",
                date_document: "12/05/2025",
                hospital: "Hôpital Cochin",
                content: `<p>Consultation de cardiologie du 12/05/2025 à l'Hôpital Cochin.</p>
                    
                    <p><strong>Médecin:</strong> Dr. Martinez</p>
                    
                    <p><strong>Motif:</strong> Suivi annuel</p>
                    
                    <p><strong>Compte-rendu:</strong><br>
                    Patient en bon état général. Absence de symptomatologie cardiovasculaire.
                    Tension artérielle: 125/80 mmHg.
                    Fréquence cardiaque: 72 bpm.</p>
                    
                    <p><strong>Conclusion:</strong><br>
                    Examen cardiologique normal. Poursuite du traitement habituel.
                    Prochain contrôle dans un an.</p>`
            },
            {
                id: 4,
                nom: "MARTIN",
                prenom: "Jean",
                patient_id: "PAT2025-2567",
                numero_securite_sociale: "294068523456789",
                type_document: "Échographie",
                description: "Échographie abdominale",
                date_document: "11/05/2025",
                hospital: "Hôpital Necker",
                content: `<p>Échographie abdominale réalisée le 11/05/2025 à l'Hôpital Necker.</p>
                    
                    <p><strong>Résultats:</strong><br>
                    Foie de taille normale, contours réguliers, échostructure homogène.
                    Vésicule biliaire alithiasique, paroi fine.
                    Voies biliaires intra et extra-hépatiques non dilatées.</p>
                    
                    <p><strong>Conclusion:</strong><br>
                    Échographie abdominale sans anomalie décelable.</p>`
            },
            {
                id: 5,
                nom: "MARTIN",
                prenom: "Jean",
                patient_id: "PAT2025-2567",
                numero_securite_sociale: "294068523456789",
                type_document: "Ordonnance",
                description: "Renouvellement traitement",
                date_document: "10/05/2025",
                hospital: "Hôpital Tenon",
                content: `<p>Ordonnance du 10/05/2025 - Hôpital Tenon</p>
                    
                    <p><strong>Médecin prescripteur:</strong> Dr. Dupont</p>
                    
                    <p><strong>Prescriptions:</strong></p>
                    <ul>
                        <li>Paracétamol 1000mg: 1 comprimé 3 fois par jour si douleur (max 3g/jour)</li>
                        <li>Oméprazole 20mg: 1 gélule le matin à jeun</li>
                    </ul>`
            },
            {
                id: 6,
                nom: "BERNARD",
                prenom: "Sophie",
                patient_id: "PAT2025-3891",
                numero_securite_sociale: "375079634567890",
                type_document: "Analyse de sang",
                description: "Bilan hépatique",
                date_document: "09/05/2025",
                hospital: "Hôpital Pitié-Salpêtrière",
                content: `<p>Bilan hépatique réalisé le 09/05/2025 à l'Hôpital Pitié-Salpêtrière.</p>
                    
                    <p><strong>Résultats:</strong></p>
                    <ul>
                        <li>ASAT (TGO): 32 UI/L (Norme: 15-40)</li>
                        <li>ALAT (TGP): 35 UI/L (Norme: 10-40)</li>
                        <li>GGT: 38 UI/L (Norme: 10-50)</li>
                        <li>PAL: 85 UI/L (Norme: 40-130)</li>
                    </ul>
                    
                    <p><strong>Conclusion:</strong><br>
                    Bilan hépatique normal. Absence de cytolyse ou de cholestase.</p>`
            },
            {
                id: 7,
                nom: "BERNARD",
                prenom: "Sophie",
                patient_id: "PAT2025-3891",
                numero_securite_sociale: "375079634567890",
                type_document: "Radiographie",
                description: "Radio dentaire panoramique",
                date_document: "08/05/2025",
                hospital: "Hôpital Robert-Debré",
                content: `<p>Radiographie dentaire panoramique réalisée le 08/05/2025 à l'Hôpital Robert-Debré.</p>
                    
                    <p><strong>Indication:</strong> Évaluation préopératoire avant extraction des dents de sagesse</p>
                    
                    <p><strong>Résultats:</strong><br>
                    Présence des quatre dents de sagesse (18, 28, 38, 48).
                    Dents 38 et 48 en position horizontale avec proximité du canal mandibulaire.
                    Dents 18 et 28 en position normale, partiellement enclavées.</p>`
            },
            {
                id: 8,
                nom: "PETIT",
                prenom: "Thomas",
                patient_id: "PAT2025-4562",
                numero_securite_sociale: "182056723456123",
                type_document: "Consultation",
                description: "Suivi dermatologique",
                date_document: "07/05/2025",
                hospital: "Hôpital Saint-Louis",
                content: `<p>Consultation dermatologique du 07/05/2025 à l'Hôpital Saint-Louis.</p>
                    
                    <p><strong>Motif:</strong> Suivi de lésions cutanées</p>
                    
                    <p><strong>Examen:</strong><br>
                    Évolution favorable des lésions précédemment identifiées.
                    Absence de nouvelles lésions suspectes.</p>`
            }
        ];

        // Wait for DOM content to load
        document.addEventListener('DOMContentLoaded', function() {
            const documentsList = document.getElementById('documents-list');
            const modal = document.getElementById('document-modal');
            const closeBtn = document.querySelector('.close-btn');
            const applyFiltersBtn = document.getElementById('apply-filters');

            // Function to create a component for documents table
            function createDocumentComponent(document) {
                return `
                    <tr class="document-row" data-id="${document.id}">
                        <td>${document.nom}</td>
                        <td>${document.prenom}</td>
                        <td>${document.numero_securite_sociale}</td>
                        <td>${document.type_document}</td>
                        <td>${document.date_document}</td>
                        <td>${document.description}</td>
                        
                    </tr>
                `;
            }

            // Load documents initially
            function loadDocuments(documents) {
                documentsList.innerHTML = '';
                
                if (documents.length === 0) {
                    documentsList.innerHTML = '<tr><td colspan="7" style="text-align:center;">Aucun document trouvé</td></tr>';
                    return;
                }
                
                documents.forEach(doc => {
                    documentsList.innerHTML += createDocumentComponent(doc);
                });
                
                // Update document count
                document.getElementById('document-count').textContent = `${documents.length} documents`;
                
                // Add event listeners to view buttons
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const docId = this.getAttribute('data-id');
                        openDocumentDetails(docId);
                    });
                });
                
                // Add event listeners to download buttons
                document.querySelectorAll('.download-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const docId = this.getAttribute('data-id');
                        alert(`Téléchargement du document #${docId} en cours...`);
                    });
                });
                
                // Make entire row clickable
                document.querySelectorAll('.document-row').forEach(row => {
                    row.addEventListener('click', function() {
                        const docId = this.getAttribute('data-id');
                        openDocumentDetails(docId);
                    });
                });
            }

            // Open document details modal
            function openDocumentDetails(docId) {
                const document = documentsData.find(doc => doc.id == docId);
                
                if (document) {
                    document.getElementById('detail-nom').textContent = document.nom;
                    document.getElementById('detail-prenom').textContent = document.prenom;
                    document.getElementById('detail-ss').textContent = document.numero_securite_sociale;
                    document.getElementById('detail-type').textContent = document.type_document;
                    document.getElementById('detail-date').textContent = document.date_document;
                    document.getElementById('detail-hospital').textContent = document.hospital;
                    document.getElementById('detail-content').innerHTML = document.content;
                    
                    modal.style.display = 'block';
                }
            }

            // Filter documents
            function filterDocuments() {
                const nomFilter = document.getElementById('filter-nom').value.toLowerCase();
                const prenomFilter = document.getElementById('filter-prenom').value.toLowerCase();
                const ssFilter = document.getElementById('filter-ss').value;
                const typeFilter = document.getElementById('filter-type').value;
                const dateFilter = document.getElementById('filter-date').value;
                
                // Convert date filter to comparable format if provided
                let formattedDateFilter = null;
                if (dateFilter) {
                    const [year, month, day] = dateFilter.split('-');
                    formattedDateFilter = `${day}/${month}/${year}`;
                }
                
                const filteredDocs = documentsData.filter(doc => {
                    return (nomFilter === '' || doc.nom.toLowerCase().includes(nomFilter)) &&
                           (prenomFilter === '' || doc.prenom.toLowerCase().includes(prenomFilter)) &&
                           (ssFilter === '' || doc.numero_securite_sociale.includes(ssFilter)) &&
                           (typeFilter === '' || doc.type_document.includes(typeFilter)) &&
                           (!formattedDateFilter || doc.date_document === formattedDateFilter);
                });
                
                loadDocuments(filteredDocs);
            }

            // Initialize with all documents
            loadDocuments(documentsData);

            // Event listeners
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            applyFiltersBtn.addEventListener('click', filterDocuments);
            
            // Pagination buttons (for demonstration)
            const pageButtons = document.querySelectorAll('.page-btn');
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    if (!this.textContent.includes('»')) {
                        this.classList.add('active');
                    }
                    // In a real application, this would load the appropriate page of results
                });
            });
            
            // Print button functionality
            document.getElementById('btn-print').addEventListener('click', function() {
                alert('Impression du document en cours...');
            });
            
            // Download button functionality
            document.getElementById('btn-download').addEventListener('click', function() {
                alert('Téléchargement du document en cours...');
            });
            
            // Share button functionality
            document.getElementById('btn-share').addEventListener('click', function() {
                alert('Ouverture de la boîte de dialogue de partage...');
            });
        });
    </script>
</body>
</html>
