// Data from PHP is loaded into 'allPrescriptions'
const originalPrescriptions = (typeof allPrescriptions !== 'undefined') ? allPrescriptions : [];
let currentSortKey = 'presc_id'; // Default sort key
let currentSortState = 2; // 2 = 'desc' (newest first)
const sortStates = ['none', 'asc', 'desc'];

// Get elements from the DOM
const searchInput = document.getElementById('prescription-search-input');
const tableBody = document.getElementById('prescription-table-body');
const headers = document.querySelectorAll('th[data-sort-key]');

// --- CORE FUNCTIONS ---

/**
 * Updates the view by filtering, sorting, and re-rendering the table.
 * This is the central function that runs on any state change.
 */
function updatePrescriptionView() {
    const searchTerm = searchInput.value.toLowerCase();

    let filteredPrescriptions = originalPrescriptions;

    if (searchTerm) {
        filteredPrescriptions = filteredPrescriptions.filter(prescription => {
            // Search by generic name, brand name, or doctor name
            const genericName = prescription.generic_name || '';
            const brandName = prescription.brand_name || '';
            const doctorName = prescription.doctor_name || '';

            // Combine all searchable fields into one string
            const valueToSearch = `${genericName} ${brandName} ${doctorName}`;

            return valueToSearch.toLowerCase().includes(searchTerm);
        });
    }

    // Apply sorting
    if (currentSortKey && currentSortState > 0) {
        sortData(filteredPrescriptions, currentSortKey, sortStates[currentSortState]);
    } else {
        sortData(filteredPrescriptions, 'presc_id', 'desc');
    }

    renderTable(filteredPrescriptions);
}

/**
 * Sorts the provided data array in place.
 * @param {Array} data - The array of prescription objects to sort.
 * @param {string} key - The key to sort by (e.g., 'generic_name', 'presc_id').
 * @param {string} direction - 'asc' or 'desc'.
 */
function sortData(data, key, direction) {
    data.sort((a, b) => {
        let valA = a[key];
        let valB = b[key];

        // Handle numeric sorting for 'presc_id' and 'med_id'
        if (key === 'presc_id' || key === 'med_id') {
            valA = Number(valA);
            valB = Number(valB);
        }
        // Handle string sorting (case-insensitive)
        else if (typeof valA === 'string') {
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
        }

        if (valA < valB) {
            return direction === 'asc' ? -1 : 1;
        }
        if (valA > valB) {
            return direction === 'asc' ? 1 : -1;
        }
        return 0;
    });
}

/**
 * Clears and re-populates the HTML table body with prescription data.
 * @param {Array} prescriptions - The filtered and sorted array of prescriptions.
 */
function renderTable(prescriptions) {
    // Clear existing table rows
    tableBody.innerHTML = '';

    if (prescriptions.length === 0) {
        // Display a "no results" message
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <span class="mingcute--prescription-fill" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 12px;"></span>
                        <p>No prescriptions found matching your search.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    // Create and append new rows
    prescriptions.forEach(prescription => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHTML(prescription.presc_id)}</td>
            <td>${escapeHTML(prescription.generic_name)}</td>
            <td>${escapeHTML(prescription.brand_name)}</td>
            <td>${escapeHTML(prescription.dosage)}</td>
            <td class="instructions-cell">${escapeHTML(prescription.dosage_instructions || 'N/A')}</td>
            <td class="instructions-cell">${escapeHTML(prescription.addtl_instructions || 'N/A')}</td>
            <td>${escapeHTML(prescription.doctor_name)}</td>
        `;
        tableBody.appendChild(row);
    });
}

/**
 * Updates the sort icons (▲/▼) in the table headers.
 */
function updateHeaderIcons() {
    headers.forEach(header => {
        const icon = header.querySelector('.sort-icon');
        if (!icon) return;

        const key = header.dataset.sortKey;
        header.classList.remove('sort-asc', 'sort-desc');

        // Set the active sort state on the header
        if (key === currentSortKey) {
            const state = sortStates[currentSortState];
            if (state === 'asc') {
                header.classList.add('sort-asc');
            } else if (state === 'desc') {
                header.classList.add('sort-desc');
            }
        }
    });
}

/**
 * A simple utility to escape HTML and prevent XSS.
 * @param {string} str - The string to escape.
 */
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// --- EVENT LISTENERS ---

// Listener for the search bar (triggers on every key press)
searchInput.addEventListener('input', updatePrescriptionView);

// Listeners for all sortable headers
headers.forEach(header => {
    header.addEventListener('click', () => {
        const key = header.dataset.sortKey;

        if (currentSortKey === key) {
            // Cycle through states: 0 -> 1 -> 2 -> 0
            currentSortState = (currentSortState + 1) % 3;
        } else {
            // New column clicked, start with 'asc'
            currentSortKey = key;
            currentSortState = 1; // 1 = 'asc'
        }

        updateHeaderIcons();
        updatePrescriptionView();
    });
});

// Initial render on page load
updatePrescriptionView();
updateHeaderIcons();