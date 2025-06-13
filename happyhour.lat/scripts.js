//paslēpt filtru
function toggleDiv() {
    var div = document.getElementById("myDiv");
    var style = window.getComputedStyle(div);
    if (style.display === "none") {
        div.style.display = "block";
    } else {
        div.style.display = "none";
    }
}

function toggleFilters(id, arrowId) {
    var filterDiv = document.getElementById(id);
    var arrow = document.getElementById(arrowId);

    if (filterDiv.style.display === "none") {
        filterDiv.style.display = "block";
        arrow.style.transform = "rotate(180deg)";
    } else {
        filterDiv.style.display = "none";
        arrow.style.transform = "rotate(0deg)";
    }
    arrow.style.transition = "all 0.5s";
}

function filterFirstColumn() {
    // Get the input value
    const input = document.getElementById("filterInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("myTable");
    const rows = table.getElementsByTagName("tr");

    // Loop through all table rows (except the header)
    for (let i = 1; i < rows.length; i++) {
        const firstCell = rows[i].getElementsByTagName("td")[0]; // First column
        if (firstCell) {
            const textValue = firstCell.textContent || firstCell.innerText;
            if (textValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = ""; // Show row
            } else {
                rows[i].style.display = "none"; // Hide row
            }
        }
    }
}

function filterTable() {
    var tilpumsCheckboxes = document.getElementsByName('tilpums');
    var stiprieDzerieniCheckboxes = document.getElementsByName('stiprieDzerieni');
    var veikalsCheckboxes = document.getElementsByName('veikals');
    var tableRows = document.querySelectorAll('#myTable tbody tr');

    var anyTilpumsChecked = Array.from(tilpumsCheckboxes).some(function(checkbox) {
        return checkbox.checked;
    });
    var anyStiprieDzerieniChecked = Array.from(stiprieDzerieniCheckboxes).some(function(checkbox) {
        return checkbox.checked;
    });
    var anyVeikalsChecked = Array.from(veikalsCheckboxes).some(function(checkbox) {
        return checkbox.checked;
    });

    tableRows.forEach(function(row) {
        var cellTilpums = row.cells[1].textContent.toLowerCase(); // Column 2
        var cellVeikals = row.cells[3].textContent.toLowerCase(); // Column 4
        var cellStiprieDzerieni = row.cells[4].textContent.toLowerCase(); // Column 5

        var isVisibleTilpums = Array.from(tilpumsCheckboxes).some(function(checkbox) {
            return checkbox.checked && cellTilpums.includes(checkbox.value.toLowerCase());
        });

        var isVisibleStiprieDzerieni = Array.from(stiprieDzerieniCheckboxes).some(function(checkbox) {
            return checkbox.checked && cellStiprieDzerieni.includes(checkbox.value.toLowerCase());
        });

        var isVisibleVeikals = Array.from(veikalsCheckboxes).some(function(checkbox) {
            return checkbox.checked && cellVeikals.includes(checkbox.value.toLowerCase());
        });

        // Show row if it matches any filter
        if ((isVisibleTilpums || !anyTilpumsChecked) &&
            (isVisibleStiprieDzerieni || !anyStiprieDzerieniChecked) &&
            (isVisibleVeikals || !anyVeikalsChecked)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
document.querySelectorAll('input[name="tilpums"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', filterTable);
});
document.querySelectorAll('input[name="stiprieDzerieni"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', filterTable);
});
document.querySelectorAll('input[name="veikals"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', filterTable);
});

document.getElementById("filterForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission

    // Get filter values
    var minVolume = document.getElementById("minVolume").value;
    var maxVolume = document.getElementById("maxVolume").value;
    var minPricePerL = document.getElementById("minPricePerL").value;
    var maxPricePerL = document.getElementById("maxPricePerL").value;

    // Build URL query string
    var queryParams = new URLSearchParams(window.location.search);
    if (minVolume) queryParams.set("minVolume", minVolume);
    else queryParams.delete("minVolume");
    
    if (maxVolume) queryParams.set("maxVolume", maxVolume);
    else queryParams.delete("maxVolume");

    if (minPricePerL) queryParams.set("minPricePerL", minPricePerL);
    else queryParams.delete("minPricePerL");

    if (maxPricePerL) queryParams.set("maxPricePerL", maxPricePerL);
    else queryParams.delete("maxPricePerL");

    // Reload page with filters applied
    window.location.search = queryParams.toString();
});
