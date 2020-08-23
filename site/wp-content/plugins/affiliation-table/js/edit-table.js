localStorage.setItem('affiliation-table-row-id', '0');

function updateActivePanel(activeNav) {
    if (activeNav === 'nav-edition') {
        document.querySelector('#edition-nav').classList.add('nav-tab-active');
        document.querySelector('#overview-nav').classList.remove('nav-tab-active');

        document.querySelector('#edition-panel').style.display = 'block';
        document.querySelector('#overview-panel').style.display = 'none';
    } else {
        document.querySelector('#edition-nav').classList.remove('nav-tab-active');
        document.querySelector('#overview-nav').classList.add('nav-tab-active');

        document.querySelector('#edition-panel').style.display = 'none';
        document.querySelector('#overview-panel').style.display = 'block';
    }
}

function toggleWithHeader() {
    if (document.querySelector('#with-header').checked) {
        document.querySelector('.table-content-header').style.display = 'table-row-group';
    } else {
        document.querySelector('.table-content-header').style.display = 'none';
    }
}

function deleteRow(rowId) {
    document.querySelector('#row-' + rowId).remove();
}

function addRowAfter(rowId) {
    const newId = (Number(localStorage.getItem('affiliation-table-row-id')) + 1).toString();
    localStorage.setItem('affiliation-table-row-id', newId);

    const tableRow = document.createElement('tr');
    tableRow.id = 'row-' + newId;

    const tableContentBody = document.querySelector('.table-content-body');
    if (rowId === 0) {
        tableContentBody.insertBefore(tableRow, tableContentBody.children[0]);
    } else {
        const position = !!rowId  ?
            [...tableContentBody.children].indexOf(document.querySelector('#row-' + rowId)) :
            -1;

        position !== -1 ?
            tableContentBody.insertBefore(tableRow, tableContentBody.children[position + 1]) :
            tableContentBody.appendChild(tableRow);
    }

    const actionsCell = document.createElement('td');
    actionsCell.className = 'table-cell-actions';
    tableRow.appendChild(actionsCell);

    const deleteRowButton = document.createElement('span');
    deleteRowButton.className = 'dashicons dashicons-minus action-button action-button-delete';
    deleteRowButton.title = 'Delete this row';
    deleteRowButton.addEventListener('click', () => deleteRow(Number(newId)));
    actionsCell.appendChild(deleteRowButton);

    const addRowButton = document.createElement('span');
    addRowButton.className = 'dashicons dashicons-plus action-button action-button-add';
    addRowButton.title = 'Add row after this row';
    addRowButton.addEventListener('click', () => addRowAfter(Number(newId)));
    actionsCell.appendChild(addRowButton);

    for (let i = 0; i < 4; i++) {
        const cell = document.createElement('td');
        cell.className = 'table-content-cell';
        tableRow.appendChild(cell);

        const cellContent = document.createElement('textarea');
        cellContent.maxLength = 255;
        cellContent.className = 'table-content-cell-content';
        cell.appendChild(cellContent);
    }
}