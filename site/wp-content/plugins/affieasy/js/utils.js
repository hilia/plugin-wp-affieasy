const SPECIAL_CHARS = {
    a: 'á|à|ã|â|À|Á|Ã|Â',
    e: 'é|è|ê|É|È|Ê',
    i: 'í|ì|î|Í|Ì|Î',
    o: 'ó|ò|ô|õ|Ó|Ò|Ô|Õ',
    u: 'ú|ù|û|ü|Ú|Ù|Û|Ü',
    c: 'ç|Ç',
    n: 'ñ|Ñ',
    '-': ' '
};

// Recalculate link depending on url and parameters
// event.data must contain url (base webshop url), parametersSelector (class of input parameters) and linkOverviewSelector (class of overview paragraph)
function recalculateLink(event) {
    if (event && event.data) {
        let data = event.data;
        let url = data.url;
        Array.from(document.querySelectorAll(event.data.parametersSelector)).forEach(input => {
            if (input && input.value) {
                const cleanedValue = removeSpecialCharsFromUrlParameter(input.value);
                input.value = cleanedValue;

                url = url.replace(`[[${input.dataset.parameter}]]`, cleanedValue);
            }
        });

        const pOverview = document.querySelector(event.data.linkOverviewSelector);
        if (pOverview) {
            pOverview.textContent = url;
        }
    }
}

// Replace special chars
function removeSpecialCharsFromUrlParameter(parameter) {
    return !!parameter ?
        Object.keys(SPECIAL_CHARS)
            .reduce((acc, cur) => acc.replace(new RegExp(SPECIAL_CHARS[cur], 'g'), cur), parameter)
            .replaceAll(/"/g, '')
            .replaceAll(/'/g, '')
            .replaceAll(/&/g, '')
            .replaceAll(/\?/g, '') :
        '';
}
