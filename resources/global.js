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
});

var requestLogs = $('.scroll-bottom');
if(requestLogs.length) {
    requestLogs.scrollTop(requestLogs[0].scrollHeight);
}

var typeaheaddata = [];
var substringMatcher = function () {
    return function findMatches(query, syncResults) {
        var matches, substrRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `query`
        substrRegex = new RegExp(query, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `query`, add it to the `matches` array
        $.each(typeaheaddata, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });

        syncResults(matches);
    };
};

$('.username-typeahead').typeahead(
    {
        hint: true,
        highlight: true,
        minLength: 1
    },
    {
        name: "username",
        source: substringMatcher()
    }
);

$(".creationTypeOptions input").change(function() {
    if($(this).val() === "manual") {
        $("#createManual").removeClass("d-none");
        $("#createOauth").addClass("d-none");
        $("#createBot").addClass("d-none");
    }
    if($(this).val() === "oauth") {
        $("#createManual").addClass("d-none");
        $("#createOauth").removeClass("d-none");
        $("#createBot").addClass("d-none");
    }
    if($(this).val() === "bot") {
        $("#createManual").addClass("d-none");
        $("#createOauth").addClass("d-none");
        $("#createBot").removeClass("d-none");
    }
});
