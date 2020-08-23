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
        document.querySelectorAll('.header-cell')
            .forEach(header => header.style.display = 'block');
    } else {
        document.querySelectorAll('.header-cell')
            .forEach(header => header.style.display = 'none');
    }
}

function addRow() {
    for (let i = 0; i < 4; i++) {
        const cell = document.createElement('textarea');
        cell.maxLength = 255;
        cell.className = 'content-cell';

        document.querySelector('.table-content').appendChild(cell);
    }
}