

function moSAMLUpgrade() {
    jQuery('a[href="#licensing-plans"]').click();
    add_css_tab("#licensingtab");
}
function close_custom_crt_modal() {
    jQuery("#my_TC_Modal").css("display", "none");
    jQuery("#plugin_tool_bar").css("z-index", "1000");
    location.reload();
}
function show_custom_crt_modal() {
    jQuery("#plugin_tool_bar").css("z-index", "0");
    jQuery("#my_custom_crt_modal").css("display", "block");

}
function add_css_tab(element) {
    // Remove active class from all navigation tabs
    jQuery(".mo_nav_tab_active").removeClass("mo_nav_tab_active").removeClass("active");

    // Add active class to clicked navigation tab
    jQuery(element).addClass("mo_nav_tab_active");

    // Hide all tab content
    jQuery(".tab-pane").removeClass("active").hide();

    // Get the target tab content ID from the href attribute
    var targetId = jQuery(element).attr("href");
    if (targetId) {
        // Show the target tab content
        jQuery(targetId).addClass("active").show();
    }
}

function resetProxy() {
    jQuery('#reset_proxy').submit();
}
function show_proxy_form() {
    jQuery('#submit_proxy').show();
    jQuery('#cum_pro').hide();
    jQuery('#sp_proxy_setup').hide();
}

function hide_proxy_form() {
    jQuery('#submit_proxy').hide();
    jQuery('#cum_pro').show();
    jQuery('#panel1').show();
    jQuery('#sp_proxy_setup').show();
}


function show_curl_msg() {
    jQuery('#help_curl_warning_desc').css("display", "block");
}
function close_curl_modal() {
    jQuery("#help_curl_warning_desc").css("display", "none");
}


function close_popup() {
    jQuery("#close_popup").submit();
}

function save_value() {
    jQuery("#do_not_show_again").submit();
}

function copyToClipboard(element) {
    jQuery(".selected-text").removeClass("selected-text");
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    jQuery(element).addClass("selected-text");
    temp.val(jQuery(element).text().trim()).select();
    document.execCommand("copy");
    temp.remove();
    jQuery(element).parent().siblings().children().children('.copied_text').text('Copied');
    jQuery(element).parent().parent().siblings().children().children('.copied_text').text('Copied');
    jQuery(element).siblings().children('.copied_text').text('Copied');
}

jQuery(window).click(function (e) {
    if (e.target.className === undefined || e.target.className.indexOf("fa-copy") === -1)
        jQuery(".selected-text").removeClass("selected-text");
});

jQuery(document).ready(function () {
    var basepath = window.location.href;
    basepath = basepath.substr(0, basepath.indexOf('administrator')) + 'plugins/authentication/miniorangesaml/';
    jQuery('.site-url').text(basepath);
    jQuery('.premium').click(function () {
        jQuery('.nav-tabs a[href="#attrib-licensing_plans"]').tab('show');
    });
});

var homepath = window.location.href;
var homepath = homepath.substr(0, homepath.indexOf('administrator'));
basepath = homepath + 'plugins/authentication/miniorangesaml/';
jQuery(document).ready(function () {
    jQuery('#metadata-link').attr('href', homepath + '?morequest=metadata');
});

function show_gen_cert_form() {
    jQuery("#generate_certificate_form").show();
    jQuery("#mo_gen_cert").hide();
    jQuery("#mo_gen_tab").hide();
}

function hide_gen_cert_form() {
    jQuery("#generate_certificate_form").hide();
    jQuery("#mo_gen_cert").show();
    jQuery("#mo_gen_tab").show();
}


jQuery(function () {
    jQuery("#idp_guides").change(function () {
        var selectedIdp = jQuery(this).find("option:selected").val();
        window.open(selectedIdp, "_blank");
    });
});

function showLink() {
    if (document.getElementById('login_link_check').checked)
        document.getElementById('dynamicText').style.display = 'block';
    else
        document.getElementById('dynamicText').style.display = 'none';
}

