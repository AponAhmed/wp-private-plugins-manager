jQuery(document).ready(function () {
    if (pagenow == "themes") {
        jQuery(".wp-header-end").before("<button type='button' class='themes-update-check' onclick='clearCache(this,\"themes\")'>Check For Update</button>");
    }
    if (pagenow == "theme-install") {
        jQuery(".wp-header-end").before("<button type='button' class='themes-update-check' onclick='PrivateThemeBrowse(this)'>Private Theme</button>");
    }
})

function PrivateThemeBrowse(_this) {
    let btn = jQuery(_this);
    btn.find(".dashicons").remove();
    btn.prepend('<span class="dashicons dashicons-update loading"></span>');
    var data = {action: "BrowsePrivateThemes"};
    jQuery.post(updobj.ajax_url, data, function (response) {
        btn.find(".dashicons").remove();
        themeStoreView(response);
    });
}

function BrowsePrivatePlugins(_this) {
    let btn = jQuery(_this);
    btn.find(".dashicons").remove();
    btn.prepend('<span class="dashicons dashicons-update loading"></span>');
    var data = {action: "BrowsePrivatePlugins"};
    jQuery.post(updobj.ajax_url, data, function (response) {
        btn.find(".dashicons").remove();
        pluginStoreView(response);
    });
}

function themeStoreView(elements) {

}

function pluginStoreView(elements) {
    let htm = "<div class='private-plugin-store-wrap'>";

    htm += "<div class='plugin-store-header'>";
    htm += "    <h3 class='private-plugin-store-title'>Private Plugins</h3>";
    htm += "    <div class='plugin-search-wrap'><input type='text' onkeyup=\"searchPlugin(this)\" placeholder='Search' class='private-plugin-search'><span class='searchClear' onclick='searchClear(this)'>&times;</span></div>";
    htm += "    <span class='private-plugin-store-inner-close' onclick='removePrivatepluginBrowser()'>&times;</span>";
    htm += "</div>";

    htm += "<div class='private-plugin-store-inner'>" + elements + "</div>";
    htm += "</div>";
    jQuery('body').append(htm);
}

function removePrivatepluginBrowser() {
    jQuery(".private-plugin-store-wrap").remove();
}

function searchPlugin(_this) {
    let q = jQuery(_this).val();
    if (q != "") {
        jQuery('.searchClear').show();
        jQuery(".private-plugin-card").each(function () {
            var str = jQuery(this).attr('data-description');
            var r = new RegExp(q, "i");
            if (str.search(r) < 0) {
                jQuery(this).addClass('collapse');
            } else {
                jQuery(this).removeClass('collapse');
            }
        });
    } else {
        jQuery(".private-plugin-card").removeClass('collapse');
        jQuery('.searchClear').hide();
    }
}

function searchClear(_this) {
    jQuery(_this).hide();
    jQuery('.private-plugin-search').val('');
    jQuery(".private-plugin-card").removeClass('collapse');
}

function clearCache(_this, module) {
    let btn = jQuery(_this);
    btn.find(".dashicons").remove();
    btn.prepend('<span class="dashicons dashicons-update loading"></span>');
    var data = {action: "clearCache", module: module};
    jQuery.post(updobj.ajax_url, data, function (response) {
        btn.find(".dashicons").remove();
        window.location.reload();
    });
}

function downloadPrivatePlugin(slug, _this) {
    let btn = jQuery(_this);
    let card = jQuery(_this).closest(".private-plugin-card");
    btn.find(".dashicons").remove();
    btn.prepend('<span class="dashicons dashicons-update loading"></span>');
    var data = {action: "downloadPrivatePlugin", slug: slug};
    jQuery.post(updobj.ajax_url, data, function (response) {
        btn.find(".dashicons").remove();
        let obj = JSON.parse(response);
        //console.log(obj);
        if (obj.error) {
            card.prepend("<span class='error-message private-plugin-message'>" + obj.message + "</span>");
            setTimeout(function () {
                card.find(".private-plugin-message").remove();
            }, 1000);
        } else {
            card.prepend("<span class='success-message private-plugin-message'>" + obj.message + "</span>");
            setTimeout(function () {
                card.find(".private-plugin-message").remove();
                card.addClass('plugin-exist');
                card.find(".plugin-download-btn").removeClass("Install").addClass("Installed").html("Installed");
            }, 1500);
        }

    });
}