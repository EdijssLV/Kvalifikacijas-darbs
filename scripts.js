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

//mainīt datu secības augoši vai dilstoši
let ascending = [];
function sortColumn(columnIndex, descending = false) {
    let table, rows, switching, i, x, y, shouldSwitch;
    table = document.querySelector("table");
    switching = true;
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("td")[columnIndex];
            y = rows[i + 1].getElementsByTagName("td")[columnIndex];
            let xValue = x.textContent || x.innerText;
            let yValue = y.textContent || y.innerText;
            if (!descending) {
                if (xValue.toLowerCase() > yValue.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else {
                if (xValue.toLowerCase() < yValue.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
    ascending[columnIndex] = !descending;
}

//Filtrēt kolonas
function toggleFilters(id) {
    var filterDiv = document.getElementById(id);
    var veikalsArrow = document.getElementById("tilpumsArrow");
    if (filterDiv.style.display === "none") {
        filterDiv.style.display = "block";
        veikalsArrow.style.transform = "rotate(180deg)";
        veikalsArrow.style.transition = "all 0.5s";
    } else {
        filterDiv.style.display = "none";
        veikalsArrow.style.transform = "rotate(0deg)";
        veikalsArrow.style.transition = "all 0.5s";
    }
}

function kategorijas() {
    var checkboxContainer = document.getElementById("kategorija");
    var veikalsArrow = document.getElementById("kategorijaArrow");
    if (checkboxContainer.style.display === "none") {
        checkboxContainer.style.display = "block";
        veikalsArrow.style.transform = "rotate(180deg)";
        veikalsArrow.style.transition = "all 0.5s";
    } else {
        checkboxContainer.style.display = "none";
        veikalsArrow.style.transform = "rotate(0deg)";
        veikalsArrow.style.transition = "all 0.5s";
    }
}

function veikals() {
    var checkboxContainer = document.getElementById("veikals");
    var veikalsArrow = document.getElementById("veikalsArrow");
    if (checkboxContainer.style.display === "none") {
        checkboxContainer.style.display = "block";
        veikalsArrow.style.transform = "rotate(180deg)";
        veikalsArrow.style.transition = "all 0.5s";
    } else {
        checkboxContainer.style.display = "none";
        veikalsArrow.style.transform = "rotate(0deg)";
        veikalsArrow.style.transition = "all 0.5s";
    }
}

function filterFirstColumn() {
    var input = document.getElementById("filterInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("myTable");
    var rows = table.getElementsByTagName("tr");
    for (var i = 0; i < rows.length; i++) {
        var cell = rows[i].getElementsByTagName("td")[0];
        if (cell) {
            var textValue = cell.textContent || cell.innerText;
            if (textValue.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
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
