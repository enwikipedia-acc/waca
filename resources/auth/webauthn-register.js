/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * This file re-uses code from web-auth/webauthn-helper                       *
 * Copyright (c) 2014-2019 Spomky-Labs                                        *
 * Released under The MIT Licence, see @web-auth/webauthn-helper/LICENCE      *
 *                                                                            *
 * All remaining code in this file is released into the public domain by the  *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 ******************************************************************************/

import {post} from './common.js'

// begin import from node_modules/@web-auth/webauthn-helper/src/useRegistration.js
import {
    fetchEndpoint,
    preparePublicKeyCredentials,
    preparePublicKeyOptions,
} from '/node_modules/@web-auth/webauthn-helper/src/common.js';

const useRegistration = ({actionUrl = '/register', actionHeader = {}, optionsUrl = '/register/options'}, optionsHeader = {}) => {
    return async (data) => {
        const optionsResponse = await fetchEndpoint(data, optionsUrl, optionsHeader);
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);
        const credentials = await navigator.credentials.create({publicKey});
        const publicKeyCredential = preparePublicKeyCredentials(credentials);
        const actionResponse = await fetchEndpoint(publicKeyCredential, actionUrl, actionHeader);
        if (! actionResponse.ok) {
            throw actionResponse;
        }
        const responseBody = await actionResponse.text();

        return responseBody !== '' ? JSON.parse(responseBody) : responseBody;
    };
};
// end import from node_modules/@web-auth/webauthn-helper/src/useRegistration.js

const register = useRegistration({
    actionUrl: './enableWebAuthn',
    optionsUrl: './enableWebAuthn'
});

const doRegistration = () => {
    var enrollment = document.querySelector('input[name=enrollment]').value;
    var tokenName = document.querySelector('input[name=authenticatorName]').value;

    register({enrollment: enrollment, tokenName: tokenName})
        .then((response) => {
            console.log('Registration success')
            post(window.location, {"stage": "success"});
        })
        .catch((error) => {
            console.log('Registration failure: ' + error);
            post(window.location, {"stage": "failure"});
        });
};

document.querySelector('#beginRegistration').addEventListener('click', doRegistration);
