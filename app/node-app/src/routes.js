const express = require("express");
const router = express.Router();
const db = require("./db"); 

// Status/Health Check Route
// http://localhost:3000/api/status
router.get("/status", (req, res) => {
    res.status(200).json({ 
        message: 'Node.js API is operational.',
        timestamp: new Date().toISOString()
    });
});

//http://localhost:3000/api/patients?doctor_id=DOC1001
router.get("/patients", async (req, res) => {
    // Extract the doctor ID from the query string 
    const doctorId = req.query.doctor_id;

    if (!doctorId) {
        console.error('[API] Missing doctor_id query parameter.');
        return res.status(400).json({ 
            message: 'Missing doctor_id query parameter. The patient list must be filtered by the doctor.',
            error: 'MISSING_DOCTOR_ID'
        });
    }

    try {
        const query = `
            SELECT DISTINCT
                p.patient_id, 
                p.first_name, 
                p.last_name, 
                p.contact_no,
                p.sex,
                p.height,
                p.weight,
                p.blood_type,
                -- Calculates age in years 
                FLOOR(DATEDIFF(NOW(), p.birthdate) / 365.25) AS age 
            FROM patients AS p
            JOIN patient_records AS pr ON p.patient_id = pr.patient_id
            WHERE pr.dr_license_number = ?
            ORDER BY p.last_name ASC
        `;
        
        const [patients] = await db.query(query, [doctorId]);
        
        console.log(`[API] Fetched ${patients.length} patients for doctor ${doctorId}.`);
        
        res.json({
            count: patients.length,
            patients: patients
        });

    } catch (err) {
        console.error('Error fetching patients:', err.message);
        res.status(500).json({ 
            message: 'Failed to retrieve patient data.',
            error: err.message 
        });
    }
});

//http://localhost:3000/api/users
router.get("/users", async (req, res) => {
    try {
        const [rows] = await db.query("SELECT * FROM users");
        res.json(rows);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

module.exports = router;