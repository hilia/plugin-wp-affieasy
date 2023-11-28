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
    
    
    // console.log('event.data.url : ', event.data.url);
    // console.log('event.data.parametersSelector : ', event.data.parametersSelector);

    if (event && event.data) {
        let data = event.data;
        console.log('data: ', data);
        let url = data.url;
        Array.from(document.querySelectorAll(data.parametersSelector)).forEach(input => {
            if (input) {
                let cleanedValue = '';

                if (input.value) {
                    console.log('input.value : ', input.value);
                    cleanedValue = removeSpecialCharsFromUrlParameter(input.value);
                    console.log('cleanedValue : ', cleanedValue);
                    url = url.replace(`[[${input.dataset.parameter}]]`, cleanedValue);
                }

                input.value = cleanedValue;
            }
        });

        console.log('url sortie : ', url);

        const pOverview = document.querySelector(data.linkOverviewSelector);
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
            .replaceAll(/'/g, '') :
        '   ';
}