function showTestWindow() {

    var testconfigurl = jQuery('#test_config_url').val();
    document.getElementById('testarati').value = 'true';
    window.open(testconfigurl, 'TEST SAML IDP', 'width=800,height=600,scrollbars=yes');
}

function redirectToTrial() {
    var testconfigurl = window.location.href;
    baseUrl = testconfigurl.split('?')[0];
    baseUrl = baseUrl + '?option=com_miniorange_saml&tab=request_demo';
    window.location.href = baseUrl;
}


function show_metadata_form() {
    jQuery(".mo_saml_current_tab").removeClass("mo_saml_current_tab");
    jQuery("#auto_configuration").addClass("mo_saml_current_tab");
    jQuery('#upload_metadata_form').show();
    jQuery('#idpdata').hide();
}

function hide_metadata_form() {
    jQuery(".mo_saml_current_tab").removeClass("mo_saml_current_tab");
    jQuery("#manual_configuration").addClass("mo_saml_current_tab");
    jQuery('#upload_metadata_form').hide();
    jQuery('#idpdata').show();
}

function show_bundle() {
    if (jQuery("#bundle_checked").is(":checked")) {
        jQuery("#bundle_content").css("display", "block");
        jQuery("#license_content").css("display", "none");
    }
    else {
        jQuery("#bundle_content").css("display", "none");
        jQuery("#license_content").css("display", "block");
    }
}

function filterFunction() {
    jQuery('.dropdown_options').css("display", "block");
    var input, filter, ul, li, a, i;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    div = document.getElementById("myDropdown");
    a = div.getElementsByTagName("a");
    var c = 0;
    for (i = 0; i < a.length; i++) {
        jQuery('.mo_dropdown_options').css("height", "auto");
        txtValue = a[i].textContent || a[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            a[i].style.display = "";
            c++;
        } else {
            a[i].style.display = "none";
        }
    }
}


var homepath = window.location.href;
var homepath = homepath.substr(0, homepath.indexOf('administrator'));
basepath = homepath + 'plugins/authentication/miniorangesaml/';
jQuery(document).ready(function () {
    jQuery('#metadata-link').attr('href', homepath + '?morequest=metadata');

    var inputValue = "text_cert";
    var targetBox = jQuery("." + inputValue);
    jQuery(".selectt").not(targetBox).hide();
    jQuery(targetBox).show();

    jQuery('input[type="radio"]').click(function () {
        var inputValue = jQuery(this).attr("value");
        var targetBox = jQuery("." + inputValue);
        jQuery(".selectt").not(targetBox).hide();
        jQuery(targetBox).show();
    });


    jQuery('#select_idp').click(function () {
        jQuery('#select_idp').toggle();
        jQuery('#myInput').toggle();
        jQuery('#dropdown-test').toggle();
    });


    jQuery(".mo_sp_inclusive_plans").change(function () {
        jQuery("#plus_total_price_basic").css("display", "none");
        jQuery("#plus_total_price_pro").css("display", "none");
        jQuery("#plus_total_price_" + jQuery(this).val()).css("display", "block");
        if (jQuery(this).val() == 'basic') {
            jQuery("#mo_pricing_list4").css("display", "none");
            jQuery("#mo_pricing_list3").css("display", "none");
            jQuery("#mo_pricing_list2").css("display", "block");
            jQuery("#mo_pricing_list1").css("display", "block");
        } else {
            jQuery("#mo_pricing_list4").css("display", "block");
            jQuery("#mo_pricing_list3").css("display", "block");
            jQuery("#mo_pricing_list2").css("display", "none");
            jQuery("#mo_pricing_list1").css("display", "none");
        }
    });


    jQuery('#upload_metadata_file').click(function () {
        var file = document.getElementById("metadata_uploaded_file");
        if (file.files.length != 0) {
            jQuery('#IDP_meatadata_form').submit();
        } else {
            alert("Please uplod the metadata file");
            jQuery('#metadata_uploaded_file').attr('required', 'true');
            jQuery('#metadata_url').attr('required', 'false');
        }

    });

    jQuery('#fetch_metadata').click(function(){
        var url = jQuery("#metadata_url").val();
        if(url!='')
        {
            jQuery('#IDP_meatadata_form').submit(); 
        }
        else{
            alert("Please enter the metadata URL");
            jQuery('#metadata_url').attr('required','true');
            jQuery('#metadata_uploaded_file').attr('required','false');
        }
        
    });



});


