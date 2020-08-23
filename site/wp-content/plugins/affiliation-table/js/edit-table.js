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
        document.querySelector('[data-row-id="0"]').style.display = 'grid';
    } else {
        document.querySelector('[data-row-id="0"]').style.display = 'none';
    }
}

function deleteRow(rowId) {
    document.querySelector(`[data-row-id="${rowId}"]`).remove();
}

function addRowAfter(rowId) {
    const newId = (Number(localStorage.getItem('affiliation-table-row-id')) + 1).toString();
    localStorage.setItem('affiliation-table-row-id', newId);

    const tableContent = document.querySelector('.table-content');
    const position = !!rowId || rowId === 0 ?
        [...tableContent.children].indexOf(document.querySelector(`[data-row-id="${rowId}"]`)) :
        -1;

    const tableRow = document.createElement('div');
    tableRow.className = 'table-row';
    tableRow.setAttribute('data-row-id', newId);
    position !== -1 ?
        tableContent.insertBefore(tableRow, tableContent.children[position + 1]) :
        tableContent.appendChild(tableRow);

    const actionsCell = document.createElement('div');
    actionsCell.className = 'table-actions-cell';
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
        const cell = document.createElement('textarea');
        cell.maxLength = 255;
        cell.className = 'table-content-cell';

        tableRow.appendChild(cell);
    }
}