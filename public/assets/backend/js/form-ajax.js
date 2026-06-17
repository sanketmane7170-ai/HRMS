$("body").on("submit", ".ajax-form-submit", function (e) {
    e.preventDefault();
    clearError();
    let form = $(this),
        modal = form.closest(".modal");
    var action = form.attr("action"),
        method = form.attr("method");
           $('button[type="submit"]').prop('disabled', true);

    var fdata = new FormData(this);
    $.ajax({
        url: action,
        method: method,
        data: fdata,
        contentType: false,
        processData: false,
        beforeSend: function () {
            toggleLoader();
        },
        success: function (response) {
            if (response.success) {
                if (response.message) {
                    showAlert(response.message, "success");
                }

                if (response.redirect) {
                    console.log("redirect", response.redirect);
                    window.location.href = response.redirect;
                } 
                if (response.download_url) {
                    window.open(response.download_url, '_blank');
                }
                afterResponse(form, response);
                $(modal).modal("hide");
                // Refresh the page after a delay of 3 seconds
                   location.reload();//reload after store data
                // 3000 milliseconds = 3 seconds
                $('button[type="submit"]').prop('disabled', false);

            } else {
                showAlert(response.message, "error");
           $('button[type="submit"]').prop('disabled', false);

            }
        },
        error: function (e) {
            showFormError(e, form);
           $('button[type="submit"]').prop('disabled', false);

        },
    });
    toggleLoader();
});

$("body").on("submit", ".ajax-form-submit_assettype", function (e) {
    e.preventDefault();
    clearError();
    let form = $(this),
        modal = form.closest(".modal");
    var action = form.attr("action"),
        method = form.attr("method");
    var fdata = new FormData(this);
    $.ajax({
        url: action,
        method: method,
        data: fdata,
        contentType: false,
        processData: false,
        beforeSend: function () {
            toggleLoader();
        },
        success: function (response) {
            if (response.success) {
                if (
                    typeof response.results.id != "undefined" &&
                    response.results.id !== null
                ) {
                    $("#asset_type_id").append(
                        '<option value="' +
                            response.results.id +
                            '">' +
                            response.results.name +
                            "</option>"
                    );
                }
                if (response.message) {
                    showAlert(response.message, "success");
                }
                afterResponse(form, response);
                $(modal).modal("hide");
            } else {
                showAlert(response.message, "error");
            }
        },
        error: function (e) {
            showFormError(e, form);
        },
    });
    toggleLoader();
});

$("body").on("submit", ".ajax-form-submit_addmanufacture", function (e) {
    e.preventDefault();
    clearError();
    let form = $(this),
        modal = form.closest(".modal");
    var action = form.attr("action"),
        method = form.attr("method");
    var fdata = new FormData(this);
    $.ajax({
        url: action,
        method: method,
        data: fdata,
        contentType: false,
        processData: false,
        beforeSend: function () {
            toggleLoader();
        },
        success: function (response) {
            if (response.success) {
                if (
                    typeof response.results.id != "undefined" &&
                    response.results.id !== null
                ) {
                    $("#asset_manufacturer_id").append(
                        '<option value="' +
                            response.results.id +
                            '">' +
                            response.results.name +
                            "</option>"
                    );
                }
                if (response.message) {
                    showAlert(response.message, "success");
                }
                afterResponse(form, response);
                $(modal).modal("hide");
            } else {
                showAlert(response.message, "error");
            }
        },
        error: function (e) {
            showFormError(e, form);
        },
    });
    toggleLoader();
});

$("body").on("click", ".edit-button", function (event) {
    event.preventDefault();
    toggleLoader();
    var src = $(this).attr("href");
    var $method = $(this).attr("method") ? $(this).attr("method") : "GET";
    $.ajax({
        url: src,
        method: $method,
        success: function (response) {
            if (response.success) {
                if (response.html) {
                    $("#editModal").html(response.html).modal("show");
                }
            } else {
                showAlert(response.message, "error");
            }
            toggleLoader();
        },
        error: function (error) {
            toggleLoader();
            showAlert(error.responseJSON.message, "error");
        },
    });
});

$("body").on("click", ".action-button", function (event) {
    event.preventDefault();
    var $text = $(this).data("alert")
        ? $(this).data("alert")
        : "Are you sure delete this record?";
    var $button = $(this);
    var $method = $(this).attr("method") ? $(this).attr("method") : "DELETE";
    var src = $(this).attr("href");
    Swal.fire({
        title: $text,
        // text: "You won't be able to revert this!",
        showCancelButton: !0,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
    }).then(function (t) {
        if (t.value) {
            toggleLoader();
            $.ajax({
                url: src,
                method: "POST",
                data: {
                    _method: $method,
                },
                success: function (response) {
                    if (response.success) {
                        if (response.message) {
                            showAlert(response.message, "success");
                        }
                        afterResponse($button, response);
                        // Refresh the page after a delay of 3 seconds
                        location.reload(); // [ Full calendar reload and other all ajax pareller reload]
                        // 3000 milliseconds = 3 seconds
                    } else {
                        showAlert(response.message, "error");
                    }
                    toggleLoader();
                },
                error: function (e) {
                    showFormError(e, $(this));
                    toggleLoader();
                },
            });
        }
    });
});

