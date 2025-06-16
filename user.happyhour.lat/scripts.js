document.addEventListener("DOMContentLoaded", function () {
    // Function to toggle filter sections
    function toggleFilters(sectionId, arrowId) {
        let section = document.getElementById(sectionId);
        let arrow = document.getElementById(arrowId);

        if (section.style.display === "none") {
            section.style.display = "block";
            arrow.textContent = "expand_more";
        } else {
            section.style.display = "none";
            arrow.textContent = "expand_less";
        }
    }

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
            let firstCell = rows[i].getElementsByTagName("td")[0];
            if (firstCell) {
                let textValue = firstCell.textContent || firstCell.innerText;
                rows[i].style.display = textValue.toLowerCase().includes(input) ? "" : "none";
            }
        }
    };

    function getCheckedValues(sectionName) {
        let checkboxes = document.querySelectorAll(`input[name="${sectionName}"]:checked`);
        return Array.from(checkboxes).map(cb => cb.value);
    }

    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener("change", filterTable);
    });

    window.toggleFilters = toggleFilters;

    let showingOnlyChanges = false;

    function toggleNonZeroChanges() {
        const rows = document.querySelectorAll("#myTable tbody tr");
    
        showingOnlyChanges = !showingOnlyChanges;
    
        rows.forEach(row => {
            const changeCell = row.children[6];
    
            if (!changeCell) return;
    
            const text = changeCell.textContent.trim();
            const numeric = parseFloat(text.replace(/[^\d\.\-]/g, ''));
    
            if (showingOnlyChanges) {
                if (isNaN(numeric) || numeric === 0) {
                    row.style.display = "none";
                } else {
                    row.style.display = "";
                }
            } else {
                row.style.display = "";
            }
        });
    }
    window.toggleNonZeroChanges = toggleNonZeroChanges;

    let showingOnlyFavorites = false;

    function toggleFavorites() {
        console.log("Toggling favorites...");

        const rows = document.querySelectorAll("#myTable tbody tr");
        showingOnlyFavorites = !showingOnlyFavorites;

        rows.forEach(row => {
            const icon = row.querySelector("i.fa");
            if (!icon) return;

            const isFavorited = icon.classList.contains("fa-thumbs-up");

            if (showingOnlyFavorites) {
                row.style.display = isFavorited ? "" : "none";
            } else {
                row.style.display = "";
            }
        });
    }

    window.toggleFavorites = toggleFavorites;
});