START TRANSACTION;

ALTER TABLE tagente_modulo DROP COLUMN IF EXISTS id_policy_module;
ALTER TABLE tagente_modulo DROP COLUMN IF EXISTS policy_linked;
ALTER TABLE tagente_modulo DROP COLUMN IF EXISTS policy_adopted;
ALTER TABLE talert_template_modules DROP INDEX IF EXISTS id_agent_module, DROP COLUMN IF EXISTS id_policy_alerts, ADD UNIQUE (id_agent_module, id_alert_template);
ALTER TABLE tusuario_perfil DROP COLUMN IF EXISTS id_policy;
ALTER TABLE tnetwork_map DROP COLUMN IF EXISTS id_policy;
ALTER TABLE tnetwork_map DROP COLUMN IF EXISTS hide_policy_modules;
ALTER TABLE ttag_module DROP COLUMN IF EXISTS id_policy_module;
DROP TABLE IF EXISTS ttag_policy_module;
DROP TABLE IF EXISTS tpolicy_modules_synth;
DROP TABLE IF EXISTS tpolicy_modules;
DROP TABLE IF EXISTS tpolicy_collections;
DROP TABLE IF EXISTS tpolicy_modules_inventory;
DROP TABLE IF EXISTS tpolicy_group_agents;
DROP TABLE IF EXISTS tpolicy_alerts_actions;
DROP TABLE IF EXISTS tpolicy_alerts;
DROP TABLE IF EXISTS tpolicy_agents;
DROP TABLE IF EXISTS tpolicy_groups;
DROP TABLE IF EXISTS tpolicies;
DROP TABLE IF EXISTS tservice;
DROP TABLE IF EXISTS tservice_element;
DROP TABLE IF EXISTS tpolicy_plugins;
DROP TABLE IF EXISTS tpolicy_queue;
ALTER TABLE `tautoconfig_actions` MODIFY COLUMN `action_type` ENUM('set-group', 'set-secondary-group', 'launch-script', 'launch-event', 'launch-alert-action', 'raw-config') DEFAULT 'launch-event';
ALTER TABLE tagent_filter DROP COLUMN IF EXISTS policies;

UPDATE tconfig SET `value`='1.0.0' WHERE `token`='db_scheme_first_version';
UPDATE tconfig SET `value`='1.0.0' WHERE `token`='db_scheme_version';
UPDATE tconfig SET `value`='PD260303' WHERE `token`='db_scheme_build';
UPDATE tconfig SET `value`='N/A' WHERE `token`='lts_name';
UPDATE tconfig SET `value`=0 WHERE `token`='lts_updates';
DELETE FROM tconfig WHERE `token`='meta_style';

DELETE FROM tconfig_os WHERE `name`='Satellite';
DELETE FROM tconfig_os WHERE `name`='Mainframe';

DELETE FROM tlink WHERE `name`='Get support';
DELETE FROM tlink WHERE `name`='Module library';

DELETE FROM ttipo_modulo WHERE `nombre`='web_analysis';
DELETE FROM ttipo_modulo WHERE `nombre`='web_data';
DELETE FROM ttipo_modulo WHERE `nombre`='web_proc';
DELETE FROM ttipo_modulo WHERE `nombre`='web_content_data';
DELETE FROM ttipo_modulo WHERE `nombre`='web_content_string';
DELETE FROM ttipo_modulo WHERE `nombre`='web_server_status_code_string';

UPDATE tnews SET `subject`='Welcome&#x20;to&#x20;Pandora&#x20;Open&#x20;Console' WHERE `subject`='Welcome&#x20;to&#x20;Pandora&#x20;FMS&#x20;Console';

DELETE FROM trecon_script WHERE `name`='Discovery.Application.VMware';
DELETE FROM trecon_script WHERE `name`='Discovery.Cloud';
DELETE FROM trecon_script WHERE `name`='IPAM&#x20;Recon';

DELETE FROM tmodule WHERE `name`='Web&#x20;module';
DELETE FROM tmodule WHERE `name`='Wux&#x20;module';

UPDATE ttag SET `url`='https://pandoraopen.io' WHERE `description`='Network&#x20;equipment';

DELETE FROM twidget WHERE `class_name`='WuxWidget';
DELETE FROM twidget WHERE `class_name`='WuxStatsWidget';
DELETE FROM twidget WHERE `class_name`='SecurityHardening';
DELETE FROM twidget WHERE `class_name`='ITSMIncidences';

UPDATE `twelcome_tip` SET `text` = REPLACE(`text`, 'Pandora&#x20;FMS', 'Pandora&#x20;Open') WHERE `text` LIKE '%Pandora&#x20;FMS%';
UPDATE `twelcome_tip` SET `url` = REPLACE(`url`, 'https://pandorafms.com/manual/', 'https://pandoraopen.io/manual/') WHERE `url` LIKE 'https://pandorafms.com/manual/%';
UPDATE `twelcome_tip` SET `url` = REPLACE(`url`, 'https://pandorafms.com/', 'https://pandoraopen.io/') WHERE `url` LIKE 'https://pandorafms.com/%';
UPDATE `twelcome_tip` SET `url` = 'https://pandoraopen.io' WHERE `id` IN (5, 8, 9, 10, 11, 12, 15, 16);

DELETE FROM `twelcome_tip` WHERE `id` IN (1, 3, 6, 13, 22, 23);

DELETE FROM twelcome_tip_file WHERE `filename`='monitorizar_web.png';
DELETE FROM twelcome_tip_file WHERE `filename`='monitorizar_desde_ip.png';
DELETE FROM twelcome_tip_file WHERE `filename`='monitorizar_con_jmx.png';
DELETE FROM twelcome_tip_file WHERE `filename`='politica_de_pass.png';

COMMIT;