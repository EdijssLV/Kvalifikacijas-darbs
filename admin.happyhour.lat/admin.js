function showDiv(divId) {
    if (divId === 'table1') {
      document.getElementById('table1').style.display = 'block';
      document.getElementById('table2').style.display = 'none';
    } else if (divId === 'table2') {
      document.getElementById('table1').style.display = 'none';
      document.getElementById('table2').style.display = 'block';
    }
}