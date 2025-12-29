<?php
/**
 * @package     Joomla.Package
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

class pkg_MiniorangeSAMLSSOInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent)
    {


    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent)
    {
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent)
    {

    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent)
    {
        if ($type == 'uninstall') {
            return true;
        }
        $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages = array())
    {
        jimport('miniorangesamlplugin.utility.SAML_Utilities');
        $PluginVersion = SAML_Utilities::GetPluginVersion();
        $PluginType = SAML_Utilities::getpluginType();
        ?>


        <style>
            .mo-row {
                width: 100%;
                display: block;
                margin-bottom: 2%;
            }

            .mo-row:after {
                clear: both;
                display: block;
                content: "";
            }

            .mo-column-2 {
                width: 19%;
                margin-right: 1%;
                float: left;
            }

            .mo-column-10 {
                width: 80%;
                float: left;
            }

            .btn {
                display: inline-block;
                font-weight: 300;
                text-align: center;
                vertical-align: middle;
                user-select: none;
                background-color: transparent;
                border: 1px solid transparent;
                padding: 4px 12px;
                font-size: 0.85rem;
                line-height: 1.5;
                border-radius: 0.25rem;
                transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            .btn-cstm {
                background-color: #3D618F;
                border: 1px solid #3D618F;
                border-radius: var(--border-radius-sm, 0.25rem);
                font-size: 1.1rem;
                padding: 0.3rem 1.5rem;
                cursor: pointer;
                transition: all var(--transition-duration, 0.15s) ease-in-out;
                color: var(--white, #ffffff) !important;
            }

            .btn-cstm:hover {
                background: #3D618F;
                color: var(--white, #ffffff) !important;
                border: 1px solid #3D618F;
            }


            /* Dark background button styles */
            :root[data-color-scheme=dark] {
                .btn-cstm {
                    color: white;
                    background-color: #007DB0;
                    border-color: 1px solid #ffffff;
                }

                .btn-cstm:hover {
                    background-color: #007DB0;
                    border-color: #ffffff;
                }
            }

            a[target=_blank]:before {
                display: none;
            }
        </style>
        <?php
        if ($PluginType == 'ALL') {
            ?>
            <strong>miniOrange SAML SP plugin</strong>
            <p>Our plugin is compatible with Joomla 3, 4, 5 and 6. Additionally, it integrates with all the SAML 2.0 compliant
                Identity
                Providers.</p>
            <div class="mo-row">Current Plugin Version: <?php echo $PluginVersion; ?></div>
            <h4>Steps to use the SAML SP plugin.</h4>
            <ul>
                <li>Click on Components</li>
                <li>Click on miniOrange SAML Single Sign-On and select Service Provider Setup tab</li>
                <li>You can start configuring.</li>
            </ul>
            <?php
        } else if ($PluginType == 'ADFS') {
            ?>
                <p>SAML SP Single Sign On - Login with ADFS</p>
                <p>Our plugin is compatible with Joomla 3, 4, 5 and 6. Additionally, it integrates with all the SAML 2.0 compliant Identity
                    Providers.</p>
                <h4>Steps to use the SAML SP plugin.</h4>
                <ul>
                    <li>Click on Components</li>
                    <li>Click on miniOrange SAML Single Sign-On and select Service Provider Setup tab</li>
                    <li>You can start configuring.</li>
                </ul>
            <?php
        } else if ($PluginType == 'GOOGLEAPPS') {
            ?>
                    <p>SAML SP Single Sign On - Login with Google Apps</p>
                    <p>Our plugin is compatible with Joomla 3, 4, 5 and 6. Additionally, it integrates with all the SAML 2.0 compliant Identity
                        Providers.</p>
                    <h4>Steps to use the SAML SP plugin.</h4>
                    <ul>
                        <li>Click on Components</li>
                        <li>Click on miniOrange SAML Single Sign-On and select Service Provider Setup tab</li>
                        <li>You can start configuring.</li>
                    </ul>
            <?php
        }
        ?>
        <div class="mo-row">
            <a class="btn btn-cstm" onClick="window.location.reload();"
                href="index.php?option=com_miniorange_saml&tab=overview">Get Started!</a>
            <a class="btn btn-cstm" href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-sp"
                target="_blank">Setup Guides</a>
            <a class="btn btn-cstm" href="https://www.miniorange.com/contact" target="_blank">Get Support! </a>
            <a class="btn btn-cstm" href="https://plugins.miniorange.com/joomla-saml-sso-changelog-free" target="_blank">Change
                Logs! </a>
        </div>
        <?php
    }

}