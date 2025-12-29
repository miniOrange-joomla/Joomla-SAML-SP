CREATE TABLE IF NOT EXISTS `#__miniorange_saml_customer_details` (
`id` int(11) UNSIGNED NOT NULL,
`email` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(50)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`licenseExpiry` TIMESTAMP NULL DEFAULT NULL,
`supportExpiry` TIMESTAMP NULL DEFAULT NULL,
`licensePlan` VARCHAR(64) NOT NULL,
`login_status` tinyint(1) DEFAULT FALSE,
`status` VARCHAR(255) NOT NULL,
`sml_lk` VARCHAR(128) NOT NULL,
`in_cmp` VARCHAR(255) NOT NULL,
`enable_redirect` BOOLEAN DEFAULT FALSE,
`enable_manager_login` BOOLEAN DEFAULT FALSE,
`enable_admin_redirect` BOOLEAN DEFAULT FALSE,
`sp_base_url` VARCHAR(255),
`sp_entity_id` VARCHAR(255) ,
`organization_name` VARCHAR(128) ,
`organization_display_name` VARCHAR(128) ,
`organization_url` VARCHAR(128),
`tech_per_name` VARCHAR(128) ,
`tech_email_add` VARCHAR(128) ,
`support_per_name` VARCHAR(128),
`support_email_add` VARCHAR(128),
`trists` TEXT NOT NULL,
`enable_email` BOOLEAN NOT NULL,
`enable_do_not_auto_create_users` BOOLEAN DEFAULT FALSE,
`miniorange_fifteen_days_before_lexp` tinyint default 0,
`miniorange_five_days_before_lexp` tinyint default 0,
`miniorange_after_lexp` tinyint default 0,
`miniorange_after_five_days_lexp` tinyint default 0,
`miniorange_lexp_notification_sent` tinyint default 0,
`auto_send_email_time` TEXT,
`admin_email` VARCHAR(255)  NOT NULL ,
`mo_cron_period` VARCHAR(255)  NOT NULL ,
`usrlmt` VARCHAR(255) DEFAULT 'MTAK',
`mail_sent` tinyint(1) NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `#__miniorange_saml_proxy_setup` (
`id` INT(11) UNSIGNED NOT NULL ,
`password` VARCHAR(255) NOT NULL ,
`proxy_host_name` VARCHAR(255) NOT NULL ,
`port_number` VARCHAR(255) NOT NULL ,
`username` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_config` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`idp_name` VARCHAR(255),
`idp_entity_id` VARCHAR(255),
`saml_request_sign` VARCHAR(32),
`single_signon_service_url` VARCHAR(255),
`binding` VARCHAR(255),
`name_id_format` VARCHAR(255),
`default_relay_state` VARCHAR(255),
`certificate` VARCHAR(4096),
`username` VARCHAR(255) ,
`email` VARCHAR(255)  ,
`grp` VARCHAR(255) ,
`login_link_check` boolean DEFAULT true,
`dynamic_link` VARCHAR(255),
`uninstall_feedback` int(2) ,
`userslim` VARCHAR(255) DEFAULT 'MAo=',
`test_configuration` boolean DEFAULT false,
`sso_status` boolean DEFAULT false,
`sso_var` VARCHAR(255) DEFAULT 'NjAK',
`sso_test` VARCHAR(255) DEFAULT 'MAo=',
`close_admintool_popup` int(11),
`default_logout_redirect_url` VARCHAR(255),
`name` VARCHAR(255) DEFAULT NULL,
`first_name` VARCHAR(128)  NULL,
`last_name` VARCHAR(128)  NULL,
`email_error` VARCHAR(355),
`page_restricted_urls` text,
`single_logout_url` TEXT   NULL ,
`user_profile_attributes` TEXT,
`disable_update_existing_users_attribute` int(11) UNSIGNED NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_role_mapping` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`mapping_value_default` VARCHAR(255)  NULL,
`role_mapping_count` int(11) UNSIGNED  NULL,
`mapping_memberof_attribute` VARCHAR(255)  NULL,
`role_mapping_key_value` text NULL,
`enable_saml_role_mapping` int(11) UNSIGNED NULL,
`idp_id` int(11) NOT NULL,
`do_not_auto_create_users` int(11),
`disable_existing_users_role_update` BOOLEAN NOT NULL default 0,
`update_existing_users_role_without_removing_current` BOOLEAN NOT NULL default 0,
`enable_role_based_redirection` BOOLEAN NOT NULL default 0,
`role_based_redirect_key_value`  text NOT NULL,
`grp` VARCHAR(255) NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_idp_attributes` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`attributes` TEXT NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;



INSERT IGNORE INTO `#__miniorange_saml_proxy_setup`(`id`) values (1) ;
INSERT IGNORE INTO `#__miniorange_saml_customer_details`(`id`,`login_status`,`organization_name`,`organization_display_name`,`organization_url`,`tech_per_name`,`tech_email_add`,`support_per_name`,`support_email_add`,`mail_sent`) values (1,0,'miniOrange','miniOrange','https://miniorange.com','miniOrange','joomlasupport@xecurify.com','miniOrange','joomlasupport@xecurify.com',0) ;
INSERT IGNORE INTO `#__miniorange_saml_role_mapping`(`id`,`mapping_value_default`,`idp_id`) values (1,'memberOf',1);
