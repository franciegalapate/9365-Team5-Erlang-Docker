<?php
session_start();

// Check if user is logged in and is a patient
if (!isset($_SESSION['account_type'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['account_type'] !== 'patient') {
    header('Location: unauthorized.php');
    exit;
}

require_once __DIR__ . '/db_connect.php';

// Get patient ID from session
$patient_id = $_SESSION['user_id'];
$prescriptions = [];

// Query to get all prescription details
$sql = "SELECT 
            pm.presc_med_id,
            p.presc_id,
            m.med_id,
            m.brand AS brand_name,
            m.name AS generic_name,
            pm.dosage,
            pm.dosage_instructions,
            pm.addtl_instructions,
            pm.qty,
            d.doctor_name AS doctor_name
        FROM 
            prescribed_medicines AS pm
        JOIN 
            prescriptions AS p ON pm.presc_id = p.presc_id
        JOIN 
            medicines AS m ON pm.med_id = m.med_id
        JOIN 
            doctors AS d ON p.dr_license_number = d.dr_license_number
        WHERE 
            p.patient_id = ?
        ORDER BY 
            p.presc_id DESC, pm.presc_med_id DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $prescriptions[] = $row;
        }
    }
    
    $stmt->close();
}

// Get patient's name for display
$patient_name = "";
$name_sql = "SELECT first_name, last_name FROM patients WHERE patient_id = ?";
$name_stmt = $conn->prepare($name_sql);
if ($name_stmt) {
    $name_stmt->bind_param("i", $patient_id);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    if ($name_row = $name_result->fetch_assoc()) {
        $patient_name = $name_row['first_name'] . ' ' . $name_row['last_name'];
    }
    $name_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions</title>
    <link rel="stylesheet" href="../assets/css/patient_view_prescriptions.css">
</head>
<body>
    <div class="main-container">
        <!-- Tab Header -->
        <nav class="tab-header">
            <div class="tab-button-wrapper">
                <a href="./patient_dashboard.php" class="tab-button">
                    <span class="material-symbols--dashboard-outline-rounded"></span>
                    <span>Patient Dashboard</span>
                </a>
                <a href="./patient_view_prescriptions.php" class="tab-button active">
                    <span class="mingcute--prescription-fill"></span>
                    <span>My Prescriptions</span>
                </a>
                <a href="./patient_purchase_history.php" class="tab-button">
                    <span class="mdi--cart-outline"></span>
                    <span>Purchase History</span>
                </a>
                <a href="./patient_medical_history.php" class="tab-button">
                    <span class="mdi--history"></span>
                    <span>Medical History</span>
                </a>
            </div>
            <a href="../../php/logout.php" class="logout-button">
                <span class="material-symbols--logout-rounded"></span>
            </a>
        </nav>

        <!-- Content Div -->
        <div class="content-wrapper">
            <div class="page-title">
                <h1>My Prescriptions</h1>
            </div>

            <div class="controls-bar">
                <div class="controls-left">
                    <div class="search-bar">
                        <span class="material-symbols--search-rounded"></span>
                        <input type="text" placeholder="Search by medicine name..." id="prescription-search-input">
                    </div>
                </div>
                <div class="controls-right">
                </div>
            </div>

            <!-- Table Container -->
            <div class="table-container">
                <table class="prescription-table">
                    <thead>
                        <tr>
                            <th data-sort-key="presc_id">ID <span class="sort-icon"></span></th>
                            <th data-sort-key="generic_name">Medicine Name <span class="sort-icon"></span></th>
                            <th data-sort-key="brand_name">Brand <span class="sort-icon"></span></th>
                            <th data-sort-key="dosage">Dosage <span class="sort-icon"></span></th>
                            <th>Dosage Instructions</th>
                            <th>Additional Instructions</th>
                            <th data-sort-key="doctor_name">Prescribed by <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody id="prescription-table-body">
                        <?php if (count($prescriptions) > 0): ?>
                            <?php foreach ($prescriptions as $prescription): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prescription['presc_id']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['generic_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['brand_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['dosage']); ?></td>
                                    <td class="instructions-cell"><?php echo htmlspecialchars($prescription['dosage_instructions'] ?? 'N/A'); ?></td>
                                    <td class="instructions-cell"><?php echo htmlspecialchars($prescription['addtl_instructions'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['doctor_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <div class="empty-state">
                                        <span class="mingcute--prescription-fill" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 12px;"></span>
                                        <p>No prescriptions found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const allPrescriptions = <?php echo json_encode($prescriptions); ?>;
    </script>
    <script src="../assets/js/patient_view_prescriptions.js" defer></script>
</body>
</html>