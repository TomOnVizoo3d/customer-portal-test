const licenseSpring = {
    cancelLicense: (id, nonce) => {
        sendData = {
            action: "vizoo_licensespring_get_cancellation_form",
            nonce: nonce,
            vizoo_licensespring_license_id: id,
        };
        jQuery.ajax({
            beforeSend: () => {
                licenseSpring.openLicensePopup(
                    "No more materials to digitize?",
                );
            },
            data: sendData,
            success: (msg) => {
                licenseSpring.setLicensePopupContent(msg);
            },
            type: "POST",
            url: jQuery("#ajax_url").val(),
        });
    },
    changeExtensionPeriod: (elem) => {
        const magicNumber3 = 3;
        const years = clamp(
            Number.parseInt(jQuery(elem).val(), 10) || 1,
            1,
            magicNumber3,
        );
        const newDate = new Date(
            jQuery("#vizoo-licensespring-license-info-expiration-date").val(),
        );
        newDate.setFullYear(newDate.getFullYear() + years);
        jQuery("#vizoo-licensespring-license-renewal-new-date").html(
            formatDate(newDate),
        );

        licenseSpring.recalculatePrice();
    },
    changeRenewalRequestType: (element) => {
        switch (element.value) {
            case "invoice": {
                document.querySelector(
                    "#vizoo-licensespring-license-popup-main-action-label",
                ).innerText = "Request invoice";
                document.querySelector(
                    "#vizoo-licensespring-license-renewal-po",
                ).style.display = "block";
                document.querySelector(
                    "#vizoo-licensespring-license-renewal-vat",
                ).style.display = "flex";
                document.querySelector(
                    "#vizoo-licensespring-license-popup-disclaimer-quote",
                ).style.display = "none";
                document.querySelector(
                    "#vizoo-licensespring-license-popup-disclaimer-invoice",
                ).style.display = "block";
                break;
            }
            case "quote":
            default: {
                document.querySelector(
                    "#vizoo-licensespring-license-popup-main-action-label",
                ).innerText = "Get quote";
                document.querySelector(
                    "#vizoo-licensespring-license-renewal-po",
                ).style.display = "none";
                document.querySelector(
                    "#vizoo-licensespring-license-renewal-vat",
                ).style.display = "none";
                document.querySelector(
                    "#vizoo-licensespring-license-popup-disclaimer-quote",
                ).style.display = "block";
                document.querySelector(
                    "#vizoo-licensespring-license-popup-disclaimer-invoice",
                ).style.display = "none";
                break;
            }
        }
    },
    clamp: (num, min, max) => {
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
    },
    closeLicensePopup: () => {
        jQuery("#vizoo-licensespring-license-popup-wrapper").hide();
        jQuery("#vizoo-licensespring-license-popup-title").empty();
        licenseSpring.resetLicensePopup();
    },
    confirmCancellation: (id, nonce) => {
        const comment = jQuery(
            "#vizoo-licensespring-license-cancellation-comment",
        ).val();

        const sendData = {
            action: "vizoo_licensespring_cancel_license",
            nonce: nonce,
            vizoo_licensespring_license_cancel_comment: comment,
            vizoo_licensespring_license_cancel_id: id,
        };

        jQuery.ajax({
            beforeSend: () => {
                licenseSpring.resetLicensePopup();
            },
            data: sendData,
            success: (msg) => {
                if (msg === "200") {
                    jQuery(
                        `#vizoo-licensespring-license-renewal-notice_${id}`,
                    ).html(
                        '<i class="fa fa-info-circle fa-fw"></i> Your cancellation request is being processed.',
                    );
                    jQuery(
                        `#vizoo-licensespring-license-actions_${id}`,
                    ).empty();
                    licenseSpring.closeLicensePopup();
                } else {
                    licenseSpring.closeLicensePopup();
                    // eslint-disable-next-line no-alert
                    alert(
                        `We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support. Error-Code: ${msg}`,
                    );
                }
            },
            type: "POST",
            url: jQuery("#ajax_url").val(),
        });
    },
    confirmRenew: (id, nonce) => {
        const form = document.getElementById(
            "vizoo-licensespring-license-popup-form",
        );
        const formData = new FormData(form);
        formData.append("action", "vizoo_licensespring_renew_license");
        formData.append("nonce", nonce);
        formData.append("vizoo_licensespring_license_renewal_id", id);
        const price = document.getElementById(
            "vizoo_licensespring_license_renewal_price",
        ).innerText;
        formData.append("vizoo_licensespring_price", price);

        jQuery.ajax({
            beforeSend: () => {
                licenseSpring.resetLicensePopup();
            },
            contentType: false,
            data: formData,
            error: (error) => {
                console.log(error);
                licenseSpring.closeLicensePopup();
                // eslint-disable-next-line no-alert
                alert(
                    "We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support.",
                );
            },
            processData: false,
            success: (msg) => {
                if (msg === "200") {
                    jQuery(
                        `#vizoo-licensespring-license-renewal-notice_${id}`,
                    ).html(
                        '<i class="fa fa-info-circle fa-fw"></i> Your renewal request is being processed.',
                    );
                    jQuery(
                        `#vizoo-licensespring-license-actions_${id}`,
                    ).empty();
                    licenseSpring.closeLicensePopup();
                } else {
                    licenseSpring.closeLicensePopup();
                    // eslint-disable-next-line no-alert
                    alert(
                        `We're sorry, but your request could not be processed. Please try again later. If the problem persists, please contact our support. Error-Code: ${msg}`,
                    );
                }
            },
            type: "POST",
            url: jQuery("#ajax_url").val(),
        });
    },
    /** Email Address Regular Expression, https://emailregex.com/ - but removed two not needed escapes */
    emailRegex:
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,

    fetchLicenses: () => {
        jQuery.ajax({
            beforeSend: () => {
                jQuery("#vizoo-licensespring-overview-loader").show();
            },
            data: {
                action: "vizoo_licensespring_get_licenses",
                nonce: jQuery("#vizoo_licensespring_get_licenses").val(),
            },
            method: "post",
            success: (html) => {
                jQuery("#vizoo-licensespring-overview-loader").hide();
                jQuery("#vizoo-licensespring-licenses-container").html(html);
            },
            url: jQuery("#ajax_url").val(),
        });
    },
    formatDate: (date) => {
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
    },
    openLicensePopup: (title) => {
        licenseSpring.resetLicensePopup();
        jQuery("#vizoo-licensespring-license-popup-title").html(title);
        jQuery("#vizoo-licensespring-license-popup-wrapper").show();
    },
    recalculatePrice: () => {
        const discounts = {
            1: 1,
            2: 0.95,
            3: 0.9,
        };

        let price = Number.parseInt(
            jQuery("#vizoo-licensespring-license-info-renewal-price").val(),
            10,
        );
        const currency = jQuery(
            "#vizoo-licensespring-license-info-renewal-currency",
        ).val();
        const years = Number.parseInt(
            jQuery("#vizoo-licensespring-license-renewal-period").val(),
            10,
        );
        const discount = discounts[years];

        price = years * price * discount;
        price = Math.floor(price);

        jQuery("#vizoo_licensespring_license_renewal_price").html(
            `${price.toFixed()} ${currency}`,
        );
    },
    renewLicense: (id, nonce) => {
        sendData = {
            action: "vizoo_licensespring_get_renewal_form",
            nonce: nonce,
            vizoo_licensespring_license_id: id,
        };
        jQuery.ajax({
            beforeSend: () => {
                licenseSpring.openLicensePopup("Renew license");
            },
            data: sendData,
            success: (msg) => {
                licenseSpring.setLicensePopupContent(msg);
            },
            type: "POST",
            url: jQuery("#ajax_url").val(),
        });
    },
    resetLicensePopup: () => {
        jQuery("#vizoo-licensespring-license-popup-content").empty();
        jQuery("#vizoo-licensespring-license-popup-loader").show();
    },
    setLicensePopupContent: (content) => {
        jQuery("#vizoo-licensespring-license-popup-loader").hide();
        jQuery("#vizoo-licensespring-license-popup-content").html(content);
    },
    toggleShowUsers: (id) => {
        const container = document.querySelector(`#license-users-${id}`);
        const renewBtn = document.querySelector(`#renew-link-${id}`);
        const cancelBtn = document.querySelector(`#cancel-link-${id}`);
        if (renewBtn && cancelBtn) {
            if (container.classList.contains("show-users")) {
                container.classList.remove("show-users");
                cancelBtn.disabled = false;
                renewBtn.disabled = false;
            } else {
                container.classList.add("show-users");
                cancelBtn.disabled = true;
                renewBtn.disabled = true;
            }
        }
    },
    updateFile: (element) => {
        const fileName = element.files[0].name;
        const targetElement = document.querySelector(
            "#vizoo-licensespring-license-renewal-upload",
        );
        targetElement.style.backgroundColor = "#00bfbf";
        licenseSpring.validateField(element);
        targetElement.innerHTML = `<span>${fileName}</span>`;
    },
    validateField: (element, single = false) => {
        if (
            element.attributes.name.nodeValue ===
            "vizoo-licensespring-license-renewal-po-file"
        ) {
            const binaryBase = 1024;
            const multiplier5 = 5;
            if (
                element.files.length === 1 &&
                element.files[0].size > multiplier5 * binaryBase * binaryBase
            ) {
                if (!single)
                    document.querySelector(
                        "#vizoo-licensespring-license-renewal-upload",
                    ).style.backgroundColor = "red";
                document.querySelector(
                    "#vizoo-licensespring-license-renewal-button-confirm",
                ).disabled = true;
                return false;
            }
            if (single) return true;
            licenseSpring.validateForm();
            return;
        }
        if (element.value.trim().length === 0) {
            if (!single) element.style.borderColor = "red";
            document.querySelector(
                "#vizoo-licensespring-license-renewal-button-confirm",
            ).disabled = true;
            return false;
        }
        if (
            element.attributes.name.nodeValue ===
                "vizoo_licensespring_contact_mail" &&
            !licenseSpring.emailRegex.test(element.value)
        ) {
            if (!single) element.style.borderColor = "red";
            document.querySelector(
                "#vizoo-licensespring-license-renewal-button-confirm",
            ).disabled = true;
            return false;
        }
        if (
            !single &&
            element.attributes.name.nodeValue !==
                "vizoo-licensespring-license-renewal-po-file"
        )
            element.style.borderColor = "";
        if (single) {
            return true;
        }
        licenseSpring.validateForm();
    },
    validateForm: () => {
        for (const entry of document.querySelectorAll(".needs-validation")) {
            if (!licenseSpring.validateField(entry, true)) {
                console.warn(
                    `validation failed for field ${entry.attributes.name.nodeValue}`,
                );
                return;
            }
        }
        document.querySelector(
            "#vizoo-licensespring-license-renewal-button-confirm",
        ).disabled = false;
    },
};

addEventListener("load", () => {
    licenseSpring.fetchLicenses();
});
