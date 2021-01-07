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

function removeSpecialCharsFromUrlParameter(parameter) {
    if (!!parameter) {
        parameter = Object.keys(SPECIAL_CHARS)
            .reduce((acc, cur) => acc.replace(new RegExp(SPECIAL_CHARS[cur], 'g'), cur), parameter)
            .replaceAll(/"/g, '')
            .replaceAll(/'/g, '');

        return !!parameter ? parameter.toLowerCase() : '';
    }

    return '';
}