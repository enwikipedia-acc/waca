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

// begin import from node_modules/@web-auth/webauthn-helper/src/useLogin.js
import {
    fetchEndpoint,
    preparePublicKeyCredentials,
    preparePublicKeyOptions,
} from '/node_modules/@web-auth/webauthn-helper/src/common.js';

const useLogin = ({actionUrl = '/login', actionHeader = {}, optionsUrl = '/login/options'}, optionsHeader = {}) => {
    return async (data) => {
        const optionsResponse = await fetchEndpoint(data, optionsUrl, optionsHeader);
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);
        const credentials = await navigator.credentials.get({publicKey});
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

const login = useLogin({
    actionUrl: './webauthn/action',
    optionsUrl: './webauthn/options'
});

const doLogin = () => {
    login({action: "login"})
        .then((response) => {
            console.log('Authentication success');
            document.querySelector('input[name=token]').value = response.token;
            document.querySelector('#loginForm').submit();
        })
        .catch((error) => {
            alert("Authentication failed.");
        });
};

document.querySelector('#webauthnAuthenticate').addEventListener('click', doLogin);
