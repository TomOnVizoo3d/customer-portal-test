// Select all text from the license key field if it's clicked. (used in template)
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function selectText(elem) {
    if (document.body.createTextRange) {
        const range = document.body.createTextRange();
        range.moveToElementText(elem);
        range.select();
    } else if (window.getSelection) {
        const selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(elem);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

function setLicensePopupContent(content) {
    jQuery("#vizoo-limelm-license-popup-loader").hide();
    jQuery("#vizoo-limelm-license-popup-content").html(content);
}

function resetLicensePopup() {
    jQuery("#vizoo-limelm-license-popup-content").empty();
    jQuery("#vizoo-limelm-license-popup-loader").show();
}

function openLicensePopup(title) {
    resetLicensePopup();
    jQuery("#vizoo-limelm-license-popup-title").html(title);
    jQuery("#vizoo-limelm-license-popup-wrapper").show();
}

function closeLicensePopup() {
    jQuery("#vizoo-limelm-license-popup-wrapper").hide();
    jQuery("#vizoo-limelm-license-popup-title").empty();
    resetLicensePopup();
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function renewLicense(id, nonce) {
    sendData = {
        action: "vizoo_limelm_get_renewal_form",
        nonce: nonce,
        vizoo_limelm_license_id: id,
    };
    jQuery.ajax({
        beforeSend: function () {
            openLicensePopup("License Upgrade");
        },
        data: sendData,
        success: function (msg) {
            setLicensePopupContent(msg);
        },
        type: "POST",
        url: jQuery("#ajax_url").val(),
    });
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function confirmMigration(id, nonce) {
    const form = document.getElementById("vizoo-limelm-license-popup-form");
    const formData = new FormData(form);
    formData.append("action", "vizoo_limelm_migrate_license");
    formData.append("nonce", nonce);
    formData.append("vizoo_limelm_license_renewal_id", id);

    jQuery.ajax({
        beforeSend: function () {
            resetLicensePopup();
        },
        contentType: false,
        data: formData,
        error: function (err) {
            console.log(err);
            closeLicensePopup();
            // eslint-disable-next-line no-alert
            alert(
                "We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support.",
            );
        },
        processData: false,
        success: function (msg) {
            if (msg === "200") {
                jQuery(`#vizoo-limelm-license-renewal-notice_${id}`).html(
                    '<i class="fa fa-info-circle fa-fw"></i> Your renewal request is being processed.',
                );
                jQuery(`#vizoo-limelm-license-actions_${id}`).empty();
                closeLicensePopup();
            } else {
                closeLicensePopup();
                // eslint-disable-next-line no-alert
                alert(
                    `We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support. Error-Code: ${msg}`,
                );
            }
        },
        type: "POST",
        url: jQuery("#ajax_url").val(),
    });
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function confirmRenew(id, nonce) {
    const form = document.getElementById("vizoo-limelm-license-popup-form");
    const formData = new FormData(form);
    formData.append("action", "vizoo_limelm_renew_license");
    formData.append("nonce", nonce);
    formData.append("vizoo_limelm_license_renewal_id", id);
    const price = document.getElementById(
        "vizoo_limelm_license_renewal_price",
    ).innerText;
    formData.append("vizoo_limelm_price", price);

    jQuery.ajax({
        beforeSend: function () {
            resetLicensePopup();
        },
        contentType: false,
        data: formData,
        error: function (err) {
            console.log(err);
            closeLicensePopup();
            // eslint-disable-next-line no-alert
            alert(
                "We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support.",
            );
        },
        processData: false,
        success: function (msg) {
            if (msg === "200") {
                jQuery(`#vizoo-limelm-license-renewal-notice_${id}`).html(
                    '<i class="fa fa-info-circle fa-fw"></i> Your renewal request is being processed.',
                );
                jQuery(`#vizoo-limelm-license-actions_${id}`).empty();
                closeLicensePopup();
            } else {
                closeLicensePopup();
                // eslint-disable-next-line no-alert
                alert(
                    `We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support. Error-Code: ${msg}`,
                );
            }
        },
        type: "POST",
        url: jQuery("#ajax_url").val(),
    });
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function cancelLicense(id, nonce, license_type) {
    sendData = {
        action: "vizoo_limelm_get_cancellation_form",
        nonce: nonce,
        vizoo_limelm_license_id: id,
    };
    jQuery.ajax({
        beforeSend: function () {
            openLicensePopup(
                license_type === "CSWS"
                    ? "No more materials to digitize?"
                    : "No need for the latest updates?",
            );
        },
        data: sendData,
        success: function (msg) {
            setLicensePopupContent(msg);
        },
        type: "POST",
        url: jQuery("#ajax_url").val(),
    });
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function confirmCancellation(id, nonce) {
    const comment = jQuery("#vizoo-limelm-license-cancellation-comment").val();

    const sendData = {
        action: "vizoo_limelm_cancel_license",
        nonce: nonce,
        vizoo_limelm_license_cancel_comment: comment,
        vizoo_limelm_license_cancel_id: id,
    };

    jQuery.ajax({
        beforeSend: function () {
            resetLicensePopup();
        },
        data: sendData,
        success: function (msg) {
            if (msg === "200") {
                jQuery(`#vizoo-limelm-license-renewal-notice_${id}`).html(
                    '<i class="fa fa-info-circle fa-fw"></i> Your cancellation request is being processed.',
                );
                jQuery(`#vizoo-limelm-license-actions_${id}`).empty();
                closeLicensePopup();
            } else {
                closeLicensePopup();
                // eslint-disable-next-line no-alert
                alert(
                    `We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support. Error-Code: ${msg}`,
                );
            }
        },
        type: "POST",
        url: jQuery("#ajax_url").val(),
    });
}

function formatDate(date) {
    const months = {
        0: "Jan",
        1: "Feb",
        2: "Mar",
        3: "Apr",
        4: "May",
        5: "Jun",
        6: "Jul",
        7: "Aug",
        8: "Sep",
        9: "Oct",
        10: "Nov",
        11: "Dec",
    };

    const maxLength = 2;
    return `${String(date.getDate()).padStart(maxLength, "0")}-${months[date.getMonth()]}-${date.getFullYear()}`;
}

function clamp(num, min, max) {
    if (min > max) {
        return num;
    }
    if (num <= min) {
        return min;
    }
    if (num >= max) {
        return max;
    }
    return num;
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function changeRenewalRequestType(element) {
    switch (element.value) {
        case "invoice": {
            document.querySelector(
                "#vizoo-limelm-license-popup-main-action-label",
            ).innerText = "Request invoice";
            document.querySelector(
                "#vizoo-limelm-license-renewal-po",
            ).style.display = "block";
            document.querySelector(
                "#vizoo-limelm-license-renewal-vat",
            ).style.display = "flex";
            break;
        }
        case "quote":
        default: {
            document.querySelector(
                "#vizoo-limelm-license-popup-main-action-label",
            ).innerText = "Get quote";
            document.querySelector(
                "#vizoo-limelm-license-renewal-po",
            ).style.display = "none";
            document.querySelector(
                "#vizoo-limelm-license-renewal-vat",
            ).style.display = "none";
            break;
        }
    }
}

function recalculatePrice() {
    const discounts = {
        1: 1,
        2: 0.95,
        3: 0.9,
    };

    let price = Number.parseInt(
        jQuery("#vizoo-limelm-license-info-renewal-price").val(),
        10,
    );
    const currency = jQuery(
        "#vizoo-limelm-license-info-renewal-currency",
    ).val();
    const years = Number.parseInt(
        jQuery("#vizoo-limelm-license-renewal-period").val(),
        10,
    );
    const discount = discounts[years];

    price = years * price * discount;
    price = Math.floor(price);

    jQuery("#vizoo_limelm_license_renewal_price").html(
        `${price.toFixed()} ${currency}`,
    );
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function changeExtensionPeriod(elem) {
    const magicNumber3 = 3;
    const years = clamp(
        Number.parseInt(jQuery(elem).val(), 10) || 1,
        1,
        magicNumber3,
    );
    const newDate = new Date(
        jQuery("#vizoo-limelm-license-info-expiration-date").val(),
    );
    newDate.setFullYear(newDate.getFullYear() + years);
    jQuery("#vizoo-limelm-license-renewal-new-date").html(formatDate(newDate));

    recalculatePrice();
}

function validateForm() {
    for (const entry of document.querySelectorAll(".needs-validation")) {
        // due to recursion of the code not possible to fix without refactoring
        // eslint-disable-next-line no-use-before-define
        if (!validateField(entry, true)) {
            console.warn(
                `validation failed for field ${entry.attributes.name.nodeValue}`,
            );
            return;
        }
    }
    document.querySelector(
        "#vizoo-limelm-license-renewal-button-confirm",
    ).disabled = false;
}

function validateField(element, single = false) {
    if (
        element.attributes.name.nodeValue ===
        "vizoo-limelm-license-renewal-po-file"
    ) {
        // 5 * 1024 * 1024
        const limit5MB = 5242880;
        if (element.files.length === 1 && element.files[0].size > limit5MB) {
            if (!single)
                document.querySelector(
                    "#vizoo-limelm-license-renewal-upload",
                ).style.backgroundColor = "red";
            document.querySelector(
                "#vizoo-limelm-license-renewal-button-confirm",
            ).disabled = true;
            return false;
        }
        if (single) return true;
        validateForm();
        return;
    }
    if (element.value.trim().length === 0) {
        if (!single) element.style.borderColor = "red";
        document.querySelector(
            "#vizoo-limelm-license-renewal-button-confirm",
        ).disabled = true;
        return false;
    }
    if (
        element.attributes.name.nodeValue === "vizoo_limelm_contact_mail" &&
        !/([^\x00-\x2F\x3A-\x40\x5B-\x5F\x7B-\xBF\xD7\xD8\xF7\s]+)/g.test(
            element.value,
        )
    ) {
        if (!single) element.style.borderColor = "red";
        document.querySelector(
            "#vizoo-limelm-license-renewal-button-confirm",
        ).disabled = true;
        return false;
    }
    if (
        !single &&
        element.attributes.name.nodeValue !==
            "vizoo-limelm-license-renewal-po-file"
    )
        element.style.borderColor = "";
    if (single) {
        return true;
    }
    validateForm();
}

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function updateFile(element) {
    const fileName = element.files[0].name;
    const targetElement = document.querySelector(
        "#vizoo-limelm-license-renewal-upload",
    );
    targetElement.style.backgroundColor = "#00bfbf";
    validateField(element);
    targetElement.innerHTML = `<span>${fileName}</span>`;
}

// used by the template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function deactivateLicense(id, confirmed, license_id, nonce) {
    const deactivationField = jQuery(`#deactivation-cell-${id}`);

    // action has been confirmed, do it
    if (confirmed === 1) {
        const sendData = {
            action: "vizoo_limelm_deactivate_activation",
            nonce: nonce,
            vizoo_limelm_activation_deactivation_id: id,
            vizoo_limelm_activation_license_id: license_id,
        };

        jQuery.ajax({
            beforeSend: function () {
                deactivationField.html(
                    '<span class="delete-info">Deactivating..</span>',
                );
            },
            data: sendData,
            error: function () {
                // eslint-disable-next-line no-alert
                alert(
                    "We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support.",
                );
            },
            success: function (msg) {
                deactivationField.html(msg);
            },
            type: "POST",
            url: jQuery("#ajax_url").val(),
        });
    }

    // action was not confirmed, show confirmation
    else if (confirmed === 0) {
        deactivationField.html(
            `Confirm <i class="fa fa-lg fa-check fa-fw delete-check" onclick="deactivateLicense(${id}, 1, ${license_id}, '${nonce}');" title="Confirm"></i><i class="fa fa-lg fa-times fa-fw delete-cross" onclick="deactivateLicense(${id}, -1);" title="Cancel"></i>`,
        );
    }

    // action was cancelled, reset deactivation cell
    else {
        deactivationField.html(
            `<i class="fa fa-lg fa-times fa-fw delete-cross" onclick="deactivateLicense(${id}, 0 );" title="Deactivate this activation."></i>`,
        );
    }
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
function fetchLicenses(jq) {
    jQuery.ajax({
        beforeSend: function () {
            jQuery("#vizoo-limelm-overview-loader").show();
        },
        data: {
            action: "vizoo_limelm_get_licenses",
            nonce: jQuery("#vizoo_limelm_get_licenses").val(),
        },
        method: "post",
        success: function (html) {
            jQuery("#vizoo-limelm-overview-loader").hide();
            jQuery("#vizoo-limelm-licenses-container").html(html);
        },
        url: jQuery("#ajax_url").val(),
    });
}

jQuery(document).ready(fetchLicenses);