function openTab(evt, vtabName) {
    var i, vtab_content, vtab_btn;
    vtab_content = document.getElementsByClassName("vtab_content");
    for (i = 0; i < vtab_content.length; i++) {
        vtab_content[i].style.display = "none";
    }
    vtab_btn = document.getElementsByClassName("vtab_btn");
    for (i = 0; i < vtab_btn.length; i++) {
        vtab_btn[i].className = vtab_btn[i].className.replace(" active", "");
    }
    document.getElementById(vtabName).style.display = "block";
    evt.currentTarget.className += " active";
}


// Licensing Page JavaScript Functions

// Function to show metadata tabs
function showMetadataTab(tabName, event) {
    // Prevent default link behavior (page scroll to top)
    if (event) {
        event.preventDefault();
    }

    // Hide all metadata tab contents
    var tabContents = document.querySelectorAll('.metadata-tab-content');
    tabContents.forEach(function (content) {
        content.style.display = 'none';
    });

    // Remove active class from all metadata tab buttons only
    var tabButtons = document.querySelectorAll('#metadata-url-tab-btn, #download-xml-tab-btn, #manual-info-tab-btn');
    tabButtons.forEach(function (button) {
        button.classList.remove('mo_saml_current_tab');
    });

    // Add active class to selected metadata tab button
    var selectedButton = document.getElementById(tabName + '-tab-btn');
    if (selectedButton) {
        selectedButton.classList.add('mo_saml_current_tab');
    }

    // Show the selected tab content
    var selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }
}

// Function to show licensing tabs (separate from metadata tabs)
function showLicensingTab(tabName, event) {
    // Prevent default link behavior (page scroll to top)
    if (event) {
        event.preventDefault();
    }

    // Hide all licensing tab contents
    var tabContents = document.querySelectorAll('.licensing-tab-content');
    tabContents.forEach(function (content) {
        content.style.display = 'none';
    });

    // Remove active class from all licensing tab buttons only
    var tabButtons = document.querySelectorAll('#plans-tab-btn, #bundle-plans-tab-btn');
    tabButtons.forEach(function (button) {
        button.classList.remove('mo_saml_current_tab');
    });

    // Add active class to selected licensing tab button
    var selectedButton = document.getElementById(tabName + '-tab-btn');
    if (selectedButton) {
        selectedButton.classList.add('mo_saml_current_tab');
    }

    // Show the selected tab content
    var selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }
}

// Function to toggle feature lists
function toggleFeatures(featureId) {
    var featureList = document.getElementById(featureId + '-list');

    if (featureList.style.display === 'none' || featureList.style.display === '') {
        featureList.style.display = 'block';
    } else {
        featureList.style.display = 'none';
    }
}

// Function to toggle bundle combinations
function toggleBundleCombinations(event) {
    if (event) {
        event.preventDefault();
    }

    var bundleList = document.getElementById('bundle-combinations-list');

    if (bundleList.style.display === 'none' || bundleList.style.display === '') {
        bundleList.style.display = 'block';
    } else {
        bundleList.style.display = 'none';
    }
}

