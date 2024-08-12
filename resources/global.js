/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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

var templateconfirms = {};
$(".jsconfirm").click(function() {
    var template = $(this).data('template');
    if(template !== undefined && template in templateconfirms) {
        return confirm(templateconfirms[template]);
    } else {
        return true;
    }
});

$(".password-strength").keyup(function () {
    var strength = zxcvbn($(this).val());
    var score = strength.score;

    if (strength.password.length < 8) {
        if(strength.feedback.warning === "") {
            strength.feedback.warning = "Password is too short";
        }

        if(score > 2) {
            score--;
        }
    }

    var bg = "bg-danger";
    if (score > 2) {
        bg = "bg-warning";
    }
    if (score > 3) {
        bg = "bg-success";
    }

    $("#password-strength-bar")
        .removeClass()
        .addClass("progress-bar")
        .addClass("w-" + (Math.max(score, 0) * 25))
        .addClass(bg);

    $('#password-strength-warning').text(strength.feedback.warning);
});

$(".sitenotice-dismiss").click(function() {
    let siteNoticeContainer = $(".sitenotice-container");
    siteNoticeContainer.removeClass('d-block');
    siteNoticeContainer.addClass('d-none');

    let siteNoticeUnhide = $(".sitenotice-show");
    siteNoticeUnhide.removeClass('d-none');

    var date = new Date();
    date.setTime(date.getTime() + 14 * 24 * 60 * 60 * 1000);

    document.cookie = 'sitenotice=' + siteNoticeContainer.data('sitenotice') + ";expires=" + date.toUTCString() + ";path=/";
})

$(".sitenotice-show").click(function() {
    let siteNoticeContainer = $(".sitenotice-container");
    siteNoticeContainer.removeClass('d-none');
    siteNoticeContainer.addClass('d-block');

    let siteNoticeUnhide = $(".sitenotice-show");
    siteNoticeUnhide.addClass('d-none');

    var date = new Date();
    date.setTime(date.getTime() - 1);

    document.cookie = 'sitenotice=invalid;expires=' + date.toUTCString() + ";path=/";
})

$(".request-table th").click(function() {
    let expiryDate = new Date();
    let sort = '';

    let classes = $(this).attr('class').split(/\s+/);
    if( $.inArray('sorted', classes )) {
        // is sorted
        expiryDate.setTime(expiryDate.getTime() + 365 * 24 * 60 * 60 * 1000);
        sort = $(this).data('sortname');

        if (classes.includes("up")) {
            sort += "/asc";
        } else if (classes.includes("down")) {
            sort += "/desc";
        } else {
            sort += "/asc";
        }
    } else {
        expiryDate.setTime(expiryDate.getTime() - 1);
    }

    document.cookie = 'request_table_sort=' + sort + ';expires=' + expiryDate.toUTCString() + ";path=/"
})


$("#banAction").change(function() {
    var selectedOption = $(this).children("option:selected").val();

    if (selectedOption === 'defer') {
        $("#banDeferTargetSelection").removeClass('d-none');
        $("#banGlobal").prop('checked', false).prop('disabled', true);
    } else {
        $("#banDeferTargetSelection").addClass('d-none');
        $("#banGlobal").prop('disabled', false);
    }
})

$("#banDuration").change(function() {
    var selectedOption = $(this).children("option:selected").val();

    if (selectedOption === 'other') {
        $("#banCustomDurationSelection").removeClass('d-none');
    } else {
        $("#banCustomDurationSelection").addClass('d-none');
    }
})

$("#commentVisibilityDropdown").on("change", "input[type='radio']", function() {
    if($(this).val() == 'user') {
        $("#commentVisibilityButton").addClass('btn-outline-secondary').removeClass('btn-danger').removeClass('btn-visited');
        $("#commentVisibilityIcon").removeClass('fa-lock').addClass('fa-lock-open');
    }
    if($(this).val() == 'admin') {
        $("#commentVisibilityButton").removeClass('btn-outline-secondary').addClass('btn-danger').removeClass('btn-visited');
        $("#commentVisibilityIcon").removeClass('fa-lock-open').addClass('fa-lock');
    }
    if($(this).val() == 'checkuser') {
        $("#commentVisibilityButton").removeClass('btn-outline-secondary').removeClass('btn-danger').addClass('btn-visited');
        $("#commentVisibilityIcon").removeClass('fa-lock-open').addClass('fa-lock');
    }
});
$(document).on('click', '.allow-focus', function (e) {
    e.stopPropagation();
});