document.addEventListener("DOMContentLoaded", function () {
    // Function to toggle filter sections
    function toggleFilters(sectionId, arrowId) {
        let section = document.getElementById(sectionId);
        let arrow = document.getElementById(arrowId);

        if (section.style.display === "none") {
            section.style.display = "block";
            arrow.textContent = "expand_more"; // Change arrow
        } else {
            section.style.display = "none";
            arrow.textContent = "expand_less"; // Change arrow
        }
    }

    // Function to filter the table based on selected checkboxes
    function filterTable() {
        let table = document.getElementById("myTable");
        let rows = table.getElementsByTagName("tr");

        let selectedTilpums = getCheckedValues("tilpums");
        let selectedKategorija = getCheckedValues("kategorija");
        let selectedVeikals = getCheckedValues("veikals");

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
            let cells = rows[i].getElementsByTagName("td");
            if (cells.length > 0) {
                let tilpums = cells[1].textContent.trim();
                let kategorija = cells[4].textContent.trim();
                let veikals = cells[3].textContent.trim();

                let showRow =
                    (selectedTilpums.length === 0 || selectedTilpums.includes(tilpums)) &&
                    (selectedKategorija.length === 0 || selectedKategorija.includes(kategorija)) &&
                    (selectedVeikals.length === 0 || selectedVeikals.includes(veikals));

                rows[i].style.display = showRow ? "" : "none";
            }
        }
    }

    window.filterFirstColumn = function () {
        let input = document.getElementById("filterInput").value.toLowerCase();
        let table = document.getElementById("myTable");
        let rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let firstCell = rows[i].getElementsByTagName("td")[0]; // First column (Dzēriens)
            if (firstCell) {
                let textValue = firstCell.textContent || firstCell.innerText;
                rows[i].style.display = textValue.toLowerCase().includes(input) ? "" : "none";
            }
        }
    };

    // Helper function to get checked values from a given section
    function getCheckedValues(sectionName) {
        let checkboxes = document.querySelectorAll(`input[name="${sectionName}"]:checked`);
        return Array.from(checkboxes).map(cb => cb.value);
    }

    // Add event listeners for all checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener("change", filterTable);
    });

    // Expose toggleFilters to global scope
    window.toggleFilters = toggleFilters;
});