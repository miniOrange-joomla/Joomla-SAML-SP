<?php
/**
 * @package     Joomla.Component	
 * @subpackage  com_miniorange_saml
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_saml/assets/js/samlUtility.js');
$document->addScript(Uri::base() . 'components/com_miniorange_saml/assets/js/bootstrap-select-min.js');
$document->addScript(Uri::base() . 'components/com_miniorange_saml/assets/js/idp-settings.js');

// Add your custom CSS files
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_saml/assets/css/mo_saml_style.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_saml/assets/css/bootstrap-select-min.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_saml/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_saml/assets/css/idp-settings.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_saml/assets/css/inline-styles.css');

// Add Font Awesome CSS from CDN
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');


$cms_version = SAML_Utilities::getJoomlaCmsVersion();
$jsonFile = Uri::base() . 'components/com_miniorange_saml/assets/json/tabs.json';

function getJsonData($url)
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Disable SSL verification (ONLY for local/dev environments)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        return null;
    }

    curl_close($ch);
    return $response;
}

$tabsJson = getJsonData($jsonFile);
$tabs = [];
if ($tabsJson) {
    $tabs = json_decode($tabsJson, true);
    if (!$tabs) {
        $tabs = [];
    }
}


if ($cms_version >= 4.0) {
    $document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js');
}
?>

<?php
if (!Mo_Saml_Local_Util::is_curl_installed()) {
    ?>
    <div id="help_curl_warning_title" class="alert alert-danger">
        <p><a target="_blank" class="mo_saml_cursor"
                onClick="show_curl_msg()"><?php echo Text::_('COM_MINIORANGE_SAML_CURL_WARNING'); ?>
                <?php echo Text::_('COM_MINIORANGE_SAML_CURL_SPAN'); ?></a></p>
    </div>
    <div id="help_curl_warning_desc" class="TC_modal">
        <div class="TC_modal-content">
            <div class="mo_boot_row">
                <div class="mo_boot_col-12 mo_boot_text-center">
                    <span
                        class="mo_saml_troubleshoot"><strong><?php echo Text::_('COM_MINIORANGE_SAML_TROUBLESHOOT'); ?></strong></span>
                    <span class="TC_modal_close" onclick="close_curl_modal()">&times;</span>
                    <hr>
                </div>
                <div class="mo_boot_col-12">
                    <?php echo Text::_('COM_MINIORANGE_SAML_LIST'); ?>
                    <?php echo Text::_('COM_MINIORANGE_SAML_CONTACT'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

$saml_active_tab = "overview";
if (method_exists(Factory::getApplication(), 'getInput')) {
    $get = Factory::getApplication()->getInput()->getArray();
} else {
    $get = Factory::getApplication()->input->getArray();
}
$test_config = isset($get['test-config']) ? true : false;
if (isset($get['tab']) && !empty($get['tab'])) {
    $saml_active_tab = $get['tab'];
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#subhead-container').css('min-height', '55px');
            var subheadDiv = document.getElementById('subhead-container');
            var trialButton = '<div class=""> <a class="mo_saml_subhead_container mo_boot_btn btn_cstm" href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=support_tab') ?>"><i class="fa fa-support mx-1"></i><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_BTN'); ?></a><a class="mo_saml_subhead_container mo_boot_btn btn_cstm" href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=request_demo') ?>"><i class="fa fa-envelope mx-1"></i><?php echo Text::_('COM_MINIORANGE_SAML_FREE_TRIAL'); ?></a></div> ';
            subheadDiv.innerHTML = trialButton;
        });
    </script>
    <?php
}
?>
<?php
$saml_configuration = SAML_Utilities::_get_values_from_table('#__miniorange_saml_config');
$session = Factory::getSession();
$session->set('show_test_config', false);
if ($test_config) {
    $session->set('show_test_config', true);
}

?>
<div class="mo_boot_container-fluid mo_saml_plugin">
    <div class="mo_boot_row mo_saml_navbar">
        <?php
        $cms_version_check = $cms_version <= 4.0;
        if ($cms_version_check) {
            $tabs['request_demo'] = [
                'id' => 'request_demo_tab',
                'href' => '#request-demo',
                'label' => 'COM_MINIORANGE_SAML_FREE_TRIAL',
                'icon' => 'fa-demo'
            ];

            $tabs['support_tab'] = [
                'id' => 'support-tb',
                'href' => '#support-tab',
                'label' => 'COM_MINIORANGE_SAML_SUPPORT_BTN',
                'icon' => 'fa-support'
            ];
        }
        ?>
        <?php foreach ($tabs as $key => $tab): ?>
            <a id="<?php echo $tab['id']; ?>"
                class="mo_boot_col mo_saml_nav-tab mo_saml_text_deco <?php echo $saml_active_tab == $key ? 'mo_nav_tab_active' : ''; ?>"
                href="<?php echo $tab['href']; ?>" onclick="add_css_tab('#<?php echo $tab['id']; ?>');" data-toggle="tab">
                <span><i class="fa fa-solid <?php echo $tab['icon']; ?>"></i></span>
                <span class="tab-label"><?php echo Text::_($tab['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="mo_boot_container-fluid mo_saml_tab-content mo_saml_plugin">
    <div class="tab-content" id="myTabContent">
        <div id="overview_plugin" class="tab-pane <?php echo ($saml_active_tab === 'overview') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php show_plugin_overview(); ?>
            </div>
        </div>

        <div id="identity-provider" class="tab-pane <?php echo ($saml_active_tab === 'idp') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php if (isset($get['id'])) { ?>

                    <?php select_identity_provider($get['id']); ?>

                <?php } else { ?>

                    <?php identity_provider_settings(); ?>
                    <?php import_export_configuration(); ?>

                <?php } ?>
            </div>

        </div>


        <div id="description" class="tab-pane <?php echo ($saml_active_tab === 'description') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php description(); ?>
            </div>
        </div>

        <div id="sso_settings" class="tab-pane <?php echo ($saml_active_tab === 'sso_settings') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php mo_sso_login(); ?>
            </div>
        </div>

        <div id="attribute-mapping"
            class="tab-pane <?php echo ($saml_active_tab === 'attribute_mapping') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php if (isset($get['id'])) { ?>

                    <?php attribute_mapping($get['id']); ?>

                <?php } else { ?>

                    <?php identity_provider_mapping(); ?>

                <?php } ?>
            </div>
        </div>


        <div id="licensing-plans" class="tab-pane <?php echo ($saml_active_tab === 'licensing') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php licensing_page(); ?>
            </div>
        </div>

        <div id="support-tab" class="tab-pane <?php echo ($saml_active_tab === 'support_tab') ? 'active' : ''; ?>">
            <div class="mo_boot_row mo_boot_container-fluid mo_saml_tab-content mo_saml_plugin">
                <?php mo_saml_local_support(); ?>
            </div>
        </div>

        <div id="request-demo" class="tab-pane <?php echo ($saml_active_tab === 'request_demo') ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <?php request_for_demo(); ?>
            </div>
        </div>
    </div>
</div>

<?php
function show_plugin_overview()
{
    ?>
    <div class="mo_boot_col-sm-12 mo_boot_mx-2">
        <section class="mo_saml_section">
            <div class="mo_saml_circle"></div>
            <div class="mo_saml_content mo_boot_m-0 mo_boot_col-sm-7">
                <div class="text_box">
                    <h2>miniOrange SAML SP plugin for Joomla</h2>
                    <p class="mo_boot_mb-4">
                        <?php
                        if (MoConstants::MO_SAML_SP == 'ALL') {
                            echo Text::_('COM_MINIORANGE_SAML_IDP_ALL');
                        } else if (MoConstants::MO_SAML_SP == 'ADFS') {
                            echo Text::_('COM_MINIORANGE_SAML_SP_ADFS');
                        } else if (MoConstants::MO_SAML_SP == 'GOOGLEAPPS') {
                            echo Text::_('COM_MINIORANGE_SAML_SP_GOOGLE_APPS');
                        }
                        ?>
                    </p>
                    <input type="button" class="mo_boot_btn btn_cstm" target="_blank"
                        onclick="window.open('https://plugins.miniorange.com/joomla-single-sign-on-sso')"
                        value="<?php echo Text::_('COM_MINIORANGE_SAML_VISIT_SITE'); ?>" />
                    <input type="button" class="mo_boot_btn btn_cstm" target="_blank"
                        onclick="window.open('https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-sp')"
                        value="<?php echo Text::_('COM_MINIORANGE_SAML_GUIDES'); ?>" />
                    <a class="mo_boot_btn btn_cstm"
                        href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=licensing') ?>"><?php echo Text::_('COM_MINIORANGE_SAML_LICENSE_PLANS'); ?></a>
                    <input type="button" class="mo_boot_btn btn_cstm" target="_blank"
                        onclick="window.open('https://faq.miniorange.com/kb/joomla')"
                        value="<?php echo Text::_('COM_MINIORANGE_SAML_FAQ'); ?>" />
                </div>
            </div>
            <div class="imgBox">
                <img class="mo_saml_overview_image"
                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/overview_tab.png">
            </div>
        </section>
    </div>
    <?php
}

function description()
{
    $siteUrl = Uri::root();
    $sp_base_url = '';

    $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
    $sp_entity_id = isset($result['sp_entity_id']) ? $result['sp_entity_id'] : '';

    if ($sp_entity_id == '') {
        $sp_entity_id = $siteUrl . 'plugins/authentication/miniorangesaml';
    }

    if (isset($result['sp_base_url'])) {
        $sp_base_url = $result['sp_base_url'];
    }

    if (empty($sp_base_url))
        $sp_base_url = $siteUrl;

    $org_name = $result['organization_name'];
    $org_dis_name = $result['organization_display_name'];
    $org_url = $result['organization_url'];
    $tech_name = $result['tech_per_name'];
    $tech_email = $result['tech_email_add'];
    $support_name = $result['support_per_name'];
    $support_email = $result['support_email_add'];
    $licensing_page_link = Uri::base() . 'index.php?option=com_miniorange_saml&tab=licensing';
    ?>
    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <h3 class="form-head form-head-bar ">1.<?php echo Text::_('COM_MINIORANGE_SAML_UPDATE_ENTITY'); ?></h3>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <form
                        action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.updateSPIssuerOrBaseUrl'); ?>"
                        method="post" name="updateissueer" id="identity_provider_update_form">
                        <div class="mo_boot_row mo_boot_m-4">
                            <div class="mo_boot_col-sm-4">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_BASE_URL'); ?> :</span>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <input class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup" type="text"
                                    name="sp_base_url" value="<?php echo $sp_base_url; ?>" required />
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_m-4">
                            <div class="mo_boot_col-sm-4">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_ISSUER'); ?> :</span>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <input class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup" type="text"
                                    name="sp_entity_id" value="<?php echo $sp_entity_id; ?>" required />
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_mt-4">
                            <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                <input type="submit" class="mo_boot_btn btn_cstm"
                                    value="<?php echo Text::_('COM_MINIORANGE_SAML_UPDATE_BTN'); ?>" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
                <h3 class="form-head form-head-bar ">2. <?php echo Text::_('COM_MINIORANGE_SAML_SHARE_METADATA'); ?></h3>
                <ul class="switch_tab_sp mo_boot_text-center mo_boot_p-0 mo_boot_mt-4 mo_boot_m-0">
                    <li class="mo_saml_current_tab" id="metadata-url-tab-btn">
                        <a href="#" class="mo_saml_bs_btn" onclick="showMetadataTab('metadata-url', event)">
                            <i class="fa fa-link"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_METADATA_URL'); ?>

                        </a>
                    </li>
                    <li class="" id="download-xml-tab-btn">
                        <a href="#" class="mo_saml_bs_btn" onclick="showMetadataTab('download-xml', event)">
                            <i class="fa fa-download"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_DOWNLOAD_XML'); ?>

                        </a>
                    </li>
                    <li class="" id="manual-info-tab-btn">
                        <a href="#" class="mo_saml_bs_btn" onclick="showMetadataTab('manual-info', event)">
                            <i class="fa fa-hand-o-up"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_MANUAL_INFO'); ?>
                        </a>
                    </li>
                </ul>
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section">
                    <div id="metadata-url-tab" class="metadata-tab-content mo_boot_display_block">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                <div class="mo_boot_row mo_boot_m-4">
                                    <p><?php echo Text::_('COM_MINIORANGE_SAML_SHARE_METADATA_TEXT'); ?></p>

                                </div>
                                <div class="mo_boot_row mo_boot_m-4">
                                    <span id="idp_metadata_url"
                                        class=" mo_saml_highlight_background_url_note mo_saml_float_right">
                                        <a href='<?php echo $sp_base_url . '?morequest=metadata'; ?>' id='metadata-linkss'
                                            target='_blank'><?php echo '<strong>' . $sp_base_url . '?morequest=metadata </strong>'; ?></a>
                                    </span>
                                    <div class="mo_boot_col-sm-1">
                                        <em class="fa fa-lg fa-copy mo_copy_sso_url mo_copytooltip"
                                            onclick="copyToClipboard('#idp_metadata_url');"></em>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div id="download-xml-tab" class="metadata-tab-content mo_saml_display_none">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 ">
                                <div class="mo_boot_row mo_boot_m-4">
                                    <p><?php echo Text::_('COM_MINIORANGE_SAML_DOWNLOAD_METADATA_TEXT'); ?></p>
                                    <div class="mo_boot_col-sm-12 mo_boot_p-0">
                                        <a href="<?php echo $sp_base_url . '?morequest=download_metadata'; ?>"
                                            class="mo_boot_btn btn_cstm anchor_tag">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_METADATA_BTN'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="manual-info-tab" class="metadata-tab-content mo_saml_display_none">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 ">
                                <div class="mo_boot_row mo_boot_m-4">
                                    <p><?php echo Text::_('COM_MINIORANGE_SAML_MANUAL_INFO_TITLE'); ?></p>
                                    <table class='customtemp mo_boot_col-sm-12'>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_ISSUER'); ?>
                                            </td>
                                            <td><span id="entidy_id"><?php echo $sp_entity_id; ?></span>
                                                <em class="fa fa-pull-right fa-lg fa-copy mo_copy mo_copytooltip"
                                                    onclick="copyToClipboard('#entidy_id');"></em>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_ASC'); ?>
                                            </td>
                                            <td>
                                                <span id="acs_url"><?php echo $sp_base_url . '?morequest=acs'; ?></span>
                                                <em class="fa fa-pull-right fa-lg fa-copy mo_copy mo_copytooltip"
                                                    onclick="copyToClipboard('#acs_url');"></em>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_AUDIENCE'); ?>
                                            </td>
                                            <td>
                                                <span id="audience_url"><?php echo $sp_entity_id; ?></span>
                                                <em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip"
                                                    onclick="copyToClipboard('#audience_url');"></em>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_NAMEID_FORMAT'); ?>
                                            </td>
                                            <td>
                                                <span
                                                    id="sp_name_id_format">urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</span>
                                                <em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip"
                                                    onclick="copyToClipboard('#sp_name_id_format');"></em>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_SLO'); ?>
                                            </td>
                                            <td>
                                                <a href='#' class='premium mo_saml_text_decoration'
                                                    onclick="moSAMLUpgrade();"><strong><?php echo Text::_('COM_MINIORANGE_SAML_PREMIUM_TXT'); ?></strong></a>
                                                <img class="crown_img_small mo_saml_float_right mo_boot_ml-2"
                                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mo_table_td_style mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_DEFAULT_REALY'); ?>
                                            </td>
                                            <td>
                                                <a href='#' class='premium mo_saml_text_decoration'
                                                    onclick="moSAMLUpgrade();"><strong><?php echo Text::_('COM_MINIORANGE_SAML_PREMIUM_TXT'); ?></strong></a>
                                                <img class="crown_img_small mo_saml_float_right mo_boot_ml-2"
                                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="mo_saml_td mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_CRT'); ?>
                                            </td>
                                            <td>
                                                <strong> <a href='#' class='premium mo_saml_text_decoration'
                                                        onclick="moSAMLUpgrade();"><strong><?php echo Text::_('COM_MINIORANGE_SAML_PREMIUM_TXT'); ?></strong></a>
                                                    <img class="crown_img_small mo_saml_float_right mo_boot_ml-2"
                                                        src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="mo_saml_td mo_boot_p-3">
                                                <?php echo Text::_('COM_MINIORANGE_SAML_CSTM_CRT'); ?>
                                            </td>
                                            <td>
                                                <?php echo Text::_('COM_MINIORANGE_SAML_CLICK'); ?>&nbsp;<a href="#"
                                                    class="mo_saml_text_decoration"
                                                    onClick="show_custom_crt_modal()"><?php echo Text::_('COM_MINIORANGE_SAML_HERE'); ?>&nbsp;</a><?php echo Text::_('COM_MINIORANGE_SAML_CERTIFICATE_TEXT'); ?>
                                                <img class="crown_img_small mo_saml_float_right mo_boot_ml-2"
                                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
                <h3 class="form-head form-head-bar ">
                    3.<?php echo Text::_('COM_MINIORANGE_SAML_CUSTOMIZE_METADATA_ORGANIZATIONAL_INFO'); ?></h3>
                <div class="mo_saml_mini_section mo_boot_p-4 mo_boot_mt-4">
                    <div class="mo_saml_main_summary mo_saml_advance_summary" style="cursor: pointer;"
                        onclick="toggleContent('org-toggle', 'org-content')">
                        <?php echo Text::_('COM_MINIORANGE_SAML_ORG_NAME'); ?><sup><strong><a href='#' class='premium'
                                    onclick="moSAMLUpgrade(); event.stopPropagation();"> <img
                                        class="crown_img_small mo_boot_mx-2"
                                        src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                        <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                            id="org-toggle">+</button>
                    </div>
                    <div id="org-content" class="mo_saml_hidden_content">
                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_NAME'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="organization_name" value="<?php echo $org_name; ?>" required
                                    disabled />
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_DIS_NAME'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="organization_display_name" value="<?php echo $org_dis_name; ?>"
                                    required disabled />
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_ORG_URL'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="organization_url" value="<?php echo $org_url; ?>" required disabled />
                            </div>
                        </div>


                        <br>
                    </div>
                </div>

                <div class="mo_saml_mini_section mo_boot_p-4 mo_boot_mt-4">
                    <div class="mo_saml_main_summary mo_saml_advance_summary" style="cursor: pointer;"
                        onclick="toggleContent('tech-toggle', 'tech-content')">
                        <?php echo Text::_('COM_MINIORANGE_SAML_TECH_PERSON'); ?><sup><strong><a href='#' class='premium'
                                    onclick="moSAMLUpgrade(); event.stopPropagation();"> <img
                                        class="crown_img_small mo_boot_mx-2"
                                        src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                        <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                            id="tech-toggle">+</button>
                    </div>
                    <div id="tech-content" class="mo_saml_hidden_content">
                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_PERSON'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="tech_per_name" value="<?php echo $tech_name; ?>" required disabled />
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_PERSON_EMAIL'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="tech_email_add" value="<?php echo $tech_email; ?>" required
                                    disabled />
                            </div>
                        </div>

                        <br>
                    </div>
                </div>

                <div class="mo_saml_mini_section mo_boot_p-4 mo_boot_mt-4">
                    <div class="mo_saml_main_summary mo_saml_advance_summary" style="cursor: pointer;"
                        onclick="toggleContent('support-toggle', 'support-content')">
                        <?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_PERSON'); ?><sup><strong><a href='#' class='premium'
                                    onclick="moSAMLUpgrade(); event.stopPropagation();"> <img
                                        class="crown_img_small mo_boot_mx-2"
                                        src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                        <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                            id="support-toggle">+</button>
                    </div>
                    <div id="support-content" class="mo_saml_hidden_content">
                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_PERSON'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="tech_per_name" value="<?php echo $tech_name; ?>" required disabled />
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                            <div class="mo_boot_col-sm-3">
                                <span><?php echo Text::_('COM_MINIORANGE_SAML_PERSON_EMAIL'); ?><span
                                        class="mo_saml_required">*</span> :</span>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="tech_email_add" value="<?php echo $tech_email; ?>" required
                                    disabled />
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="my_custom_crt_modal" class="TC_modal">
        <div class="mo_boot_row TC_modal-content">
            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_saml_display_block" id="generate_certificate_form">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-lg-10 mo_boot_col-sm-8">
                        <h3 class="mo_saml_form_head"><?php echo Text::_('COM_MINIORANGE_SAML_CUSTOM_CERTIFICATE_TAB'); ?>
                            <div class="mo_tooltip"><img class="crown_img_small mo_saml_float_right"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"><span
                                    class="mo_tooltiptext small"><?php echo Text::sprintf('COM_MINIORANGE_SAML_AVAIABLE_FROM_ENTERPRISE', $licensing_page_link); ?></span>
                            </div>
                        </h3>
                    </div>
                    <div class="mo_boot_col-sm-2">
                        <input type="button" class="mo_boot_btn btn_cstm"
                            value=" <?php echo Text::_('COM_MINIORANGE_SAML_BACK'); ?>" onclick="hide_gen_cert_form()" />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-4">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_COUNTRY_CODE'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input class="mo_saml_table_textbox mo-form-control" type="text"
                            placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_COUNTRY_CODE_PLACEHOLDER'); ?>" disabled>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_STATE'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input class="mo_saml_table_textbox mo-form-control" type="text"
                            placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_STATE_PLACEHOLDER'); ?>" disabled />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_COMPANY'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input class="mo_saml_table_textbox mo-form-control" type="text"
                            placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_COMPANY_PLACEHOLDER'); ?>" disabled />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_UNIT'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input class="mo_saml_table_textbox mo-form-control" type="text"
                            placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_UNIT_PLACEHOLDER'); ?>" disabled />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_COMMON'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input class="mo_saml_table_textbox mo-form-control" type="text"
                            placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_COMMON_PLACEHOLDER'); ?>" disabled />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_DIGEST_ALGORITH'); ?><span class="mo_saml_required">*</span>
                        :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <select class="mo_saml_table_textbox mo-form-control mo-form-control-select">
                            <?php echo Text::_('COM_MINIORANGE_SAML_VALID_DAYS'); ?>
                            <option>SHA512</option>
                            <option>SHA384</option>
                            <option>SHA256</option>
                            <option>SHA1</option>
                        </select>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_PRIVATE_KEY'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <select class="mo_saml_table_textbox mo-form-control mo-form-control-select">
                            <?php echo Text::_('COM_MINIORANGE_SAML_VALID_DAYS'); ?>
                            <option>2048 bits</option>
                            <option>1024 bits</option>
                        </select>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-4">
                        <?php echo Text::_('COM_MINIORANGE_SAML_VALID_DAYS'); ?><span class="mo_saml_required">*</span> :
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <select class="mo_saml_table_textbox mo-form-control mo-form-control-select">
                            <option>365 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>180 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>90 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>45 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>30 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>15 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                            <option>7 <?php echo Text::_('COM_MINIORANGE_SAML_DAYS'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
                    <div class="mo_boot_col-sm-12">
                        <input type="submit" value=" <?php echo Text::_('COM_MINIORANGE_SAML_SELF_SIGNED'); ?>" disabled
                            class="btn btn_cstm" ; />
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-3" id="mo_gen_cert">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-10">
                        <h3 class="mo_saml_form_head"><?php echo Text::_('COM_MINIORANGE_SAML_CUSTOM_CERTIFICATE_TAB'); ?>
                            <div class="mo_tooltip"><img class="crown_img_small mo_boot_ml-2 mo_saml_float_right"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"><span
                                    class="mo_tooltiptext small"><?php echo Text::sprintf('COM_MINIORANGE_SAML_AVAIABLE_FROM_ENTERPRISE', $licensing_page_link); ?></span>
                            </div>
                        </h3>
                    </div>
                    <div class="mo_boot_col-sm-2">
                        <button class="TC_modal_close mo_boot_btn btn_cstm_red"
                            onclick="close_custom_crt_modal()">&times;</button>
                    </div>
                    <div class="mo_boot_col-sm-12 alert alert-info">
                        <?php echo Text::_('COM_MINIORANGE_SAML_CUSTOM_CRT_NOTE'); ?>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_mt-3" id="customCertificateData"><br>
                        <div class="mo_boot_row custom_certificate_table">
                            <div class="mo_boot_col-sm-3">
                                <?php echo Text::_('COM_MINIORANGE_SAML_PUBLIC_CRT'); ?>
                                <span class="mo_saml_required">*</span>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <textarea disabled="disabled" rows="4"
                                    class="mo_saml_table_textbox w-100 mo_boot_col-sm-12 mb-3"></textarea>
                            </div>
                        </div>
                        <div class="mo_boot_row custom_certificate_table">
                            <div class="mo_boot_col-sm-3">
                                <?php echo Text::_('COM_MINIORANGE_SAML_PRIVATE_CRT'); ?>
                                <span class="mo_saml_required">*</span>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <textarea disabled="disabled" rows="4"
                                    class="mo_saml_table_textbox w-100 mo_boot_col-sm-12"></textarea>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-3 custom_certificate_table" id="save_config_element">
                            <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                <input disabled="disabled" type="submit" name="submit"
                                    value=" <?php echo Text::_('COM_MINIORANGE_SAML_UPLOAD'); ?>"
                                    class="mo_boot_btn btn_cstm" /> &nbsp;&nbsp;
                                <input type="button" name="submit"
                                    value=" <?php echo Text::_('COM_MINIORANGE_SAML_GENERATE'); ?>"
                                    class="mo_boot_btn btn_cstm" onclick="show_gen_cert_form()" />&nbsp;&nbsp;
                                <input disabled type="submit" name="submit"
                                    value=" <?php echo Text::_('COM_MINIORANGE_SAML_RM'); ?>"
                                    class="mo_boot_btn btn_cstm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mo_boot_row mo_saml_display_none">
                <div class="mo_boot_col-12 mo_boot_text-center">
                    <h2><span><strong><?php echo Text::_('COM_MINIORANGE_SAML_TC'); ?></strong></span></h2>
                    <span class="TC_modal_close" onclick="close_TC_modal()">&times;</span>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function licensing_page()
{
    $useremail = new Mo_saml_Local_Util();
    $useremail = $useremail->_load_db_values('#__miniorange_saml_customer_details');
    if (isset($useremail))
        $user_email = $useremail['email'];
    else
        $user_email = "xyz";

    ?>
    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
            <h3 class="form-head form-head-bar "><?php echo Text::_('COM_MINIORANGE_SAML_SELECT_PLAN_UPGRADE'); ?></h3>
            <ul class="switch_tab_sp text-center mo_boot_p-0 mo_boot_mt-4 ">
                <li class="mo_saml_current_tab" id="plans-tab-btn">
                    <a href="#" class="mo_saml_bs_btn" onclick="showLicensingTab('plans', event)">
                        <i class="fa fa-link"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_PLANS'); ?>

                    </a>
                </li>
                <li class="" id="bundle-plans-tab-btn">
                    <a href="#" class="mo_saml_bs_btn" onclick="showLicensingTab('bundle-plans', event)">
                        <i class="fa fa-download"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_BUNDLE_PLANS'); ?>

                    </a>
                </li>

            </ul>
        </div>

        <div id="plans-tab" class="licensing-tab-content mo_boot_display_block">
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-sm-3">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_boot_pb-0">
                        <h3 class="mo_boot_text-center"><?php echo Text::_('COM_MINIORANGE_SAML_BASIC_HEADING'); ?></h3>
                        <h1 class=" text-center mo_saml_pricing_title mo_boot_mt-0">$149<sup
                                class="mo_saml_pricing_asterisk">*</sup></h1>
                        <div class="mo_boot_text-center mo_boot_mt-1">
                            <button class=" mo_saml_get_plugin_btn"
                                onclick="window.open('https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_basic_plan')"><?php echo Text::_('COM_MINIORANGE_SAML_GET_PLUGIN'); ?></button>
                        </div>

                    </div>
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_secion_feature mo_boot_pt-0 mo_boot_pb-0">
                        <div class="mo_boot_mb-3 mo_boot_mt-1">
                            <div class="feature-section" onclick="toggleFeatures('basic-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_included_icon"><i
                                            class="fa fa-check"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_INCLUDED_FEATURES'); ?>
                                </p>
                                <hr class="mo_boot_m-0">
                            </div>
                            <div id="basic-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTO_CREATION'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTHENTICATION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE_SP_METADATA'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>

                            <div class="feature-section" onclick="toggleFeatures('basic-not-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_not_included_icon"><i
                                            class="fa fa-times"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_NOT_INCLUDED_FEATURES'); ?>
                                </p>
                            </div>
                            <div id="basic-not-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ROLE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ATTRIBUTE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SINGLE_LOGOUT'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_SUPER'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKDOOR_URL1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_CHILD'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_RESTRICTION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTH_CONTEXT_CLASS'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SIGNATURE_ALGO'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SLO_BINDING_TYPE'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SAML_BINDING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_IDP'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SIGNED_REQUEST_SSO'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_GENERATE_CUSTOM_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_SYNC_IDP_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_STORE_MULTIPLE_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_MULTIPLE_IDP_SUPPORT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="mo_boot_col-sm-3">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_boot_pb-0">
                        <h3 class="mo_boot_text-center"><?php echo Text::_('COM_MINIORANGE_SAML_STANDARD_HEADING'); ?></h3>
                        <h1 class=" text-center mo_saml_pricing_title mo_boot_mt-0">$249<sup
                                class="mo_saml_pricing_asterisk">*</sup></h1>
                        <div class="mo_boot_text-center mo_boot_mt-1">
                            <button class=" mo_saml_get_plugin_btn"
                                onclick="window.open('https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_standard_plan')"><?php echo Text::_('COM_MINIORANGE_SAML_GET_PLUGIN'); ?></button>
                        </div>

                    </div>
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_secion_feature mo_boot_pt-0 mo_boot_pb-0">
                        <div class="mo_boot_mb-3 mo_boot_mt-1">
                            <div class="feature-section" onclick="toggleFeatures('standard-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_included_icon"><i
                                            class="fa fa-check"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_INCLUDED_FEATURES'); ?>
                                </p>
                                <hr class="mo_boot_m-0">
                            </div>
                            <div id="standard-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTO_CREATION'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTHENTICATION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE_SP_METADATA'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ROLE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ATTRIBUTE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SAML_BINDING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_IDP'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DEFAULT_REDIRECT_URL'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SIGNED_REQUEST_SSO'); ?></li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>

                            <div class="feature-section" onclick="toggleFeatures('standard-not-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_not_included_icon"><i
                                            class="fa fa-times"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_NOT_INCLUDED_FEATURES'); ?>
                                </p>
                            </div>
                            <div id="standard-not-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ROLE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ATTRIBUTE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SINGLE_LOGOUT'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_SUPER'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKDOOR_URL1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_CHILD'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_RESTRICTION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTH_CONTEXT_CLASS'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SIGNATURE_ALGO'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SLO_BINDING_TYPE'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_GENERATE_CUSTOM_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_SYNC_IDP_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_STORE_MULTIPLE_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_MULTIPLE_IDP_SUPPORT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="mo_boot_col-sm-3">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_boot_pb-0">
                        <h3 class=" mo_boot_text-center"><?php echo Text::_('COM_MINIORANGE_SAML_PREMIUM_HEADER'); ?></h3>
                        <h1 class=" text-center mo_saml_pricing_title mo_boot_mt-0">$399<sup
                                class="mo_saml_pricing_asterisk">*</sup></h1>
                        <div class="mo_boot_text-center mo_boot_mt-1">
                            <button class=" mo_saml_get_plugin_btn"
                                onclick="window.open('https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_premium_plan')"><?php echo Text::_('COM_MINIORANGE_SAML_GET_PLUGIN'); ?></button>
                        </div>
                    </div>
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_secion_feature mo_boot_pt-0 mo_boot_pb-0">
                        <div class="mo_boot_mb-3 mo_boot_mt-1">
                            <div class="feature-section" onclick="toggleFeatures('premium-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_m-0 mo_boot_text-center mo_saml_feature_header"> <span
                                        class="mo_saml_included_icon"><i
                                            class="fa fa-check"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_INCLUDED_FEATURES'); ?>
                                </p>
                                <hr class="mo_boot_m-0">
                            </div>
                            <div id="premium-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTO_CREATION'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTHENTICATION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE_SP_METADATA'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ROLE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ATTRIBUTE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SAML_BINDING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_IDP'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DEFAULT_REDIRECT_URL'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SIGNED_REQUEST_SSO'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ROLE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ATTRIBUTE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SINGLE_LOGOUT'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_SUPER'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKDOOR_URL1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>

                            <div class="feature-section" onclick="toggleFeatures('premium-not-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_not_included_icon"><i
                                            class="fa fa-times"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_NOT_INCLUDED_FEATURES'); ?>
                                </p>
                            </div>
                            <div id="premium-not-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_CHILD'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_RESTRICTION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTH_CONTEXT_CLASS'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SIGNATURE_ALGO'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SLO_BINDING_TYPE'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_GENERATE_CUSTOM_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_SYNC_IDP_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_STORE_MULTIPLE_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_MULTIPLE_IDP_SUPPORT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="mo_boot_col-sm-3">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_boot_pb-0">
                        <h3 class=" mo_boot_text-center"><?php echo Text::_('COM_MINIORANGE_SAML_ENTERPRISE'); ?></h3>
                        <h1 class=" text-center mo_saml_pricing_title mo_boot_mt-0">$449<sup
                                class="mo_saml_pricing_asterisk">*</sup></h1>
                        <div class="mo_boot_text-center mo_boot_mt-1">
                            <button class=" mo_saml_get_plugin_btn"
                                onclick="window.open('https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_enterprise_plan')"><?php echo Text::_('COM_MINIORANGE_SAML_GET_PLUGIN'); ?></button>
                        </div>
                    </div>
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_secion_feature mo_boot_pt-0 mo_boot_pb-0">
                        <div class="mo_boot_mb-3 mo_boot_mt-1">
                            <div class="feature-section" onclick="toggleFeatures('enterprise-included')"
                                style="cursor: pointer;">
                                <p class="mo_boot_mb-0 mo_boot_text-center mo_saml_feature_header"><span
                                        class="mo_saml_included_icon"><i
                                            class="fa fa-check"></i></span>&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_INCLUDED_FEATURES'); ?>
                                </p>
                            </div>
                            <div id="enterprise-included-list" class="feature-list mo_boot_p-2" style="display: none;">
                                <ul class="mo_saml_feature_list">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTO_CREATION'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_UNLIMITED_AUTHENTICATION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE_SP_METADATA'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ROLE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ATTRIBUTE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SAML_BINDING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_IDP'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DEFAULT_REDIRECT_URL'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SIGNED_REQUEST_SSO'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ROLE_MAPPING'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_ADVANCE_ATTRIBUTE_MAPPING'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SINGLE_LOGOUT'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_SUPER'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKDOOR_URL1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_BACKEND_LOGIN_CHILD'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_RESTRICTION'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING1'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTH_CONTEXT_CLASS'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_SIGNATURE_ALGO'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_SLO_BINDING_TYPE'); ?></li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_GENERATE_CUSTOM_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_SYNC_IDP_CONFIG'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_STORE_MULTIPLE_CERT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                    <li><span>•</span> <?php echo Text::_('COM_MINIORANGE_SAML_MULTIPLE_IDP_SUPPORT'); ?>
                                    </li>
                                    <hr class="mo_boot_m-2">
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_saml_pricing_card mo_saml_mini_section">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-4 mo_boot_ml-3">
                                <h3 class="mo_boot_mb-3 "><?php echo Text::_('COM_MINIORANGE_SAML_ALL_INCLUSIVE'); ?></h3>
                                <p class="mo_boot_mb-3 text-muted">
                                    <?php echo Text::_('COM_MINIORANGE_SAML_ALL_INCLUSIVE_DESC'); ?>
                                </p>
                                <h1 class="mo_boot_mb-2 mo_saml_pricing_title mo_saml_inclusive">$649<sup
                                        class="mo_saml_pricing_asterisk">*</sup></h1>
                                <button class="mo_saml_contact_btn mo_boot_btn btn_cstm"
                                    onclick="window.open('https://www.miniorange.com/contact')"><?php echo Text::_('COM_MINIORANGE_SAML_CONTACT_US'); ?></button>
                            </div>


                            <div class="mo_boot_col-sm-7">
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_ALL_ENTERPRISE_FEATURES'); ?></p>
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_FOLLOWING_ADDONS'); ?></p>
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-6">
                                        <ul class="mo_boot_mb-0 mo_boot_mt-3 mo_saml_feature_list">
                                            <li>• <?php echo Text::_('COM_MINIORANGE_SAML_IP_RESTRICTION'); ?></li>
                                            <li>• <?php echo Text::_('COM_MINIORANGE_SAML_ROLE_GROUP_REDIRECTION'); ?></li>
                                            <li>• <?php echo Text::_('COM_MINIORANGE_SAML_INTEGRATE_COMMUNITY_BUILDER'); ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <ul class="mo_boot_mb-0 mo_boot_mt-3 mo_saml_feature_list">
                                            <li>• <?php echo Text::_('COM_MINIORANGE_SAML_PAGE_ARTICLE_RESTRICTION'); ?>
                                            </li>
                                            <li>• <?php echo Text::_('COM_MINIORANGE_SAML_SSO_LOGIN_AUDIT'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




        </div>


        <div id="bundle-plans-tab" class="licensing-tab-content mo_saml_display_none">

            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_saml_pricing_card mo_saml_mini_section">
                        <h2 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_SAML_BOOST_USER_MANAGEMENT'); ?> <span
                                class="mo_saml_imp_text"><?php echo Text::_('COM_MINIORANGE_SAML_SCIM_INTEGRATION'); ?></span>
                        </h2>
                        <h3 class="mo_boot_mb-4"><?php echo Text::_('COM_MINIORANGE_SAML_SIMPLIFY_USER_PROVISIONING'); ?>
                        </h3>
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-6">
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_USER_PROVISIONING'); ?></p>
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_REAL_TIME_SYNC'); ?></p>
                            </div>
                            <div class="mo_boot_col-sm-6">
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_SEAMLESS_ROLE_MAPPING'); ?></p>
                                <p class="mo_boot_mb-2"><span class="mo_saml_included_icon">✔</span>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_SECURE_COMPLIANT'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_saml_pricing_card mo_saml_mini_section">
                        <h2 class="mo_boot_mb-3 mo_boot_col-sm-12 p-0">
                            <?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_EXPERIENCE'); ?> <span
                                class="mo_saml_imp_text"><?php echo Text::_('COM_MINIORANGE_SAML_DISCOUNT'); ?></span><button
                                class=" mo_saml_contact_btn mo_boot_btn btn_cstm  mo_boot_ml-3"
                                onclick="window.open('https://www.miniorange.com/contact')"><?php echo Text::_('COM_MINIORANGE_SAML_CONTACT_US'); ?></button>
                        </h2>
                        <div>
                        </div>
                        <h3 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_SAML_ADD_SCIM_SAML_PLAN'); ?> <span
                                class="mo_saml_imp_text">$50</span>
                            <?php echo Text::_('COM_MINIORANGE_SAML_OFF_TOTAL_PRICE'); ?></h3>
                        <div class="mo_boot_mb-3">
                            <a href="#" class="mo_saml_bundle_link"
                                onclick="toggleBundleCombinations(event)"><?php echo Text::_('COM_MINIORANGE_SAML_CHECK_RECOMMENDED_BUNDLES'); ?></a>
                        </div>
                        <div id="bundle-combinations-list" style="display: none;">
                            <div class="bundle-combinations-container">
                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_STANDARD'); ?></strong>
                                        <span style="font-weight: bold;">$249</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_STANDARD'); ?></strong>
                                        <span style="font-weight: bold;">$199</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$448</span> <span
                                            class="mo_saml_bundle_discount_price">$398*</span>
                                    </div>
                                </div>

                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_PREMIUM'); ?></strong>
                                        <span style="font-weight: bold;">$399</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_STANDARD'); ?></strong>
                                        <span style="font-weight: bold;">$199</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$598</span> <span
                                            class="mo_saml_bundle_discount_price">$548*</span>
                                    </div>
                                </div>

                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_ENTERPRISE'); ?></strong>
                                        <span style="font-weight: bold;">$449</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_STANDARD'); ?></strong>
                                        <span style="font-weight: bold;">$199</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$648</span> <span
                                            class="mo_saml_bundle_discount_price">$598*</span>
                                    </div>
                                </div>

                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_STANDARD'); ?></strong>
                                        <span style="font-weight: bold;">$249</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_PREMIUM'); ?></strong>
                                        <span style="font-weight: bold;">$299</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$548</span> <span
                                            class="mo_saml_bundle_discount_price">$498*</span>
                                    </div>
                                </div>

                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_PREMIUM'); ?></strong>
                                        <span style="font-weight: bold;">$399</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_PREMIUM'); ?></strong>
                                        <span style="font-weight: bold;">$299</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$698</span> <span
                                            class="mo_saml_bundle_discount_price">$648*</span>
                                    </div>
                                </div>

                                <div class="mo_saml_mini_sectionN mo_boot_align-items-center mo_boot_p-3">
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SAML_SP_ENTERPRISE'); ?></strong>
                                        <span style="font-weight: bold;">$449</span>
                                    </div>
                                    <div class="bundle-operator">+</div>
                                    <div class="bundle-item">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_JOOMLA_SCIM_PREMIUM'); ?></strong>
                                        <span style="font-weight: bold;">$299</span>
                                    </div>
                                    <div class="bundle-operator">=</div>
                                    <div class="bundle-result mo_boot_text-center">
                                        <span class="mo_saml_bundle_original_price">$748</span> <span
                                            class="mo_saml_bundle_discount_price">$698*</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
            <h3 class="form-head form-head-bar "><?php echo Text::_('COM_MINIORANGE_SAML_INTEGRATION_ASSISTANCE'); ?></h3>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card">
                        <p class="mo_boot_mb-2"><?php echo Text::_('COM_MINIORANGE_SAML_INTEGRATION_ASSISTANCE_DESC'); ?>
                        </p>
                        <p class="mo_boot_mb-2"><?php echo Text::_('COM_MINIORANGE_SAML_PROVIDE_SERVICES'); ?></p>
                        <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_EMAIL_DOUBTS'); ?> <a
                                href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
            <h3 class="form-head form-head-bar "><?php echo Text::_('COM_MINIORANGE_SAML_SAML_PLUGIN_ADDONS'); ?></h3>
            <div class="mo_boot_row mo_boot_d-flex">
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="community-builder-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_INTEGRATE_COMMUNITY_BUILDER_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_COMMUNITY_BUILDER_DESC'); ?>
                            </p>
                            <a href="https://www.miniorange.com/contact" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>

                    </div>
                </div>
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="ip-restriction-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_IP_RESTRICTION_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_IP_RESTRICTION_DESC'); ?>
                            </p>
                            <a href="https://www.miniorange.com/contact" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="scim-sync-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_SCIM_SYNC_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_SCIM_SYNC_DESC'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/joomla-scim-user-provisioning" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-3 mo_boot_d-flex">
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="page-restriction-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_PAGE_RESTRICTION_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_PAGE_RESTRICTION_DESC'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/page-and-article-restriction-for-joomla" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="sso-audit-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_SSO_AUDIT_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_SSO_AUDIT_DESC'); ?>
                            </p>
                            <a href="https://www.miniorange.com/contact" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-4 mo_boot_d-flex">
                    <div class="mo_saml_pricing_card mo_saml_mini_section mo_saml_addon_card mo_boot_h-100">
                        <div id="role-redirection-content">
                            <h3 class="mo_boot_mb-2 mo_saml_addon_title">
                                <?php echo Text::_('COM_MINIORANGE_SAML_ROLE_REDIRECTION_TITLE'); ?>
                            </h3>
                            <p class="mo_boot_mb-2 mo_saml_addon_desc" style="font-size: 16px; min-height: 3rem;">
                                <?php echo Text::_('COM_MINIORANGE_SAML_ROLE_REDIRECTION_DESC'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/role-based-redirection-for-joomla" target="_blank"
                                class="mo_saml_addon_link"><?php echo Text::_('COM_MINIORANGE_SAML_LEARN_MORE'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mo_boot_col-sm-12 mo_boot_mt-4">
            <div class="mo_saml_pricing_card mo_saml_mini_section">
                <div class="mo_boot_d-flex mo_boot_justify-content-between mo_boot_align-items-center"
                    onclick="toggleUpgradeSection()" style="cursor: pointer;">
                    <h3 class="mo_boot_mb-0 mo_boot_col-sm-7"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_HEADER'); ?>
                    </h3>
                    <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                        id="upgrade-toggle">+</button>
                </div>
                <div id="upgrade-content" style="display: none;">
                    <div class="mo_boot_row mo_boot_mt-3 mo_boot_col-sm-12">
                        <div class="mo_boot_col-sm-12 mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">1</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_STEP_ONE'); ?></p>
                            </div>

                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">4</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_FOUR'); ?></p>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">2</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_STEP_TWO'); ?></p>
                            </div>

                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">5</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_FIVE'); ?></p>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">3</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_STEP_THREE'); ?></p>
                            </div>

                            <div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
                                <div class="mo_saml_step_number">6</div>
                                <p class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_SAML_UPGRADE_SIX'); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="mo_boot_col-sm-12 mo_boot_mt-4">
            <div class="mo_saml_pricing_card mo_saml_mini_section ">
                <div class="mo_boot_d-flex mo_boot_justify-content-between mo_boot_align-items-center"
                    onclick="toggleLicensingSection()" style="cursor: pointer;">
                    <h3 class="mo_boot_mb-0 mo_boot_col-sm-7">
                        <?php echo Text::_('COM_MINIORANGE_SAML_RETURN_POLICY_HEADER'); ?>
                    </h3>
                    <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                        id="licensing-toggle">+</button>
                </div>
                <div id="licensing-content" style="display: none;">
                    <div class="mo_boot_mt-3 mo_boot_col-sm-12">

                        <div>
                            <p class="mo_boot_mb-2"><?php echo Text::_('COM_MINIORANGE_SAML_RETURN_POLICY_DESC'); ?></p>
                            <h4 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_SAML_HOW_IT_WORKS'); ?></h4>
                            <p>1. <?php echo Text::_('COM_MINIORANGE_SAML_REPORT_ISSUE'); ?> <a
                                    href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a></p>
                            <p>2. <?php echo Text::_('COM_MINIORANGE_SAML_TEAM_WORK'); ?></p>
                            <p>3. <?php echo Text::_('COM_MINIORANGE_SAML_REFUND_AMOUNT'); ?></p>

                            <h4 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_SAML_POLICY_NOT_COVER'); ?></h4>
                            <ol class="mo_boot_mb-0" style="padding-left: 20px;">
                                <p><?php echo Text::_('COM_MINIORANGE_SAML_CHANGE_MIND'); ?></p>
                                <p><?php echo Text::_('COM_MINIORANGE_SAML_INFRASTRUCTURE_ISSUES'); ?></p>
                                <p><?php echo Text::_('COM_MINIORANGE_SAML_FEES_PAID'); ?></p>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    </div>
    </div>
    </div>
    <?php
}


function mo_sso_login()
{
    $siteUrl = Uri::root();
    $sp_base_url = $siteUrl;
    $main_menu_link = Uri::base() . 'index.php?option=com_menus&view=items&menutype=mainmenu';
    $licensing_page_link = Uri::base() . 'index.php?option=com_miniorange_saml&tab=licensing';
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');

    $idp_name = isset($attribute['idp_name']) ? $attribute['idp_name'] : '';
    ?>

    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <h3 class="form-head form-head-bar ">
                    <?php echo Text::_('COM_MINIORANGE_SAML_ACCOUNT_CREATION_SETTINGS'); ?><sup><strong><a href='#'
                                class='premium' onclick="moSAMLUpgrade();"> <img class="crown_img_small mo_boot_mx-2"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                </h3>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <h3><?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING'); ?></h3>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_ml-2">
                            <p><?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING_NOTE'); ?></p>
                        </div>
                        <div class=" mo_boot_row  mo_boot_col-sm-12 mo_boot_ml-2">
                            <div class="mo_boot_row mo_boot_col-sm-3 mo_boot_mt-2">
                                <select id="idp_name" name="idp_name"
                                    class="mo-form-control mo-form-control-select mo_saml_proxy_setup mo_saml_block_cursor"
                                    disabled>
                                    <option value="">
                                        <?php echo !empty($idp_name) ? htmlspecialchars($idp_name) : Text::_('COM_MINIORANGE_SAML_SELECT_IDP_NAME'); ?>
                                    </option>
                                </select>
                            </div>
                            <div class="mo_boot_d-flex mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_align-items-center">
                                <input
                                    class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                    type="text" name="domain_mapping"
                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING_PLACEHOLDER'); ?>"
                                    disabled />
                                <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_block_cursor mo_boot_col-sm-2"
                                    style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <h3><?php echo Text::_('COM_MINIORANGE_SAML_LOGIN_CREATE_ACCOUNT_BY_EMAIL_USERNAME'); ?></h3>
                        <div
                            class="mo_boot_d-flex mo_boot_align-items-center mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_ml-2 mo_boot_p-0">
                            <p class="mo_boot_col-sm-7 mo_boot_mb-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_LOGIN_CREATE_ACCOUNT'); ?>
                            </p>

                            <select
                                class="mo-form-control mo-form-control-select mo_saml_proxy_setup mo_boot_col-sm-3 mo_boot_mr-3">
                                <option selected><?php echo Text::_('COM_MINIORANGE_SAML_EMAIL'); ?></option>
                                <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_USERNAME'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_mt-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_DO_NOT_AUTO_CREATE_USERS'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="enable_auto_create_users" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                        <div
                            class="mo_boot_d-flex mo_boot_align-items-center mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_ml-2 mo_boot_p-0">
                            <p class="mo_boot_col-sm-12 mo_boot_mb-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_LOGIN_CREATE_ACCOUNT'); ?>
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
                <h3 class="form-head form-head-bar ">
                    <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_ADMIN_ACCESS'); ?><sup><strong><a href='#'
                                class='premium' onclick="moSAMLUpgrade();"> <img class="crown_img_small mo_boot_mx-2"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                </h3>


                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_mt-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_TO_IDP'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="auto_redirect_idp" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_ml-2 mo_boot_p-0">
                            <div class="mo_boot_col-sm-12 mo_boot_mb-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_REDIRECT_NOTE'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <h3><?php echo Text::_('COM_MINIORANGE_SAML_ENABLE_SSO_ADMIN_URL'); ?></h3>
                        <div class="mo_boot_col-sm-12">
                            <div class="mo_boot_row mo_boot_mt-3">
                                <div class="mo_boot_col-sm-6">
                                    <div class="mo_boot_form-check">
                                        <div class="mo_boot_d-flex mo_boot_align-items-center">
                                            <input type="checkbox"
                                                class="mo_boot_form-check-input mo_boot_mr-2 mo_saml_block_cursor"
                                                id="admin_super_users" disabled>
                                            <label class="mo_boot_form-check-label"
                                                for="admin_super_users"><?php echo Text::_('COM_MINIORANGE_SAML_ADMIN_SUPER_USERS'); ?></label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_form-check">
                                        <div class="mo_boot_d-flex mo_boot_align-items-center">
                                            <input type="checkbox"
                                                class="mo_boot_form-check-input mo_boot_mr-2 mo_saml_block_cursor"
                                                id="manager_users mo_saml_block_cursor" disabled>
                                            <label class="mo_boot_form-check-label"
                                                for="manager_users"><?php echo Text::_('COM_MINIORANGE_SAML_MANAGER'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-6">
                                    <div class="mo_boot_form-check">
                                        <div class="mo_boot_d-flex mo_boot_align-items-center">
                                            <input type="checkbox"
                                                class="mo_boot_form-check-input mo_boot_mr-2 mo_saml_block_cursor"
                                                id="child_admin_groups" disabled>
                                            <label class="mo_boot_form-check-label"
                                                for="child_admin_groups"><?php echo Text::_('COM_MINIORANGE_SAML_CHILD_ADMIN_GROUPS'); ?></label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_form-check">
                                        <div class="mo_boot_d-flex mo_boot_align-items-center">
                                            <input type="checkbox"
                                                class="mo_boot_form-check-input mo_boot_mr-2 mo_saml_block_cursor"
                                                id="child_manager_groups" disabled>
                                            <label class="mo_boot_form-check-label"
                                                for="child_manager_groups"><?php echo Text::_('COM_MINIORANGE_SAML_CHILD_MANAGER_GROUPS'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_col-sm-10  mo_boot_mt-3 ">
                                <div class="mo_boot_col-sm-12 mo_boot_p-0">
                                    <input
                                        class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup mo_saml_block_cursor"
                                        type="text"
                                        placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_URL_PLACEHOLDER'); ?>"
                                        disabled />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-9 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_mt-0"><?php echo Text::_('COM_MINIORANGE_SAML_BACKDOOR_URL'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="enable_backdoor" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                        <div class="mo_saml_highlight_background_url_note mo_boot_mt-3">
                            <div class="mo_boot_row mo_boot_m-0">
                                <div class="mo_boot_col-10">
                                    <span class="mo_saml_text mo_boot_color">
                                        <strong><?php echo $sp_base_url; ?>administrator?mopassadminsso=true</strong>
                                    </span>
                                </div>
                                <div class="mo_boot_col-2">
                                    <em class="fa fa-lg fa-copy mo_copy_sso_login_url mo_copytooltip mo_saml_block_cursor"
                                        disabled></em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4">
                <h3 class="form-head form-head-bar ">
                    <?php echo Text::_('COM_MINIORANGE_SAML_DOMAIN_MAPPING_SHARED_SESSION'); ?><sup><strong><a href='#'
                                class='premium' onclick="moSAMLUpgrade();"> <img class="crown_img_small mo_boot_mx-2"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                </h3>



                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <h3><?php echo Text::_('COM_MINIORANGE_SAML_LOGIN_FLOW_DOMAIN_MAPPING_FAILS'); ?></h3>
                        <div class="mo_boot_col-sm-12 mo_boot_ml-2">
                            <select
                                class="mo-form-control mo-form-control-select mo_saml_proxy_setup mo_boot_col-sm-6 mo_boot_mr-3">
                                <option selected>
                                    <?php echo Text::_('COM_MINIORANGE_SAML_ALLOW_USER_LOGIN_JOOMLA_CREDENTIALS'); ?>
                                </option>
                                <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_BLOCK_USER_ACCESS'); ?></option>
                                <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_REDIRECT_TO_CUSTOM_URL'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_mt-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_IGNORE_SPECIAL_CHARS_EMAIL'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="ignore_special_chars" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_ml-2 mo_boot_p-0">
                            <div class="mo_boot_col-sm-12 mo_boot_mb-0">
                                <?php echo Text::_('COM_MINIORANGE_SAML_IGNORE_SPECIAL_CHARS_NOTE'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section ">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mt-0"><?php echo Text::_('COM_MINIORANGE_SAML_ENABLE_SHARED_SESSION'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="enable_shared_session" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="mo_boot_row m-0 p-0 mo_boot_mt-5">
            <div class="mo_boot_col-sm-12 m-0 p-0 mo_boot_text-center">
                <input type="submit" class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                    value="<?php echo Text::_('COM_MINIORANGE_SAML_SAVE_LOGIN_SETTINGS'); ?>" disabled />
            </div>
        </div>
    </div>
    <?php
}

function attribute_mapping()
{
    $licensing_page_link = Uri::base() . 'index.php?option=com_miniorange_saml&tab=licensing';
    ?>
    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <h3 class="form-head form-head-bar ">
                    <?php echo Text::_('COM_MINIORANGE_SAML_ATTRIBUTE_MAPPING'); ?><sup><strong><a href='#' class='premium'
                                onclick="moSAMLUpgrade();"> <img class="crown_img_small mo_boot_mx-2"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                </h3>


                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-7">a)
                                <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_ATTRIBUTE_MAPPING'); ?>
                            </h3>
                            <div class="form-check mo_boot_ml-sm-auto mo_boot_pr-md-5">
                                <div class="mo_boot_d-flex mo_boot_align-items-center">
                                    <input type="checkbox" class="form-check-input mo_boot_mr-2 mo_saml_block_cursor"
                                        id="do_not_update_attributes" disabled>
                                    <label class="form-check-label" for="do_not_update_attributes">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_DO_NOT_UPDATE_EXISTING_USERS_ATTRIBUTES'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                            <div class="mo_boot_row">
                                <!-- Left Column -->
                                <div class="mo_boot_col-sm-6">
                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-4">
                                            <label
                                                class="form-label"><?php echo Text::_('COM_MINIORANGE_SAML_USERNAME_LABEL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                            <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                type="text"
                                                value="<?php echo Text::_('COM_MINIORANGE_SAML_NAMEID_PLACEHOLDER'); ?>"
                                                disabled />
                                        </div>
                                    </div>

                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-4">
                                            <label
                                                class="form-label"><?php echo Text::_('COM_MINIORANGE_SAML_EMAIL_LABEL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                            <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                type="text"
                                                value="<?php echo Text::_('COM_MINIORANGE_SAML_NAMEID_PLACEHOLDER'); ?>"
                                                disabled />
                                        </div>
                                    </div>

                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-4">
                                            <label
                                                class="form-label"><?php echo Text::_('COM_MINIORANGE_SAML_NAME_LABEL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                            <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                type="text"
                                                placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_ATTRIBUTE_NAME_FOR_NAME'); ?>"
                                                disabled />
                                        </div>
                                    </div>
                                </div>

                                <div class="mo_boot_col-sm-6">


                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-4">
                                            <label
                                                class="form-label"><?php echo Text::_('COM_MINIORANGE_SAML_FIRST_NAME_LABEL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                            <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                type="text"
                                                placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_ATTRIBUTE_NAME_FOR_NAME'); ?>"
                                                disabled />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-4">
                                            <label
                                                class="form-label"><?php echo Text::_('COM_MINIORANGE_SAML_LAST_NAME_LABEL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                            <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                type="text"
                                                placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_ATTRIBUTE_NAME_FOR_NAME'); ?>"
                                                disabled />
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <p class="mo_boot_mb-0"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?></strong>
                                <?php echo Text::_('COM_MINIORANGE_SAML_ATTRIBUTE_MAPPING_NOTE'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between"
                            onclick="toggleAdditionalAttributes()" style="cursor: pointer;">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-7">
                                <?php echo Text::_('COM_MINIORANGE_SAML_MAP_ADDITIONAL_USER_ATTRIBUTES'); ?>
                            </h3>
                            <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                                id="additional-attributes-toggle">+</button>
                        </div>

                        <div id="additional-attributes-content" style="display: none;">
                            <div class="mo_boot_row mo_boot_m-2 mo_saml_highlight_background_url_note mo_boot_mt-4">
                                <div class="mo_boot_col-sm-12 mo_boot_m-4">
                                    <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                                        <h4 class="mo_boot_mb-0 mo_boot_col-sm-6 ">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_ADD_JOOMLA_USER_PROFILE_ATTRIBUTES'); ?>
                                        </h4>
                                        <div class="mo_boot_col-sm-2 mo_boot_offset-sm-4 mo_boot_p-0">
                                            <button class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                                                disabled><?php echo Text::_('COM_MINIORANGE_SAML_ADD_BTN'); ?></button>
                                        </div>
                                    </div>

                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-5">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_USER_PROFILE_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <div class="mo_boot_col-sm-5 mo_boot_col-sm-5 mo_boot_offset-sm-1">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_block_cursor"
                                                style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
                                        </div>

                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-5">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_USER_PROFILE_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <div class="mo_boot_col-sm-5 mo_boot_offset-sm-1">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_block_cursor"
                                                style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
                                        </div>
                                    </div>

                                    <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                        <p class="mo_boot_mb-0 small">
                                            <strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?></strong>
                                            <?php echo Text::_('COM_MINIORANGE_SAML_USER_PROFILE_ATTRIBUTES_NOTE'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-2 mo_saml_highlight_background_url_note mo_boot_mt-4">
                                <div class="mo_boot_col-sm-12 mo_boot_m-4">
                                    <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                                        <h4 class="mo_boot_mb-0 mo_boot_col-sm-6 ">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_ADD_JOOMLA_FIELD_ATTRIBUTES'); ?>
                                        </h4>
                                        <div class="mo_boot_col-sm-2 mo_boot_offset-sm-4 mo_boot_p-0">
                                            <button class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                                                disabled><?php echo Text::_('COM_MINIORANGE_SAML_ADD_BTN'); ?></button>
                                        </div>
                                    </div>

                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-5">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_USER_FIELD_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <div class="mo_boot_col-sm-5 mo_boot_offset-sm-1">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_block_cursor"
                                                style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
                                        </div>
                                    </div>

                                    <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                        <p class="mo_boot_mb-0 small">
                                            <strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?></strong>
                                            <?php echo Text::_('COM_MINIORANGE_SAML_FIELD_ATTRIBUTES_NOTE'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mo_boot_row mo_boot_m-2 mo_saml_highlight_background_url_note mo_boot_mt-4">
                                <div class="mo_boot_col-sm-12 mo_boot_m-4">
                                    <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                                        <h4 class="mo_boot_mb-0 mo_boot_col-sm-6">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_ADD_JOOMLA_CONTACT_ATTRIBUTES'); ?>
                                        </h4>
                                        <div class="mo_boot_col-sm-2 mo_boot_offset-sm-4 mo_boot_p-0">
                                            <button class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                                                disabled><?php echo Text::_('COM_MINIORANGE_SAML_ADD_BTN'); ?></button>
                                        </div>
                                    </div>
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-5">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_USER_CONTACT_ATTRIBUTES'); ?>"
                                                    disabled />
                                            </div>
                                            <div class="mo_boot_col-sm-5 mo_boot_offset-sm-1">
                                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                                    type="text"
                                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_ATTRIBUTE'); ?>"
                                                    disabled />
                                            </div>
                                            <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_block_cursor"
                                                style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
                                        </div>
                                    </div>

                                    <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                        <p class="mo_boot_mb-0 small">
                                            <strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?></strong>
                                            <?php echo Text::_('COM_MINIORANGE_SAML_CONTACT_ATTRIBUTES_NOTE'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mo_boot_row m-0 p-0 mo_boot_mt-5">
            <div class="mo_boot_col-sm-12 m-0 p-0 mo_boot_text-center">
                <input type="submit" class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                    value="<?php echo Text::_('COM_MINIORANGE_SAML_ATTRIBUTE_MAPPING_BTN'); ?>" disabled />
            </div>
        </div>
    </div>

    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <h3 class="form-head form-head-bar ">
                    <?php echo Text::_('COM_MINIORANGE_SAML_GROUP_MAPPING'); ?><sup><strong><a href='#' class='premium'
                                onclick="moSAMLUpgrade();"> <img class="crown_img_small mo_boot_mx-2"
                                    src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                </h3>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_saml_mini_section">
                    <div class="mo_boot_col-sm-12 mo_boot_m-3">

                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">a)
                                <?php echo Text::_('COM_MINIORANGE_SAML_ENABLE_GROUP_MAPPING'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="enable_group_mapping" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                    </div>

                    <div class="mo_boot_col-sm-12 mo_boot_m-3 mo_boot_mt-4">


                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">b)
                                <?php echo Text::_('COM_MINIORANGE_SAML_OVERRIDE_ROLE'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="override_role" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                    </div>


                    <div class="mo_boot_col-sm-12 mo_boot_m-3 ">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0  mo_boot_col-sm-6"><?php echo Text::_('COM_MINIORANGE_SAML_APPEND'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="append_roles" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_m-3 mo_boot_mt-4">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">c)
                                <?php echo Text::_('COM_MINIORANGE_SAML_AUTO_CREATE_USERS_IF_ROLES_NOT_MAPPED'); ?>
                            </h3>
                            <label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
                                <input type="checkbox" id="auto_create_users" disabled>
                                <span class="slider mo_saml_block_cursor"></span>
                            </label>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_m-3 mo_boot_mt-4">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">d)
                                <?php echo Text::_('COM_MINIORANGE_SAML_SELECT_DEFAULT_GROUP_FOR_NEW_USERS'); ?>
                            </h3>
                            <div class="mo_boot_col-sm-4">
                                <select class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor" disabled>
                                    <option value="public"><?php echo Text::_('COM_MINIORANGE_SAML_PUBLIC'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_m-3 mo_boot_mt-4">
                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">e)
                                <?php echo Text::_('COM_MINIORANGE_SAML_GROUP_ROLE'); ?>
                            </h3>
                            <div class="mo_boot_col-sm-4">
                                <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor" type="text"
                                    placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_ATTRIBUTE_NAME_FOR_GROUP'); ?>"
                                    disabled />
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_m-3 mo_boot_mt-4">

                        <div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                            <h3 class="mo_boot_mb-0 mo_boot_col-sm-6">f)
                                <?php echo Text::_('COM_MINIORANGE_SAML_ADD_GROUP_MAPPINGS'); ?>
                            </h3>
                            <div class="mo_boot_col-sm-2 mo_boot_offset-sm-3">
                                <button class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                                    disabled><?php echo Text::_('COM_MINIORANGE_SAML_ADD_BTN'); ?></button>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row mo_boot_mt-2">
                                <div class="mo_boot_col-sm-5">
                                    <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor" type="text"
                                        placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_USER_PROFILE_ATTRIBUTE'); ?>"
                                        disabled />
                                </div>
                                <div class="mo_boot_col-sm-5">
                                    <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor" type="text"
                                        placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_ATTRIBUTE'); ?>"
                                        disabled />
                                </div>
                                <div class="mo_boot_col-sm-2">
                                    <i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_saml_trash_icon mo_saml_block_cursor"
                                        style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"
                                        disabled></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mo_boot_row m-0 p-0 mo_boot_mt-5">
            <div class="mo_boot_col-sm-12 m-0 p-0 mo_boot_text-center">
                <input type="submit" class="mo_boot_btn btn_cstm mo_saml_block_cursor"
                    value="<?php echo Text::_('COM_MINIORANGE_SAML_SAVE_GROUP_MAPPING'); ?>" disabled />
            </div>
        </div>
    </div>
    <?php
}

function request_for_demo()
{
    $current_user = Factory::getUser();
    $result = new Mo_saml_Local_Util();
    $result = $result->_load_db_values('#__miniorange_saml_customer_details');
    $admin_email = isset($result['email']) ? $result['email'] : '';
    if ($admin_email == '')
        $admin_email = $current_user->email;

    ?>
    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2 mo_boot_mt-0">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8">
                        <h3 class="mo_saml_form_heading"><?php echo Text::_('COM_MINIORANGE_SAML_TRIAL_TAB'); ?></h3>
                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <div class="alert alert-info mo_boot_mt-0">
                            <i class="fa fa-info-circle mo_boot_mr-2"></i>
                            <span><?php echo Text::_('COM_MINIORANGE_SAML_TRIAL_DESC'); ?></span>
                        </div>

                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_p-4 mo_saml_mini_section">
                            <form name="demo_request" method="post"
                                action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.requestForTrialPlan'); ?>">
                                <input type="hidden" name="option1" value="mo_saml_login_send_query" />

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3">
                                        <label><?php echo Text::_('COM_MINIORANGE_SAML_EMAIL'); ?> :</label>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="email"
                                            class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup" name="email"
                                            value="<?php echo $admin_email; ?>" placeholder="person@example.com" required />
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3">
                                        <label><?php echo Text::_('COM_MINIORANGE_SAML_REQUEST_TRIAL'); ?> :</label>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <select required class="mo-form-control mo-form-control-select mo_saml_proxy_setup"
                                            name="plan">
                                            <option disabled selected class="mo_saml_text_align">-----------------------
                                                <?php echo Text::_('COM_MINIORANGE_SAML_SELECT'); ?> -----------------------
                                            </option>
                                            <option value="Joomla SAML Standard Plugin">Joomla SAML SP Standard Plugin
                                            </option>
                                            <option value="Joomla SAML Premium Plugin">Joomla SAML SP Premium Plugin
                                            </option>
                                            <option value="Joomla SAML Enterprise Plugin">Joomla SAML SP Enterprise Plugin
                                            </option>
                                            <option value="Not Sure"><?php echo Text::_('COM_MINIORANGE_SAML_NOT_SURE'); ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3">
                                        <label><?php echo Text::_('COM_MINIORANGE_SAML_DESC'); ?> :</label>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <textarea name="description"
                                            class="mo_boot_form-text-control mo_saml_proxy_setup mo_saml_description"
                                            rows="7" onkeyup="mo_saml_valid(this)" onblur="mo_saml_valid(this)"
                                            onkeypress="mo_saml_valid(this)" required
                                            placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_TRIAL_ASSISTANCE'); ?>"></textarea>
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                        <button type="submit" class="mo_boot_btn btn_cstm">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_TC_BTN'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function select_identity_provider()
{
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');
    $idp_name = "";
    $idp_entity_id = "";
    $single_signon_service_url = "";
    $name_id_format = "";
    $certificate = "";
    $dynamicLink = "Login with IDP";
    $siteUrl = Uri::root();
    $sp_base_url = $siteUrl;
    $session = Factory::getSession();
    $current_state = $session->get('show_test_config');
    if ($current_state) {
        ?>
        <script>
            jQuery(document).ready(function () {
                var elem = document.getElementById("test-config");
                elem.scrollIntoView();
            });
        </script>
        <?php
        $session->set('show_test_config', false);
    }
    if (isset($attribute['idp_entity_id'])) {
        $idp_name = isset($attribute['idp_name']) ? $attribute['idp_name'] : '';
        $idp_entity_id = $attribute['idp_entity_id'];
        $single_signon_service_url = $attribute['single_signon_service_url'];
        $name_id_format = $attribute['name_id_format'];
        $certificate = $attribute['certificate'];
    }
    $isAuthEnabled = PluginHelper::isEnabled('authentication', 'miniorangesaml');
    $isSystemEnabled = PluginHelper::isEnabled('system', 'samlredirect');
    if (!$isSystemEnabled || !$isAuthEnabled) {
        ?>
        <div id="system-message-container">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <div class="alert alert-error">
                <h4 class="alert-heading">
                    <?php echo Text::_('COM_MINIORANGE_SAML_WARNING'); ?>         <?php echo Text::_('COM_MINIORANGE_SAML_WARNING'); ?>
                </h4>
                <div class="alert-message">
                    <?php echo Text::_('COM_MINIORANGE_SAML_WARNING_MSG'); ?>
                </div>
            </div>
        </div>
        <?php
    }
    $setup_guides = json_decode(SAML_Utilities::setupGuides(), true);
    $guide_count = count($setup_guides);
    ?>

    <div class="mo_boot_col-sm-12 mo_main_saml_section">
        <form action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.saveConfig'); ?>"
            method="post" name="adminForm" id="identity_provider_settings_form" enctype="multipart/form-data">
            <input type="hidden" name="option1" value="mo_saml_save_config">
            <div class="mo_boot_row mo_boot_mt-3 mo_boot_d-flex mo_boot_align-items-center">
                <div class="mo_boot_col-lg-8 mo_boot_p-0">
                    <h3 class="mo_saml_form_heading mo_boot_mb-0">
                        <?php echo Text::_('COM_MINIORANGE_SAML_CHOOSE_METHOD'); ?>
                    </h3>
                </div>
                <a href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=idp'); ?>"
                    class="mo_boot_btn btn_cstm mo_boot_ml-auto">
                    <i class="fa fa-arrow-left"></i> <?php echo Text::_('COM_MINIORANGE_SAML_BACK'); ?>
                </a>

                <ul class="switch_tab_sp mo_boot_text-center mo_boot_p-0 mo_boot_mt-4 ">
                    <li class="mo_saml_current_tab" id="auto_configuration"><a href="#" id="mo_saml_upload_idp_tab"
                            class="mo_saml_bs_btn" onclick="show_metadata_form()"><i class="fa fa-upload"></i>
                            <?php echo Text::_('COM_MINIORANGE_SAML_UPLOAD_METADATA_TAB'); ?></a></li>

                    <li class="" id="manual_configuration"><a href="#" id="mo_saml_idp_manual_tab" class="mo_saml_bs_btn"
                            onclick="hide_metadata_form()"><i class="fa fa-hand-o-up"></i>
                            <?php echo Text::_('COM_MINIORANGE_SAML_MANUAL_CONFIG'); ?></a></li>
                </ul>
            </div>
            <div id="idpdata" class="mo_saml_display_none">
                <div class="mo_boot_row " id="">
                    <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_saml_mini_section">

                        <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_saml_mini_section">
                            <div class="mo_boot_col-sm-12 mo_boot_row mo_boot_m-4 mo_boot_p-0 ">
                                <div
                                    class=" mo_boot_col-sm-12 mo_boot_p-0 mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
                                    <h3 class="mo_saml_form_head mo_boot_col-sm-4">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_BASIC_CONFIG'); ?>
                                    </h3>
                                    <a href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-sp"
                                        target="_blank" class="mo_boot_btn btn_cstm mo_saml_documentation_link">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_GUIDES'); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-4" id="sp_name_idp">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_IDP_NAME'); ?> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <input type="text" class="mo-form-control mo_saml_proxy_setup" name="idp_name"
                                        placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_NAME_PLACEHOLDER'); ?>"
                                        value="<?php echo $idp_name; ?>" />
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-4" id="sp_entity_id_idp">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_IDP_ENTITY_ID'); ?><span
                                            class="mo_saml_required">*</span> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <input type="text" class="mo-form-control mo_boot_was-validated mo_saml_proxy_setup"
                                        name="idp_entity_id"
                                        placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ISSUER_PLACEHOLDER'); ?>"
                                        value="<?php echo $idp_entity_id; ?>"
                                        title="<?php echo Text::_('COM_MINIORANGE_SAML_GUIDES'); ?>" required />
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-4" id="sp_nameid_format_idp">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_NAMEID_FORMAT'); ?> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <select class="mo-form-control mo-form-control-select mo_saml_proxy_setup"
                                        id="name_id_format" name="name_id_format">
                                        <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress" <?php if ($name_id_format == "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress")
                                            echo 'selected = "selected"' ?>>
                                                urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress
                                            </option>
                                            <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" <?php if ($name_id_format == "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified")
                                            echo 'selected = "selected"' ?>>
                                                urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_m-4" id="sp_sso_url_idp">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_IDP_SSO_URL_SERVICE'); ?><span
                                            class="mo_saml_required">*</span> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <input class="mo_boot_was-validated mo-form-control mo_saml_proxy_setup" type="url"
                                        placeholder="Single Sign-On Service URL (Http-Redirect) binding of your IdP"
                                        name="single_signon_service_url" value="<?php echo $single_signon_service_url; ?>"
                                        required />
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-4" id="sp_certificate_idp">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_ADD_CRTI'); ?> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-lg-6">
                                            <div class="mo_boot_d-flex mo_boot_align-items-center">
                                                <span class="mo_boot_mr-3">Enter as text:</span>
                                                <label class="mo_saml_toggle-switch-rect">
                                                    <input type="radio" name="cert" class="form-check-input"
                                                        value="text_cert" CHECKED>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mo_boot_col-lg-6">
                                            <div class="mo_boot_d-flex mo_boot_align-items-center">
                                                <span class="mo_boot_mr-3">Upload Certificate:</span>
                                                <label class="mo_saml_toggle-switch-rect">
                                                    <input type="radio" name="cert" class="form-check-input"
                                                        value="upload_cert">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="upload_cert selectt">
                                        <div class="mo_saml_border">
                                            <input type="file" id="myFile" name="myFile" class="m-2">
                                        </div>
                                        <span id="uploaded_cert"></span>
                                    </div>
                                    <div class="text_cert selectt">
                                        <textarea rows=4 name="certificate" class="mo_boot_col-12 mo_saml_proxy_setup"
                                            placeholder="Format of Certificate
                                ---BEGIN CERTIFICATE---
                                XXXXXXXXXXXXXX
                                ---END CERTIFICATE---"><?php echo $certificate; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_m-4" id="saml_login">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_ENABLE_BTN'); ?> :</span>
                                </div>
                                <div class="mo_boot_col-sm-1 mo_boot_mb-3">
                                    <label class="mo_saml_toggle-switch-rect">
                                        <input type="checkbox" id="login_link_check" name="login_link_check"
                                            onclick="showLink()" value="1" <?php
                                            $count = isset($attribute['login_link_check']) ? $attribute['login_link_check'] : "0";
                                            $dynamicLink = isset($attribute['dynamic_link']) && !empty($attribute['dynamic_link']) ? $attribute['dynamic_link'] : "";
                                            if ($count == 1)
                                                echo 'checked="checked"';
                                            else
                                                $dynamicLink = "Login with your IDP";
                                            ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="mo_boot_col-sm-7 mo_boot_p-0">
                                    <input type="text" id="dynamicText" name="dynamic_link"
                                        placeholder="Enter button name eg. Login with IDP"
                                        value="<?php echo $dynamicLink; ?>" class="mo-form-control mo_boot_p-2">
                                    <?php
                                    if ($count != 1) {
                                        echo '<script>document.getElementById("dynamicText").style.display="none"</script>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-3">
                            </div>
                            <div class="mo_boot_col-sm-9 mo_boot_mt-1">
                            </div>
                            <div class="mo_boot_row mo_boot_m-4">
                                <div class="mo_boot_col-sm-4">
                                    <span><?php echo Text::_('COM_MINIORANGE_SAML_SSO_URL'); ?> :</span>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <div class="mo_saml_highlight_background_url_note mo_boot_p-0">
                                        <div class="mo_boot_row mo_boot_m-2">
                                            <div class="mo_boot_col-10">
                                                <span id="show_sso_url" class="mo_saml_text mo_boot_color">
                                                    <strong><?php echo $sp_base_url . '?morequest=sso'; ?></strong>
                                                </span>
                                            </div>
                                            <div class="mo_boot_col-2">
                                                <em class="fa fa-lg fa-copy mo_copy_sso_login_url mo_copytooltip"
                                                    onclick="copyToClipboard('#show_sso_url');"></em>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>


                        </div>

                    </div>

                    <div class="mo_boot_col-sm-12 mo_boot_p-4 mo_boot_mt-4 mo_saml_mini_section">
                        <div class="mo_saml_mini_section">
                            <div class="mo_saml_main_summary mo_saml_advance_summary" style="cursor: pointer;"
                                onclick="togglePremiumContent('premium-toggle', 'premium-content')">
                                <?php echo Text::_('COM_MINIORANGE_SAML_PREMIUM_VERSIONS_FEATURE'); ?><sup><strong><a
                                            href='#' class='premium' onclick="moSAMLUpgrade();"> <img
                                                class="crown_img_small mo_boot_mx-2"
                                                src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                                <button type="button" class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black"
                                    id="premium-toggle">+</button>
                            </div>
                            <div id="premium-content" class="mo_saml_hidden_content">
                                <div class="mo_boot_row mo_boot_m-4" id="sp_slo_idp">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_IDP_SLO'); ?> </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo_saml_table_textbox mo-form-control mo_saml_block_cursor"
                                            type="text" name="single_logout_url" placeholder="Single Logout URL" disabled>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_m-4">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_SIGN_ALGO'); ?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <select class="mo-form-control mo-form-control-select mo_saml_proxy_setup" readonly>
                                            <option>sha256</option>
                                            <option disabled>sha384</option>
                                            <option disabled>sha512</option>
                                            <option disabled>sha1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_m-4" id="sp_binding_type">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_SELECT_BIND'); ?></span>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="radio" name="miniorange_saml_idp_sso_binding" value="HttpRedirect"
                                            checked=1 aria-invalid="false" class="mo_saml_block_cursor" disabled>
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_BIND_ONE'); ?></span><br>
                                        <input type="radio" name="miniorange_saml_idp_sso_binding" value="HttpPost"
                                            aria-invalid="false" class="mo_saml_block_cursor" disabled>
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_BIND_TWO'); ?> </span>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_m-4" id="sp_saml_request_idp">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_SIGN_SLO'); ?></span>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <div class="mo_boot_col-sm-1">
                                            <label class="mo_saml_toggle-switch-rect">
                                                <input type="checkbox" disabled>
                                                <span class="slider mo_saml_block_cursor"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_m-4" id="sp_saml_context_class">
                                    <div class="mo_boot_col-sm-4">
                                        <span><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT'); ?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <select class="mo-form-control mo-form-control-select mo_saml_proxy_setup" readonly>
                                            <option><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT_O1'); ?></option>
                                            <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT_O2'); ?>
                                            </option>
                                            <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT_O3'); ?>
                                            </option>
                                            <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT_O4'); ?>
                                            </option>
                                            <option disabled><?php echo Text::_('COM_MINIORANGE_SAML_CONTEXT_O5'); ?>
                                            </option>
                                        </select>
                                    </div>
                                </div><br>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_row m-0 p-0 mo_boot_mt-5 mo_boot_col-sm-12 ">
                        <div class="mo_boot_col-sm-12 m-0 p-0 mo_boot_text-center">
                            <input type="hidden"
                                value="<?php echo $sp_base_url . 'administrator/index.php?morequest=sso&q=test_config'; ?>"
                                id="test_config_url">
                            <input type="hidden" value="" id="testarati">
                            <input type="submit" class="mo_boot_btn btn_cstm mo_boot_mt-2"
                                value="<?php echo Text::_('COM_MINIORANGE_SAML_SAVE_BTN'); ?>" />
                            <input type="submit" name="reset_config" class="mo_boot_btn btn_cstm btn_cstm_red mo_boot_mt-2"
                                title="<?php echo Text::_('COM_MINIORANGE_SAML_PROXY_RESET_BTN'); ?>"
                                value="<?php echo Text::_('COM_MINIORANGE_SAML_PROXY_RESET_BTN'); ?>" />
                        </div>
                    </div>
                </div>

            </div>

        </form>

        <form name="f" id="mo_sp_exp_exportconfig" method="post"
            action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.importexport'); ?>">
        </form>
        <div class="mo_boot_row" id="upload_metadata_form">
            <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_saml_mini_section">
                <form
                    action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.handle_upload_metadata'); ?>"
                    name="metadataForm" method="post" id="IDP_meatadata_form" enctype="multipart/form-data">
                    <div class="mo_boot_row mo_boot_m-4">
                        <div class="mo_boot_col-sm-12">
                            <h3 class="mo_saml_form_head">
                                <?php echo Text::_('COM_MINIORANGE_SAML_CHOOSE_UPLOAD_METADATA'); ?>
                            </h3>
                        </div>
                        <div class="mo_boot_col-lg-12 mo_boot_col-sm-12 mo_boot_mt-3">
                            <input id="mo_saml_upload_metadata_form_action" type="hidden" name="option1"
                                value="upload_metadata" />
                            <?php echo Text::_('COM_MINIORANGE_SAML_UPLOAD_MEATADATA_BTN'); ?>
                        </div>
                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-8 mo_boot_mt-3">
                            <input type="hidden" name="action" value="upload_metadata" />
                            <input type="file" id="metadata_uploaded_file" class="mo-form-control-file" name="metadata_file"
                                hidden onchange="updateFileName(this)" />
                            <label for="metadata_uploaded_file" class="mo_saml_file_upload_btn"><?php echo Text::_('COM_MINIORANGE_SAML_CHOOSE_FILE'); ?></label>
                            <span id="file-name" class="mo_saml_file_upload_text"><?php echo Text::_('COM_MINIORANGE_SAML_NO_FILE_UPLOADED'); ?></span>
                            <small class="mo_saml_file_upload_text mo_boot_mt-1 mo_saml_display_block"><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORTED_FILE_TYPE'); ?></small>
                        </div>
                        <div class="mo_boot_col-lg-4 mo_boot_col-sm-12 mo_boot_text-center mo_boot_mt-3">
                            <button type="button" class="mo_boot_btn btn_fetch_metadata" id="upload_metadata_file"
                                name="option1"
                                method="post">&nbsp;&nbsp;<?php echo Text::_('COM_MINIORANGE_SAML_UPLOAD'); ?></button>
                        </div>
                    </div>
                    <div class="text-center metadata_or  mo_boot_col-sm-8 mo_boot_offset-sm-2">
                        <div class="mo_saml_or">
                            <span
                                class="mo_saml_rounded_circle mo_boot_p-2"><?php echo Text::_('COM_MINIORANGE_SAML_OR'); ?></span>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_m-4">

                        <div class="mo_boot_col-lg-12 mo_boot_col-sm-12 ">
                            <input type="hidden" name="action" value="fetch_metadata" />
                            <?php echo Text::_('COM_MINIORANGE_SAML_ENTER_URL'); ?>
                        </div>
                        <div class="mo_boot_col-lg-10 mo_boot_col-sm-8 mo_boot_mt-3">
                            <input type="url" id="metadata_url" name="metadata_url"
                                placeholder=" <?php echo Text::_('COM_MINIORANGE_SAML_ENTER_METADATA_URL'); ?>"
                                class="mo-form-control" required />
                        </div>

                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-12 mo_boot_mt-3">
                            <?php echo Text::_('COM_MINIORANGE_SAML_ENABLE_AUTO_UPDATE'); ?><sup><strong><a href='#'
                                        class='premium' onclick="moSAMLUpgrade();"> <img
                                            class="crown_img_small mo_boot_mx-2"
                                            src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                        </div>
                        <div class="mo_boot_col-lg-4 mo_boot_col-sm-12 mo_boot_text-center mo_boot_mt-3">
                            <select name="sync_interval" class="mo-form-control mo-form-control-select" readonly>
                                <option value="hourly"> <?php echo Text::_('COM_MINIORANGE_SAML_SYNC_HR'); ?></option>
                                <option value="daily" disabled> <?php echo Text::_('COM_MINIORANGE_SAML_SYNC_DAILY'); ?>
                                </option>
                                <option value="weekly" disabled> <?php echo Text::_('COM_MINIORANGE_SAML_SYNC_WEEKLY'); ?>
                                </option>
                                <option value="monthly" disabled> <?php echo Text::_('COM_MINIORANGE_SAML_SYNC_MONTHLY'); ?>
                                </option>
                            </select>
                        </div>
                        <div
                            class="mo_boot_col-lg-4 mo_boot_col-sm-12 mo_boot_offset-lg-6 mo_boot_text-center mo_boot_mt-3">
                            <button type="button" class="mo_boot_btn btn_fetch_metadata" name="option1" method="post"
                                id="fetch_metadata">
                                <?php echo Text::_('COM_MINIORANGE_SAML_FETCH_METADATA1'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
}

function mo_saml_local_support()
{
    $current_user = Factory::getUser();
    $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
    $admin_email = isset($result['email']) ? $result['email'] : '';
    $admin_phone = isset($result['admin_phone']) ? $result['admin_phone'] : '';
    if ($admin_email == '')
        $admin_email = $current_user->email;
    ?>
 

    <div class="mo_boot_col-sm-12 mo_main_saml_section">
    <ul class="switch_tab_sp mo_boot_text-center mo_boot_p-0 mo_boot_mt-4 " id="support">
        <li class="mo_saml_current_tab" id="general_query_tab">
            <a href="#" class="mo_saml_bs_btn" onclick="changeSubMenuSupport('#support', this.closest('li'), '#mo_general_support'); return false;">
                <?php echo Text::_('COM_MINIORANGE_SAML_GENERAL_QUERY'); ?>
            </a>
        </li>

        <li class="" id="setup_meeting_tab">
            <a href="#" class="mo_saml_bs_btn" onclick="changeSubMenuSupport('#support', this.closest('li'), '#mo_screen_share'); return false;">
                <?php echo Text::_('COM_MINIORANGE_SAML_SETUP_SCREEN'); ?>
            </a>
        </li>
    </ul>
    <div class="mo_boot_col-sm-12" id="mo_general_support">
        <div class="mo_boot_row mo_boot_p-0">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
               
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">

                        <div class="alert alert-info mo_boot_mt-0">
                            <i class="fa fa-info-circle mo_boot_mr-2"></i>
                            <span><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_DESC'); ?></span>
                        </div>

                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_p-4 mo_saml_mini_section">
                            <form name="f" method="post"
                                action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.contactUs'); ?>">
                                <input type="hidden" name="option1" value="mo_saml_login_send_query" />

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_EMAIL'); ?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input type="email"
                                            class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup"
                                            name="query_email" value="<?php echo $admin_email; ?>"
                                            placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_ENTER_EMAIL'); ?>"
                                            required />
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_NUMBER'); ?> :</strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input type="text" class="mo_saml_table_textbox mo-form-control mo_saml_proxy_setup"
                                            name="query_phone"
                                            pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})"
                                            value="<?php echo $admin_phone; ?>"
                                            placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_PHONE_PLACEHOLDER'); ?>" />
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mb-4">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_QUERY'); ?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <textarea name="mo_saml_query_support"
                                            class="mo_boot_form-text-control mo_saml_proxy_setup mo_saml_description"
                                            rows="7" required
                                            placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_WRITE_QUERY'); ?>"></textarea>
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                        <button type="submit" name="send_query" class="mo_boot_btn btn_cstm">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_SUBMIT_QUERY'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mo_boot_col-sm-12" id="mo_screen_share" style="display:none">
        <div class="mo_boot_row mo_boot_p-0">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">

                        <div class="alert alert-info mo_boot_mt-0">
                            <i class="fa fa-info-circle mo_boot_mr-2"></i>
                            <span><?php echo Text::_('COM_MINIORANGE_SAML_SUPPORT_DESC'); ?></span>
                        </div>

                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_my-3">
                    <?php
                        $arrContextOptions=array(
                            "ssl"=>array(
                                "verify_peer"=>false,
                                "verify_peer_name"=>false,
                            ),
                        );  
                        
                        $strJsonFileContents = file_get_contents(Uri::root()."/administrator/components/com_miniorange_saml/assets/json/timezones.json", false, stream_context_create($arrContextOptions));
                        $timezoneJsonArray = json_decode($strJsonFileContents, true);

                        ?>
                    <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.meetingSetup'); ?>">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 mo_boot_p-4 mo_saml_mini_section">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_EMAIL');?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input class="mo-form-control mo_boot_px-3 mo_saml_textbox"  type="email" placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_EMAIL_PLACEHOLDER'); ?>"  name="mo_saml_setup_call_email" value="<?php echo $admin_email; ?>"  required>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_ISSUE');?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <select required id="issue_dropdown"  class="mo-form-control mo-form-control-select mo_saml_textbox" name="mo_saml_setup_call_issue" required>
                                            <option value=""><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_SELECT_ISSUE');?></option>
                                            <option id="sso_setup_issue"><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_SSO_SETUP_ISSUE');?></option>
                                            <option><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_CUSTOM_REQUIREMENT');?></option>
                                            <option id="other_issue"><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_OTHER');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_DATE');?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input class="mo-form-control mo_callsetup_table_textbox mo_saml_textbox" name="mo_saml_setup_call_date" type="datetime-local"  id="calldate" required>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_TIME');?><span class="mo_saml_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <select class="mo_callsetup_table_textbox mo_boot_px-2 mo_saml_textbox mo_boot_col-sm-12 mo-form-control mo-form-control-select" name="mo_saml_setup_call_timezone" id="timezone" required>
                                        <?php
                                        foreach($timezoneJsonArray as $data)
                                            {
                                            echo "<option>".$data."</option>";
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_DESCRIPTION');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <textarea id="issue_description" rows="4" class="mo_boot_px-2 form-control mo_boot_col-sm-12" name="mo_saml_setup_call_desc" placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_SETUP_CALL_DESCRIPTION_PLACEHOLDER');?>" ></textarea>
                                    </div>
                                </div>
                                
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                        <button type="submit" name="send_query" class="mo_boot_btn btn_cstm">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_SUBMIT_QUERY'); ?>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>

 
    <?php
}

function add_on_description()
{
    ?>
    <div class="mo_boot_col-sm-12 mo_boot_mx-2">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-lg-5">
                        <h3 class="mo_saml_form_head"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_mt-4">
                    <div class="vtab mo_boot_col-lg-3 mo_boot_col-sm-4">
                        <button class="vtab_btn active py-3" onclick="openTab(event, 'vaddon1')"
                            id="defaultTab"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A1'); ?></button>
                        <button class="vtab_btn py-3"
                            onclick="openTab(event, 'vaddon3')"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A2'); ?></button>
                        <button class="vtab_btn py-3"
                            onclick="openTab(event, 'vaddon4')"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A3'); ?></button>
                        <button class="vtab_btn py-3"
                            onclick="openTab(event, 'vaddon5')"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A4'); ?></button>
                        <button class="vtab_btn py-3"
                            onclick="openTab(event, 'vaddon6')"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A5'); ?></button>
                        <button class="vtab_btn py-3"
                            onclick="openTab(event, 'vaddon7')"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A6'); ?></button>
                    </div>

                    <div class="vtab-box mo_boot_col-lg-9 mo_boot_col-sm-8">
                        <div class="vtab_content p-0 mo_saml_display_block" id="vaddon1">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_A1'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_TAB_CBI'); ?></p>
                            <a href="https://www.miniorange.com/contact" target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>

                        <div class="vtab_content p-0 mo_saml_display_none" id="vaddon3">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON3'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON3_TEXT'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/joomla-scim-user-provisioning" target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>

                        <div class="vtab_content p-0 mo_saml_display_none" id="vaddon4">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON4'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON4_TEXT'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/page-and-article-restriction-for-joomla"
                                target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>

                        <div class="vtab_content p-0 mo_saml_display_none" id="vaddon5">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON5'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON5_TEXT'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/media-restriction-in-joomla" target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>

                        <div class="vtab_content p-0 mo_saml_display_none" id="vaddon6">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON6'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON6_TEXT'); ?>
                            </p>
                            <a href="https://plugins.miniorange.com/role-based-redirection-for-joomla" target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>

                        <div class="vtab_content p-0 mo_saml_display_none" id="vaddon7">
                            <h4 class="vheader"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON7'); ?></h4>
                            <p class="vcontent"><?php echo Text::_('COM_MINIORANGE_SAML_VADDON7_TEXT'); ?>
                            </p>
                            <a href="https://www.miniorange.com/contact" target=_blank><button
                                    class="mo_boot_btn btn_cstm"><?php echo Text::_('COM_MINIORANGE_SAML_ADDON_LEARN_MORE'); ?></button></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
function identity_provider_settings()
{
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');

    $idp_name = isset($attribute['idp_name']) ? $attribute['idp_name'] : '';
    $idp_entity_id = isset($attribute['idp_entity_id']) ? $attribute['idp_entity_id'] : '';
    $siteUrl = Uri::root();
    $sp_base_url = $siteUrl;
    ?>

    <input type="hidden" value="<?php echo $siteUrl . '?morequest=sso&q=test_config'; ?>" id="test_config_url">
    <input type="hidden" value="" id="testarati">

    <div id="idp_list_table" class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-10">
                        <h3 class="mo_saml_form_heading">
                            <?php echo Text::_('COM_MINIORANGE_SAML_IDENTITY_PROVIDER_SETTINGS'); ?>
                        </h3>
                    </div>
                    <div class="mo_boot_col-sm-2 mo_saml_btn_end">
                        <?php
                        $idp_configured = !empty($attribute['idp_entity_id']) && !empty($attribute['single_signon_service_url']);
                        if ($idp_configured) {
                            ?>
                            <button class="mo_boot_btn btn_cstm mo_saml_block_cursor" disabled
                                title="Upgrade to Premium to add multiple IDPs">
                                <i class="fa fa-lock"></i> <?php echo Text::_('COM_MINIORANGE_SAML_ADD_NEW_IDP'); ?>
                                <sup><strong><a href='#' class='premium' onclick="moSAMLUpgrade();"> <img
                                                class="crown_img_small mo_boot_mx-2"
                                                src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                            </button>
                        <?php } else { ?>
                            <a href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=idp&id=new'); ?>"
                                class="mo_boot_btn btn_cstm">
                                <i class="fa fa-plus"></i> <?php echo Text::_('COM_MINIORANGE_SAML_ADD_NEW_IDP'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-4">
                        <div class="input-group">
                            <input type="text" class="mo-form-control" id="idp-search" placeholder="<?php echo Text::_('COM_MINIORANGE_SAML_SEARCH_IDP_PLACEHOLDER'); ?>" />
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_saml_mini_section">
                            <table class="mo_boot_m-0 mo_boot_col-sm-12">
                                <thead>
                                    <tr>
                                        <th class="mo_boot_p-4 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_IDP_NAME'); ?>
                                        </th>
                                        <th class="mo_boot_p-4 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_SSO_URL'); ?>
                                        </th>
                                        <th class="mo_boot_p-4 mo_boot_col-sm-3 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_ACTION'); ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <hr class="mo_boot_m-0">
                                        </td>
                                    </tr>
                                </thead>
                                <tbody id="idp-table-body">
                                    <?php if (!empty($idp_entity_id)): ?>
                                        <tr class="idp-row" data-idp-name="<?php echo htmlspecialchars($idp_name); ?>">
                                            <td>
                                                <div class="mo_saml_idp_name mo_boot_p-4 mo_boot_text-center">
                                                    <strong><?php echo !empty($idp_name) ? htmlspecialchars($idp_name) : 'IDP 1'; ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mo_saml_idp_sso_url mo_boot_p-4 mo_boot_text-center">
                                                    <span title="<?php echo $sp_base_url . '?morequest=sso'; ?>">
                                                        <?php echo $sp_base_url . '?morequest=sso'; ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mo_saml_actions_container mo_boot_pl-lg-5 mo_boot_pr-lg-5">
                                                    <div class="mo_saml_actions_bar mo_saml_mini_section">
                                                        <button class="mo_saml_action_btn mo_saml_mini_section"
                                                            onclick="copySsoUrl('<?php echo $sp_base_url . '?morequest=sso'; ?>')"
                                                            title="<?php echo Text::_('COM_MINIORANGE_SAML_COPY'); ?>">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                        <div class="mo_saml_action_separator"></div>
                                                        <a href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=idp&id=1'); ?>"
                                                            class="mo_saml_action_btn mo_saml_mini_section"
                                                            title="<?php echo Text::_('COM_MINIORANGE_SAML_EDIT'); ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <div class="mo_saml_action_separator"></div>
                                                        <form method="post"
                                                            action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.deleteCurrentIdp'); ?>"
                                                            class="mo_saml_display_inline">
                                                            <input type="hidden" name="idp_id" value="1" />
                                                            <button type="submit"
                                                                class="mo_saml_action_btn mo_saml_mini_section"
                                                                title="<?php echo Text::_('COM_MINIORANGE_SAML_DELETE'); ?>"
                                                                onclick="return confirm('<?php echo Text::_('COM_MINIORANGE_SAML_DELETE_IDP_CONFIRM'); ?>')">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </form>
                                                        <div class="mo_saml_action_separator"></div>
                                                        <button class="mo_saml_test_btn mo_saml_mini_section"
                                                            onclick="showTestWindow()"
                                                            title="<?php echo Text::_('COM_MINIORANGE_SAML_TEST_CONFIG_TITLE'); ?>"
                                                            <?php if (!$idp_entity_id)
                                                                echo "disabled"; ?>>
                                                            Test
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center mo_saml_no_data">
                                                <div class="mo_saml_empty_state">
                                                    <i class="fa fa-users fa-3x"></i>
                                                    <p><?php echo Text::_('COM_MINIORANGE_SAML_NO_IDP_CONFIGURED'); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-4">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-10">
                                <p><?php echo Text::_('COM_MINIORANGE_SAML_SWITCHING_ENVIRONMENTS'); ?></p>
                                <p><?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG_HERE'); ?></p>
                            </div>
                            <div class="mo_boot_col-sm-2 text-right">
                                <button class="mo_boot_btn btn_cstm" onclick="showImportExportConfig()">
                                    <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function import_export_configuration()
{
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');
    $idp_entity_id = isset($attribute['idp_entity_id']) ? $attribute['idp_entity_id'] : '';
    ?>
    <div id="mo_saml_import_export_id" class="mo_boot_col-sm-12 mo_main_saml_section mo_saml_display_none">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-10">
                        <h3 class="mo_saml_form_heading"><?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_CONFIG'); ?>
                        </h3>
                    </div>
                    <div class="mo_boot_col-sm-4 text-right">
                        <button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_saml_toggle_btn_black" onclick="backToIdpList()" id="back_to_idp_list">
                            <i class="fa fa-arrow-left"></i>
                        </button>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_p-4 mo_saml_mini_section">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-8">
                                    <h3 class="mo_saml_form_heading">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_EXPORT_CONFIG'); ?>
                                    </h3>
                                    <p class="mo_saml_config_text">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_EXPORT_CONFIG_DESC'); ?>
                                    </p>
                                </div>
                                <div class="mo_boot_col-sm-4 text-right">
                                    <button type="button" class="mo_boot_btn btn_cstm mo_boot_mt-2" <?php if ($idp_entity_id)
                                        echo "enabled";
                                    else
                                        echo "disabled"; ?>
                                        onclick="jQuery('#mo_sp_exp_exportconfig').submit();">
                                        <i class="fa fa-upload"></i>
                                        <?php echo Text::_('COM_MINIORANGE_SAML_EXPORT_CONFIG'); ?>
                                    </button>
                                    <form name="f" id="mo_sp_exp_exportconfig" method="post"
                                        action="<?php echo Route::_('index.php?option=com_miniorange_saml&task=myaccount.importexport'); ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_p-4 mo_saml_mini_section">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-8">
                                    <h3 class="mo_saml_form_heading">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_CONFIG'); ?> <sup><strong><a href='#'
                                                    class='premium' onclick="moSAMLUpgrade();"> <img
                                                        class="crown_img_small mo_boot_mx-2"
                                                        src="<?php echo Uri::base(); ?>/components/com_miniorange_saml/assets/images/crown.webp"></a></strong></sup>
                                    </h3>
                                    <p class="mo_saml_config_text">
                                        <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_CONFIG_DESC'); ?>
                                    </p>

                                    <div class="mo_boot_col-lg-12 mo_boot_col-sm-8 mo_boot_mt-3">
                                        <label for="metadata_uploaded_file" class="mo_saml_file_upload_btn"><?php echo Text::_('COM_MINIORANGE_SAML_CHOOSE_FILE'); ?></label>
                                        <span id="file-name" class="mo_saml_file_upload_text"><?php echo Text::_('COM_MINIORANGE_SAML_NO_FILE_UPLOADED'); ?></span>
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-4 text-right">
                                    <button type="button" class="mo_boot_btn btn_cstm" disabled>
                                        <i class="fa fa-download"></i> <?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_CONFIG'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <p><?php echo Text::_('COM_MINIORANGE_SAML_IMPORT_EXPORT_NOTE'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}


function identity_provider_mapping()
{
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');

    $idp_name = isset($attribute['idp_name']) ? $attribute['idp_name'] : '';
    $idp_entity_id = isset($attribute['idp_entity_id']) ? $attribute['idp_entity_id'] : '';
    $siteUrl = Uri::root();
    $sp_base_url = $siteUrl;
    ?>

    <input type="hidden" value="<?php echo $siteUrl . '?morequest=sso&q=test_config'; ?>" id="test_config_url">
    <input type="hidden" value="" id="testarati">

    <div id="idp_list_table" class="mo_boot_col-sm-12 mo_main_saml_section">
        <div class="mo_boot_row mo_boot_p-2">
            <div class="mo_boot_col-sm-12 mo_boot_px-2">
                <div class="mo_boot_row mo_boot_mb-4">
                    <div class="mo_boot_col-sm-8">
                        <h3 class="mo_saml_form_heading">
                            <?php echo Text::_('COM_MINIORANGE_SAML_IDENTITY_PROVIDER_SETTINGS'); ?>
                        </h3>
                    </div>
                </div>

                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_saml_mini_section">
                            <table class="mo_boot_m-0 mo_boot_col-sm-12">
                                <thead>
                                    <tr>
                                        <th class="mo_boot_p-4 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_IDP_NAME'); ?>
                                        </th>
                                        <th class="mo_boot_p-4 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_SSO_URL'); ?>
                                        </th>
                                        <th class="mo_boot_p-4 mo_boot_col-sm-3 mo_boot_text-center">
                                            <?php echo Text::_('COM_MINIORANGE_SAML_ACTION'); ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <hr class="mo_boot_m-0">
                                        </td>
                                    </tr>
                                </thead>
                                <tbody id="idp-table-body">
                                    <?php if (!empty($idp_entity_id)): ?>
                                        <tr class="idp-row" data-idp-name="<?php echo htmlspecialchars($idp_name); ?>">
                                            <td>
                                                <div class="mo_saml_idp_name mo_boot_p-4 mo_boot_text-center">
                                                    <strong><?php echo !empty($idp_name) ? htmlspecialchars($idp_name) : 'IDP 1'; ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mo_saml_idp_sso_url mo_boot_p-4 mo_boot_text-center">
                                                    <span title="<?php echo $sp_base_url . '?morequest=sso'; ?>">
                                                        <?php echo $sp_base_url . '?morequest=sso'; ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mo_saml_actions_container mo_boot_pl-lg-5 mo_boot_pr-lg-5">
                                                    <div class="mo_saml_actions_bar mo_saml_mini_section">
                                                        <a href="<?php echo Route::_('index.php?option=com_miniorange_saml&tab=attribute_mapping&id=1'); ?>"
                                                            class="mo_saml_action_btn mo_saml_mini_section"
                                                            title="<?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE'); ?>">
                                                            <i class="fa fa-cog mo_boot_mr-2"></i>
                                                            <?php echo Text::_('COM_MINIORANGE_SAML_CONFIGURE'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center mo_saml_no_data">
                                                <div class="mo_saml_empty_state">
                                                    <i class="fa fa-users fa-3x"></i>
                                                    <p><?php echo Text::_('COM_MINIORANGE_SAML_NO_IDP_CONFIGURED'); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
