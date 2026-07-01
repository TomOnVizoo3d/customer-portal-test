let prevHtml = "";
let prevTitle = "";
let prevTerm = "";

// TODO Check if used
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function vizooSearch(original_term, currentPage, isSearchPage) {
    let term = original_term;
    term = term.replace(/[^a-z0-9A-Z ]/g, "");
    const maxLength = 2;

    if (term === "" || (term.replace(/\s/g, "") === "" && !isSearchPage)) {
        if (prevHtml !== "") {
            jQuery("#main").html(prevHtml);
            document.title = prevTitle;
        }
    } else if (term.replace(/\s/g, "").length <= maxLength) {
        jQuery("#main").html(
            '<article><header><h2 class="entry-header">Please enter a valid search term.</h2></header><div class="entry-content"><p>Please provide us more than three letters, so we can search more effectively.</p><hr /></div></article>',
        );
        document.title = prevTitle;
    } else if (prevTerm !== term) {
        prevTerm = term;
        jQuery.ajax({
            beforeSend: function () {
                jQuery(".search-icon").hide();
                jQuery(".search-loading").show();
            },
            data: {
                action: "vizoo_search",
                vizoo_currentpage: currentPage,
                vizoo_searchterm: term,
            },
            method: "post",
            success: function (html) {
                jQuery("#main").html(html);
                jQuery("#searchquery").html(term);
                jQuery(".search-icon").show();
                jQuery(".search-loading").hide();
                document.title = `Search Results for “${term}” – Vizoo`;
            },
            url: ajax_object.ajax_url,
        });
    }
}

// TODO Check if used
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function searchValidation(elem) {
    jQuery(elem).val(
        jQuery(elem)
            .val()
            .replace(/[^a-zA-Z0-9\s]/g, ""),
    );
}

jQuery(document).ready(() => {
    prevHtml = jQuery("#main").html();
    prevTitle = document.title;
});