// Function to toggle individual add-ons
function toggleAddon(addonId) {
    var addonContent = document.getElementById(addonId + '-content');
    var toggleButton = document.getElementById(addonId + '-toggle');

    if (addonContent.style.display === 'none' || addonContent.style.display === '') {
        addonContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        addonContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}

// Function to toggle upgrade section
function toggleUpgradeSection() {
    var upgradeContent = document.getElementById('upgrade-content');
    var toggleButton = document.getElementById('upgrade-toggle');

    if (upgradeContent.style.display === 'none' || upgradeContent.style.display === '') {
        upgradeContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        upgradeContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}

// Function to toggle licensing section
function toggleLicensingSection() {
    var licensingContent = document.getElementById('licensing-content');
    var toggleButton = document.getElementById('licensing-toggle');

    if (licensingContent.style.display === 'none' || licensingContent.style.display === '') {
        licensingContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        licensingContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}

// Function to toggle additional attributes section
function toggleAdditionalAttributes() {
    const content = document.getElementById('additional-attributes-content');
    const toggle = document.getElementById('additional-attributes-toggle');

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        toggle.innerHTML = '-';
    } else {
        content.style.display = 'none';
        toggle.innerHTML = '+';
    }
}

// Function to show tab (for licensing page)
function showTab(tabName) {
    document.getElementById('plans-content').style.display = 'none';
    document.getElementById('bundle-plans-content').style.display = 'none';
    document.getElementById('plans-tab').classList.remove('active');
    document.getElementById('bundle-plans-tab').classList.remove('active');
    document.getElementById(tabName + '-content').style.display = 'block';
    document.getElementById(tabName + '-tab').classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('idp-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.idp-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const idpName = row.getAttribute('data-idp-name').toLowerCase();
                if (idpName.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const totalCountElement = document.getElementById('total-idp-count');
            if (totalCountElement) {
                totalCountElement.textContent = visibleCount;
            }
        });
    }

    const fileInput = document.getElementById('config_file');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const noFileText = (typeof moSAMLNoFileUploaded !== 'undefined') ? moSAMLNoFileUploaded : 'No file uploaded';
            const fileName = this.files[0] ? this.files[0].name : noFileText;
            const nextElement = this.nextElementSibling;
            if (nextElement) {
                nextElement.textContent = fileName;
            }
        });
    }
});

function copySsoUrl(ssoUrl) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(ssoUrl);
    } else {
        // Fallback for older browsers
        var textArea = document.createElement("textarea");
        textArea.value = ssoUrl;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
}

function showImportExportConfig() {
    document.getElementById('idp_list_table').style.display = 'none';
    document.getElementById('mo_saml_import_export_id').style.display = 'block';
}

function backToIdpList() {
    document.getElementById('mo_saml_import_export_id').style.display = 'none';
    document.getElementById('idp_list_table').style.display = 'block';
}

function toggleContent(buttonId, contentId) {
    const button = document.getElementById(buttonId);
    const content = document.getElementById(contentId);

    if (button && content) {
        const isHidden = content.style.display === 'none' || content.style.display === '';
        content.style.display = isHidden ? 'block' : 'none';
        button.textContent = isHidden ? '-' : '+';
    }
}

function togglePremiumContent(buttonId, contentId) {
    const button = document.getElementById(buttonId);
    const content = document.getElementById(contentId);

    if (button && content) {
        const isHidden = content.style.display === 'none' || content.style.display === '';
        content.style.display = isHidden ? 'block' : 'none';
        button.textContent = isHidden ? '-' : '+';
    }
}

function updateFileName(input) {
    const noFileText = (typeof moSAMLNoFileUploaded !== 'undefined') ? moSAMLNoFileUploaded : "No file uploaded";
    const fileName = input.files.length ? input.files[0].name : noFileText;
    document.getElementById("file-name").textContent = fileName;
}


function changeSubMenu(tabPanelId, element0, element1) {
    var $panel = jQuery(tabPanelId);
    $panel.find('.mo_saml_sub_menu_active').removeClass('mo_saml_sub_menu_active');
    jQuery(element0).addClass('mo_saml_sub_menu_active');
    jQuery(element1).nextAll('div').css('display', 'none');
    jQuery(element1).prevAll().css('display', 'none');
    jQuery(element1).css('display', 'block');
}

function changeSubMenuSupport(tabPanelId, element0, element1) {
    var $panel = jQuery(tabPanelId);
    $panel.find('.mo_saml_current_tab').removeClass('mo_saml_current_tab');
    jQuery(element0).addClass('mo_saml_current_tab');
    // Hide both content divs first
    jQuery('#mo_general_support').css('display', 'none');
    jQuery('#mo_screen_share').css('display', 'none');
    // Show the selected div
    jQuery(element1).css('display', 'block');
}