function afterResponse(selector, response) {
    if (selector.attr("redirect") !== undefined) {
        location.replace(response.redirect);
    }
    if (selector.attr("html") !== undefined) {
        $(selector.attr("html")).html(response.html);
    }
    if (selector.attr("replace") !== undefined) {
        $(selector.attr("replace")).replaceWith(response.html);
    }
    if (selector.attr("datatable") !== undefined) {
        if (typeof table !== "undefined") {
            table.draw();
        }
    }

    //// if has reset class reset form
    if (selector.hasClass("reset")) {
        selector[0].reset();
    }
}

function clearError() {
    $(".invalid-feedback").remove();
    $(".form-control").removeClass("is-invalid");
    $(".form-select").removeClass("is-invalid");
}

function showFormError(errorResponse, selector) {
    if (errorResponse.status === 422) {
        var response = $.parseJSON(errorResponse.responseText);
        $.each(response.errors, function (key, val) {
            selector.find('[name="' + key + '"]').addClass("is-invalid");
            selector
                .find('[name="' + key + '"]:first')
                .parent()
                .append(
                    ' <span class="invalid-feedback" role="alert"><strong>' +
                        val +
                        "</strong></span>"
                );
        });
    }
    if (errorResponse.status == 403) {
        showAlert(errorResponse.responseJSON.message, "error");
    }
}
$("body").on("click", ".add_asset_type", function (event) {
    event.preventDefault();
    toggleLoader();
    var src = $(this).attr("href");
    var input_value = $(this).attr("input-value");
    var $method = $(this).attr("method") ? $(this).attr("method") : "GET";
    $.ajax({
        url: src,
        method: $method,
        success: function (response) {
            if (response.success) {
                if (response.html) {
                    $("#add_asset_type_modal")
                        .html(response.html)
                        .modal("show");
                    $(".input_value").val(input_value);
                }
            } else {
                showAlert(response.message, "error");
            }
            toggleLoader();
        },
        error: function (error) {
            toggleLoader();
            showAlert(error.responseJSON.message, "error");
        },
    });
});
$("body").on("click", ".add_manufacturer", function (event) {
    event.preventDefault();
    toggleLoader();
    var src = $(this).attr("href");
    var input_value = $(this).attr("input-value");
    var $method = $(this).attr("method") ? $(this).attr("method") : "GET";
    $.ajax({
        url: src,
        method: $method,
        success: function (response) {
            if (response.success) {
                if (response.html) {
                    $("#add_manufacturer_modal")
                        .html(response.html)
                        .modal("show");
                    $(".input_value").val(input_value);
                }
            } else {
                showAlert(response.message, "error");
            }
            toggleLoader();
        },
        error: function (error) {
            toggleLoader();
            showAlert(error.responseJSON.message, "error");
        },
    });
});
//// added ajax base select2
//// example usage  data-target="{{route('ajax.select2.fetch.designations')}}" data-dependent="nationality,name"
function loadAssetType() {
    $(".asset_type_selection").select2({
        // allowClear: true,
        language: {
            noResults: function () {
                const target_url = $(".asset_type_selection").data("setpath");
                var input_value = $(".select2-search__field").val();
                return $(
                    '<span>No Result Found <a class="add_asset_type" input-value="' +
                        input_value +
                        '" href="' +
                        target_url +
                        '">Click Here For Add</a></span>'
                );
            },
        },
        ajax: {
            url: function () {
                return $(this).data("target");
            },
            data: function (params) {
                var query = {};
                query.search = params.term;
                var dependent = $(this).data("dependent");
                if (typeof dependent !== "undefined") {
                    var dependentArray = dependent.split(",");
                    dependentArray.forEach((element) => {
                        var dependent = $('select[name="' + element + '"]');
                        if (dependent.length > 0 && dependent.val()) {
                            query[element] = dependent.val();
                        }
                    });
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response.data,
                };
            },
        },
    });
}
function loadAssetManufacturer() {
    $(".asset_manufacturer_selection").select2({
        // allowClear: true,
        language: {
            noResults: function () {
                const target_url = $(".asset_manufacturer_selection").data(
                    "setpath"
                );
                var input_value = $(".select2-search__field").val();
                return $(
                    '<span>No Result Found <a class="add_asset_type" input-value="' +
                        input_value +
                        '" href="' +
                        target_url +
                        '">Click Here For Add</a></span>'
                );
            },
        },
        ajax: {
            url: function () {
                return $(this).data("target");
            },
            data: function (params) {
                var query = {};
                query.search = params.term;
                var dependent = $(this).data("dependent");
                if (typeof dependent !== "undefined") {
                    var dependentArray = dependent.split(",");
                    dependentArray.forEach((element) => {
                        var dependent = $('select[name="' + element + '"]');
                        if (dependent.length > 0 && dependent.val()) {
                            query[element] = dependent.val();
                        }
                    });
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response.data,
                };
            },
        },
    });
}
function loadAjaxSelect2() {
    $(".ajax-select2").select2({
        // allowClear: true,
        ajax: {
            url: function () {
                return $(this).data("target");
            },
            data: function (params) {
                var query = {};
                query.search = params.term;
                var dependent = $(this).data("dependent");
                if (typeof dependent !== "undefined") {
                    var dependentArray = dependent.split(",");
                    dependentArray.forEach((element) => {
                        var dependent = $('select[name="' + element + '"]');
                        if (dependent.length > 0 && dependent.val()) {
                            query[element] = dependent.val();
                        }
                    });
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response.data,
                };
            },
        },
    });
}
