/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

$(function () {
    $("[rel='popover']").popover();
});

$(".visit-tracking").mouseup(function() {
    $(this).addClass('btn-outline-visited');
})

