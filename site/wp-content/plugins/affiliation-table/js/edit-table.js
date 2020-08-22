function updateActivePanel(activeNav) {
    if (activeNav === 'nav-edition') {
        document.querySelector('#nav-edition').classList.add('nav-tab-active');
        document.querySelector('#nav-overview').classList.remove('nav-tab-active');

        document.querySelector('#panel-edition').style.display = "block";
        document.querySelector('#panel-overview').style.display = "none";
    } else {
        document.querySelector('#nav-edition').classList.remove('nav-tab-active');
        document.querySelector('#nav-overview').classList.add('nav-tab-active');

        document.querySelector('#panel-edition').style.display = "none";
        document.querySelector('#panel-overview').style.display = "block";
    }
}