/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

export const post = (url, parameters) => {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = url;

    for(const key in parameters) {
        if(parameters.hasOwnProperty(key)) {
            const input = document.createElement('input');
            input.type='hidden';
            input.name = key;
            input.value = parameters[key];
            form.appendChild(input);
        }
    }

    document.body.appendChild(form);
    form.submit();
}