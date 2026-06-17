/*
Author       : Dreamguys
Template Name: Kanakku - Bootstrap Admin Template
Version      : 1.0
*/
var rte = [];
var rteConfig =
    "{bold,underline,italic}|{forecolor,backcolor}|{justifyleft,justifycenter,justifyright,justifyfull}|{insertorderedlist,insertunorderedlist,indent,outdent}|{insertblockquote,insertemoji,selectall}|{paragraphs}{fontname}{fontsize}{inlinestyle}{lineheight}" +
    "{removeformat,cut,copy,paste,delete,find}";
var rteConfigWithSource =
    "|{insertlink,insertchars,inserttable,insertimage,insertvideo,insertcode}|#{undo,redo,fullscreenenter,fullscreenexit,togglemore}|{bold,underline,italic}|{forecolor}|{justifyleft,justifycenter,justifyright,justifyfull}|{insertorderedlist,insertunorderedlist,indent,outdent}|{paragraphs}{fontname}{fontsize}{inlinestyle}{lineheight}|{removeformat,cut,copy,paste,delete,find}|{code}";
(function ($) {
    "use strict";

    // Variables declarations
    var $wrapper = $(".main-wrapper");
    var $pageWrapper = $(".page-wrapper");
    var $slimScrolls = $(".slimscroll:not(.sidebar .slimscroll)"); // Exclude sidebar from JS scroller

    feather.replace();

    // Sidebar
    var Sidemenu = function () {
        this.$menuItem = $("#sidebar-menu a");
    };

    function init() {
        var $this = Sidemenu;
        $("#sidebar-menu a").on("click", function (e) {
            if ($(this).parent().hasClass("submenu")) {
                e.preventDefault();
            }
            if (!$(this).hasClass("subdrop")) {
                $("ul", $(this).parents("ul:first")).slideUp(350);
                $("a", $(this).parents("ul:first")).removeClass("subdrop");
                $(this).next("ul").slideDown(350);
                $(this).addClass("subdrop");
            } else if ($(this).hasClass("subdrop")) {
                $(this).removeClass("subdrop");
                $(this).next("ul").slideUp(350);
            }
        });
        // Auto-expand the active submenu on page load. This used to go through
        // the same path as a manual click - including its 350ms slideDown
        // animation - purely because the page navigated, not because anyone
        // clicked anything. Combined with the scroll fix below waiting on a
        // timeout, that meant up to ~750ms where the sidebar visibly sat at
        // the top before "jumping" to the right spot, which read as "it reset
        // to the top". Showing it instantly removes that wait entirely.
        $("#sidebar-menu ul li.submenu a.active")
            .parents("li:last")
            .children("a:first")
            .addClass("active subdrop")
            .next("ul")
            .show();

        // Bring the current page's sidebar item into view on load, so you
        // don't have to manually scroll to find where you are every time you
        // navigate (this matters a lot once submenus are expanded and the
        // active item can be far down the list). No animation, no delay -
        // since the submenu above is already expanded (not animating), the
        // final layout is known immediately.
        var activeEl = document.querySelector("#sidebar-menu a.active:last-of-type") ||
            [].slice.call(document.querySelectorAll("#sidebar-menu a.active")).pop();
        var sidebarEl = document.querySelector(".sidebar");
        if (activeEl && sidebarEl) {
            // getBoundingClientRect is relative to the viewport, not the nearest
            // positioned ancestor, so it stays accurate regardless of how deep the
            // active link is nested inside submenus.
            var sidebarRect = sidebarEl.getBoundingClientRect();
            var activeRect = activeEl.getBoundingClientRect();
            var deltaFromTop = activeRect.top - sidebarRect.top;
            var targetScroll = sidebarEl.scrollTop + deltaFromTop - sidebarRect.height / 2;
            sidebarEl.scrollTop = Math.max(0, targetScroll);
        }
    }

    // Sidebar Initiate
    init();

    // Mobile menu sidebar overlay
    $("body").append('<div class="sidebar-overlay"></div>');
    $(document).on("click", "#mobile_btn", function () {
        $wrapper.toggleClass("slide-nav");
        $(".sidebar-overlay").toggleClass("opened");
        $("html").addClass("menu-opened");
        return false;
    });

    // Sidebar overlay
    $(".sidebar-overlay").on("click", function () {
        $wrapper.removeClass("slide-nav");
        $(".sidebar-overlay").removeClass("opened");
        $("html").removeClass("menu-opened");
    });

    // Page Content Height
    if ($(".page-wrapper").length > 0) {
        var height = $(window).height();
        $(".page-wrapper").css("min-height", height);
    }

    // Page Content Height Resize
    $(window).resize(function () {
        if ($(".page-wrapper").length > 0) {
            var height = $(window).height();
            $(".page-wrapper").css("min-height", height);
        }
    });

    // Select 2
    if ($(".select").length > 0) {
        $(".select").select2({
            minimumResultsForSearch: -1,
            width: "100%",
        });
    }
    if ($(".select-search").length > 0) {
        $(".select-search").select2();
    }
    // Datetimepicker

    if ($(".datetimepicker").length > 0) {
        $(".datetimepicker").datetimepicker({
            format: "DD-MM-YYYY",
            icons: {
                up: "fas fa-angle-up",
                down: "fas fa-angle-down",
                next: "fas fa-angle-right",
                previous: "fas fa-angle-left",
            },
        });
    }

    // Tooltip
    if ($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Datatable
    if ($(".datatable").length > 0) {
        $(".datatable").DataTable({
            bFilter: false,
        });
    }

    // Sidebar Slimscroll
    if ($slimScrolls.length > 0) {
        $slimScrolls.slimScroll({
            height: "auto",
            width: "100%",
            position: "right",
            size: "7px",
            color: "#ccc",
            allowPageScroll: true, // Allow native bubbling
            wheelStep: 10,
            touchScrollStep: 100,
        });
        
        // Only apply manual height calculation for non-sidebar elements if needed
        $slimScrolls.not(".sidebar .slimscroll").each(function() {
            var wHeight = $(window).height() - 60;
            $(this).height(wHeight);
            $(this).parent(".slimScrollDiv").height(wHeight);
        });

        $(window).resize(function () {
            $slimScrolls.not(".sidebar .slimscroll").each(function() {
                var rHeight = $(window).height() - 60;
                $(this).height(rHeight);
                $(this).parent(".slimScrollDiv").height(rHeight);
            });
        });
    }

    // Password Show

    if ($(".toggle-password").length > 0) {
        $(document).on("click", ".toggle-password", function () {
            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $(".pass-input");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }

    // Check all email

    $(document).on("click", "#check_all", function () {
        $(".checkmail").click();
        return false;
    });
    if ($(".checkmail").length > 0) {
        $(".checkmail").each(function () {
            $(this).on("click", function () {
                if ($(this).closest("tr").hasClass("checked")) {
                    $(this).closest("tr").removeClass("checked");
                } else {
                    $(this).closest("tr").addClass("checked");
                }
            });
        });
    }

    // Mail important

    $(document).on("click", ".mail-important", function () {
        $(this).find("i.fa").toggleClass("fa-star").toggleClass("fa-star-o");
    });

    // Small Sidebar
    $(document).on("click", "#toggle_btn", function () {
        if ($("body").hasClass("mini-sidebar")) {
            $("body").removeClass("mini-sidebar");
            $(".subdrop + ul").slideDown();
        } else {
            $("body").addClass("mini-sidebar");
            $(".subdrop + ul").slideUp();
        }
        setTimeout(function () {
            mA.redraw();
            mL.redraw();
        }, 300);
        return false;
    });

    $(document).on("mouseover", function (e) {
        e.stopPropagation();
        if (
            $("body").hasClass("mini-sidebar") &&
            $("#toggle_btn").is(":visible")
        ) {
            var targ = $(e.target).closest(".sidebar").length;
            if (targ) {
                $("body").addClass("expand-menu");
                $(".subdrop + ul").slideDown();
            } else {
                $("body").removeClass("expand-menu");
                $(".subdrop + ul").slideUp();
            }
            return false;
        }
    });

    $(document).on("click", "#filter_search", function () {
        $("#filter_inputs").slideToggle("slow");
    });

    // Chat

    var chatAppTarget = $(".chat-window");
    (function () {
        if ($(window).width() > 991) chatAppTarget.removeClass("chat-slide");

        $(document).on(
            "click",
            ".chat-window .chat-users-list a.media",
            function () {
                if ($(window).width() <= 991) {
                    chatAppTarget.addClass("chat-slide");
                }
                return false;
            }
        );
        $(document).on("click", "#back_user_list", function () {
            if ($(window).width() <= 991) {
                chatAppTarget.removeClass("chat-slide");
            }
            return false;
        });
    })();
})(jQuery);

function clearError() {
    $(".invalid-feedback").remove();
    $(".form-control").removeClass("is-invalid");
}

function showValidationError(response, form) {
    $.each(response.errors, function (key, val) {
        form.find('[name="' + key + '"]').addClass("is-invalid");
        form.find('[name="' + key + '"]')
            .parent()
            .append(
                ' <span class="invalid-feedback" role="alert"><strong>' +
                    val +
                    "</strong></span>"
            );
    });
}

function previewImage(fileInput, previewFileLocation, action = "html") {
    html = "";
    var total_file = document.getElementById(fileInput).files.length;
    for (var i = 0; i < total_file; i++) {
        // html +=
        //     "<div class='col-md-3 mt-2'><img width=100 src='" +
        //     URL.createObjectURL(event.target.files[i]) +
        //     "'></div>";
        if (event.target.files[i].type === 'application/pdf') {
            html += "<div class='col-md-3 mt-2'><img width=100 src='"+pdfIconPath+"'></div>";
        } else {
            // Preview images for other file types
            html += "<div class='col-md-3 mt-2'><img width=100 src='" + URL.createObjectURL(event.target.files[i]) + "'></div>";
        }
    }
    if (action == "append") {
        $("#" + previewFileLocation).append(html);
    } else {
        $("#" + previewFileLocation).html(html);
    }
}

function previewImageOrPdf(fileInput, previewFileLocation, action = "html") {
    var html = "";
    var totalFiles = document.getElementById(fileInput).files.length;
    
    for (var i = 0; i < totalFiles; i++) {
        var file = document.getElementById(fileInput).files[i];
        console.log("file.type",file.type);
        if (file.type === 'application/pdf') {
            // Display static PDF image
            html += "<div class='col-md-3 mt-2'><img width=100 src='{{ asset('assets/backend/img/icon-pdf.svg') }}'></div>";
        } else {
            // Preview images for other file types
            html += "<div class='col-md-3 mt-2'><img width=100 src='" + URL.createObjectURL(file) + "'></div>";
        }
    }
    
    if (action == "append") {
        $("#" + previewFileLocation).append(html);
    } else {
        $("#" + previewFileLocation).html(html);
    }
}
function initTextEditor(ids,assetUrl=null) {
    for (i = 0; i < ids.length; i++) {
        var editorcfg = {};
        editorcfg.toolbar = "mytoolbar";
        editorcfg.toolbar_mytoolbar = rteConfig;
        if(assetUrl){
            editorcfg.url_base = assetUrl;
            console.log(assetUrl+"-"+ids[i]);
        }
         new RichTextEditor(document.getElementById(ids[i]),editorcfg);
    }
}
function initTextEditorWithSource(ids) {
    for (i = 0; i < ids.length; i++) {
        var editorcfg = {};
        editorcfg.toolbar = "mytoolbar";
        editorcfg.toolbar_mytoolbar = rteConfig + rteConfigWithSource;
        rte[ids[i]] = new RichTextEditor(
            document.getElementById(ids[i]),
            editorcfg
        );
    }
}

function clearCkeditorData() {
    for (instance in rte) {
        rte[instance].setHTMLCode("");
    }
}

function initselect2search() {
    $(".select-search").select2();
}

function initselect2() {
    $(".select").select2({
        width: "100%",
    });
}

function clearFormCkEditorInstance(form) {
    for (instance in rte) {
        if (form.find("#" + instance).length > 0) {
            rte[instance].setHTMLCode("");
        }
    }
}


/*Custom Scroll JS Starts*/
$(document).ready(function(){

    $(".att-wrapper1").scroll(function(){
        $(".att-wrapper2")
            .scrollLeft($(".att-wrapper1").scrollLeft());
    });
    $(".att-wrapper2").scroll(function(){
        $(".att-wrapper1")
            .scrollLeft($(".att-wrapper2").scrollLeft());
    });

});
/*Custom Scroll JS Ends*/
