<?php
/**
 * Pandora FMS OpenSource
 * Copyright (c) 2004-2025 Pandora FMS Community
 * https://pandorafms.org
 *
 * Este programa es software libre; puedes redistribuirlo y/o modificarlo bajo
 * los términos de la Licencia Pública General de GNU publicada por la Free
 * Software Foundation para la versión 2. Este programa se distribuye con la
 * esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; ni siquiera con la
 * garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO
 * PARTICULAR. Consulta la Licencia Pública General de GNU para más detalles.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation for version 2. This program is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * Эта программа является свободным программным обеспечением; вы можете
 * распространять и/или изменять её в соответствии с условиями Стандартной
 * общественной лицензии GNU (GPL), опубликованной Фондом свободного
 * программного обеспечения (Free Software Foundation) для версии 2. Эта
 * программа распространяется в надежде, что она будет полезной, НО БЕЗ
 * КАКИХ-ЛИБО ГАРАНТИЙ, даже без подразумеваемой гарантии КОММЕРЧЕСКОЙ
 * ПРИГОДНОСТИ или ПРИГОДНОСТИ ДЛЯ КОНКРЕТНОЙ ЦЕЛИ. Подробнее см. Стандартную
 * общественную лицензию GNU.
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier selon les termes de la Licence Publique Générale GNU, publiée par
 * la Free Software Foundation pour la version 2. Ce programme est distribué
 * dans l'espoir qu'il sera utile, mais SANS AUCUNE GARANTIE, même sans la
 * garantie implicite de QUALITÉ MARCHANDE ou D'ADÉQUATION À UN USAGE
 * PARTICULIER. Consultez la Licence Publique Générale GNU pour plus de détails.
 *
 * このプログラムはフリーソフトウェアです。GNU一般公衆利用許諾書
 * （Free Software Foundationによって公開されたバージョン2）の条件の下で、
 * 自由に再配布および改変することができます。本プログラムは有用であることを
 * 願って配布されますが、いかなる保証もありません。商品性や特定目的への適合性の
 * 保証も含まれません。詳しくはGNU一般公衆利用許諾書をご覧ください。
 * ============================================================================
 */

use PandoraFMS\Tools\Files;
use PandoraFMS\Agent;

global $config;

require_once $config['homedir'].'/include/functions_db.php';
require_once $config['homedir'].'/include/functions_io.php';
require_once $config['homedir'].'/include/functions_notifications.php';
require_once $config['homedir'].'/include/functions_servers.php';
require_once $config['homedir'].'/vendor/autoload.php';

/**
 * Base class to run scheduled tasks in cron extension
 */
class ConsoleSupervisor
{

    /**
     * Minimum modules to check performance.
     */
    public const MIN_PERFORMANCE_MODULES = 100;


    /**
     * Minimum queued elements in synchronization queue to be warned..
     */
    public const MIN_SYNC_QUEUE_LENGTH = 200;

    /**
     * Icons for notifications.
     */
    public const ICON_CONGRATS = 'images/notification/congrats.svg';
    public const ICON_DISABLE = 'images/notification/disable.svg';
    public const ICON_ERROR = 'images/notification/error.svg';
    public const ICON_FAVORITE = 'images/notification/favorite.svg';
    public const ICON_HEADSUP = 'images/notification/headsup.svg';
    public const ICON_INFORMATION = 'images/notification/information.svg';
    public const ICON_POPULAR = 'images/notification/popular.svg';
    public const ICON_QUESTION = 'images/notification/question.svg';

    /**
     * Show if console supervisor is enabled or not.
     *
     * @var boolean
     */
    public $enabled;

    /**
     * Value of 'id' from tnotification_source
     * where description is 'System status'
     *
     * @var integer
     */
    public $sourceId;

    /**
     * Target groups to be notified.
     *
     * @var array
     */
    public $targetGroups;

    /**
     * Target users to be notified.
     *
     * @var array
     */
    public $targetUsers;

    /**
     * Targets up to date.
     *
     * @var boolean
     */
    public $targetUpdated;

    /**
     * Show messages or not.
     *
     * @var boolean
     */
    public $interactive;


    /**
     * Constructor.
     *
     * @param boolean $interactive Show output while executing or not.
     *
     * @return class This object
     */
    public function __construct(bool $interactive=true)
    {
        $source = db_get_row(
            'tnotification_source',
            'description',
            io_safe_input('System status')
        );

        $this->interactive = $interactive;

        if ($source === false) {
            $this->enabled = false;
            $this->sourceId = null;

            $this->targetGroups = null;
            $this->targetUsers = null;
        } else {
            $this->enabled = (bool) $source['enabled'];
            $this->sourceId = $source['id'];
        }

        return $this;
    }


    /**
     * Warn a message.
     *
     * @param string $msg Message.
     *
     * @return void
     */
    public function warn(string $msg)
    {
        if ($this->verbose === true) {
            echo date('M  j G:i:s').' ConsoleSupervisor: '.$msg."\n";
        }
    }


    /**
     * Manage scheduled tasks (basic).
     *
     * @return void
     */
    public function runBasic()
    {

        /*
         * PHP configuration warnings:
         *  NOTIF.PHP.SAFE_MODE
         *  NOTIF.PHP.INPUT_TIME
         *  NOTIF.PHP.EXECUTION_TIME
         *  NOTIF.PHP.UPLOAD_MAX_FILESIZE
         *  NOTIF.PHP.MEMORY_LIMIT
         *  NOTIF.PHP.DISABLE_FUNCTIONS
         *  NOTIF.PHP.VERSION
         *  NOTIF.PHP.VERSION.SUPPORT
         */

        $this->checkPHPSettings();

        /*
         * Check component statuses (servers down - frozen).
         *  NOTIF.SERVER.STATUS.ID_SERVER
         */

        $this->checkPandoraServers();

        /*
         * Check at least 1 server running in master mode.
         *  NOTIF.SERVER.MASTER
         */

        $this->checkPandoraServerMasterAvailable();

        /*
         * Check if CRON is running.
         *  NOTIF.CRON.CONFIGURED
         */

        $this->checkCronRunning();

        /*
         * Check if has API access.
         *  NOTIF.API.ACCESS
         */
        $this->checkApiAccess();

        /*
         * Check if AllowOverride is None or All.
         *  NOTIF.ALLOWOVERIDE.MESSAGE
         */

        $this->checkAllowOverrideEnabled();

        /*
         * Check if the Pandora Console log
         * file remains in old location.
         *  NOTIF.PANDORACONSOLE.LOG.OLD
         */

        $this->checkPandoraConsoleLogOldLocation();

        /*
         * Check if the audit log file
         * remains in old location.
         *  NOTIF.AUDIT.LOG.OLD
         */

        $this->checkAuditLogOldLocation();

        /*
         * Check if performance variables are corrects
         */

        $this->checkPerformanceVariables();

        /*
         * Checks if sync queue is longer than limits.
         *  NOTIF.SYNCQUEUE.LENGTH
         */

        

        /*
         * Checkc agent missing libraries.
         * NOTIF.AGENT.LIBRARY
         */

        /*
         * Check MYSQL Support Version
         */

        $this->checkMYSQLSettings();

        /*
         * Check total modules in system
         */

         $this->checkTotalModules();

        /*
         * Check total modules by agent
         */

         $this->checkTotalModulesByAgent();
    }


    /**
     * Manage scheduled tasks.
     *
     * @return void
     */
    public function run()
    {
        global $config;

        $this->maintenanceOperations();

        if ($this->enabled === false) {
            // Notifications not enabled.
            return;
        }

        if ($this->sourceId === null) {
            // Source not detected.
            return;
        }

        // Automatic checks launched by supervisor.
        $this->warn('running.');

        /*
         * Check number of files in attachment:
         *  NOTIF.FILES.ATTACHMENT
         */

        $this->checkAttachment();

        /*
         * Files in data_in:
         *  NOTIF.FILES.DATAIN  (>1000)
         *  NOTIF.FILES.DATAIN.BADXML (>150)
         */

        $this->checkDataIn();

        /*
         * Check module queues not growing:
         *  NOTIF.SERVER.QUEUE.ID_SERVER
         */

        $this->checkServers();

        /*
         * Check component statuses (servers down - frozen).
         *  NOTIF.SERVER.STATUS.ID_SERVER
         */

        $this->checkPandoraServers();

        /*
         * Check at least 1 server running in master mode.
         *  NOTIF.SERVER.MASTER
         */

        $this->checkPandoraServerMasterAvailable();

        /*
         * PHP configuration warnings:
         *  NOTIF.PHP.SAFE_MODE
         *  NOTIF.PHP.INPUT_TIME
         *  NOTIF.PHP.EXECUTION_TIME
         *  NOTIF.PHP.UPLOAD_MAX_FILESIZE
         *  NOTIF.PHP.MEMORY_LIMIT
         *  NOTIF.PHP.DISABLE_FUNCTIONS
         *  NOTIF.PHP.VERSION
         *  NOTIF.PHP.VERSION.SUPPORT
         */

        $this->checkPHPSettings();

        /*
         * Check pandoradb running in main DB.
         * Check pandoradb running in historical DB.
         *  NOTIF.PANDORADB
         *  NOTIF.PANDORADB.HISTORICAL
         */

        $this->checkPandoraDBMaintenance();

        /*
         * Check external components.
         *  NOTIF.EXT.ELASTICSEARCH
         *  NOTIF.EXT.LOGSTASH
         *
         */

        $this->checkExternalComponents();

        /*
         * Check incoming scheduled downtimes (< 15d).
         *  NOTIF.DOWNTIME
         */

        $this->checkDowntimes();

        /*
         * Check if has API access.
         *  NOTIF.API.ACCESS
         */
        $this->checkApiAccess();

        /*
         * Check if event storm protection is activated.
         *  NOTIF.MISC.EVENTSTORMPROTECTION
         */

        $this->checkEventStormProtection();

        /*
         * Check if develop_bypass is enabled.
         *  NOTIF.MISC.DEVELOPBYPASS
         */

        $this->checkDevelopBypass();

        /*
         * Check if fontpath exists.
         *  NOTIF.MISC.FONTPATH
         */

        $this->checkFont();

        /*
         * Check if default user and password exists.
         *  NOTIF.SECURITY.DEFAULT_PASSWORD
         */

        $this->checkDefaultPassword();

        /*
         * Check if CRON is running.
         *  NOTIF.CRON.CONFIGURED
         */

        $this->checkCronRunning();


        /*
         * Check if has API access.
         *  NOTIF.API.ACCESS
         */
        $this->checkApiAccess();

        /*
         * Check if AllowOverride is None or All.
         *  NOTIF.ALLOWOVERRIDE.MESSAGE
         */

        $this->checkAllowOverrideEnabled();

        /*
         * Check if HA status.
         */

        /*
         * Check if the audit log file
         * remains in old location.
         */

        $this->checkAuditLogOldLocation();

        /*
         * Check if performance variables are corrects
         */
        $this->checkPerformanceVariables();

        /*
         * Checks if sync queue is longer than limits.
         *  NOTIF.SYNCQUEUE.LENGTH
         */

        

        /*
         * Checkc agent missing libraries.
         * NOTIF.AGENT.LIBRARY
         */

        /*
         * Check MYSQL Support Version
         *
         */

        $this->checkMYSQLSettings();

        /*
         * Check total modules in system
         */

         $this->checkTotalModules();

        /*
         * Check total modules by agent
         */

         $this->checkTotalModulesByAgent();

    }


    /**
     * Check if performance variables are corrects
     *
     * @return void
     */
    public function checkPerformanceVariables()
    {
        global $config;

        $names = [
            'event_purge'                      => 'Max. days before events are deleted',
            'trap_purge'                       => 'Max. days before traps are deleted',
            'audit_purge'                      => 'Max. days before audited events are deleted',
            'string_purge'                     => 'Max. days before string data is deleted',
            'gis_purge'                        => 'Max. days before GIS data is deleted',
            'days_purge'                       => 'Max. days before purge',
            'days_compact'                     => 'Max. days before data is compacted',
            'days_delete_unknown'              => 'Max. days before unknown modules are deleted',
            'days_delete_not_initialized'      => 'Max. days before delete not initialized modules',
            'days_autodisable_deletion'        => 'Max. days before autodisabled agents are deleted',
            'report_limit'                     => 'Item limit for real-time reports',
            'event_view_hr'                    => 'Default hours for event view',
            'big_operation_step_datos_purge'   => 'Big Operation Step to purge old data',
            'small_operation_step_datos_purge' => 'Small Operation Step to purge old data',
            'row_limit_csv'                    => 'Row limit in csv log',
            'limit_parameters_massive'         => 'Limit for bulk operations',
            'block_size'                       => 'User block size for pagination',
            'global_block_size'                => 'Global block size for pagination',
            'short_module_graph_data'          => 'Data precision',
            'graph_precision'                  => 'Data precision in graphs',
        ];

        $variables = (array) json_decode(io_safe_output($config['performance_variables_control']));

        foreach ($variables as $variable => $values) {
            if (empty($config[$variable]) === true || $config[$variable] === '') {
                continue;
            }

            $message = '';
            $limit_value = '';
            $url = '';
            if ($config[$variable] > $values->max) {
                $message = 'Check the setting of %s, a value greater than %s is not recommended';

                if ($variable === 'block_size') {
                    $message .= '. (User: '.$config['id_user'].')';
                }

                $limit_value = $values->max;
            }

            if ($config[$variable] < $values->min) {
                $message = 'Check the setting of %s, a value less than %s is not recommended';
                $limit_value = $values->min;
            }

            if ($limit_value !== '' && $message !== '') {

                    $url = '__url__/index.php?sec=general&sec2=godmode/setup/setup';
                

                if ($variable === 'block_size') {

                        $url = '__url__/index.php?sec=gusuarios&sec2=godmode/users/configure_user&edit_user=1&pure=0&id_user='.$config['id_user'];
                    
                }

                if ($variable === 'global_block_size') {

                        $url = '__url__/index.php?sec=gsetup&sec2=godmode/setup/setup&section=vis';
                    
                }

                $this->notify(
                    [
                        'type'              => 'NOTIF.VARIABLES.PERFORMANCE.'.$variable,
                        'title'             => __('Incorrect config value'),
                        'message'           => __(
                            $message,
                            $names[$variable],
                            $limit_value
                        ),
                        'url'               => $url,
                        'icon_notification' => self::ICON_HEADSUP,
                    ]
                );
            }
        }

    }


    /**
     * Executes console maintenance operations. Executed ALWAYS through CRON.
     *
     * @return void
     */
    public function maintenanceOperations()
    {

    }


    /**
     * Check number of agents and disable agentaccess token if number
     * is equals and more than 200.
     *
     * @return void
     */
    public function checkAccessStatisticsPerformance()
    {
        global $config;

        $total_agents = db_get_value('count(*)', 'tagente');

        if ($total_agents >= 200) {
            if ((int) $config['agentaccess'] !== 0) {
                db_process_sql_update('tconfig', ['value' => 0], ['token' => 'agentaccess']);
                $this->notify(
                    [
                        'type'              => 'NOTIF.ACCESSSTASTICS.PERFORMANCE',
                        'title'             => __('Access statistics performance'),
                        'message'           => __(
                            'Usage of agent access statistics IS NOT RECOMMENDED on systems with more than 200 agents due performance penalty'
                        ),
                        'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                        'icon_notification' => self::ICON_HEADSUP,
                    ]
                );
            } else {
                $this->cleanNotifications('NOTIF.ACCESSSTASTICS.PERFORMANCE');
            }
        } else {
            $this->cleanNotifications('NOTIF.ACCESSSTASTICS.PERFORMANCE');
        }
    }


    /**
     * Update targets for given notification using object targets.
     *
     * @param array   $notification Current notification.
     * @param boolean $send_mails   Only update db targets, no email.
     *
     * @return void
     */
    public function updateTargets(
        array $notification,
        bool $send_mails=true
    ) {
        $notification_id = $notification['id_mensaje'];
        $blacklist = [];

        if (is_array($this->targetUsers) === true
            && count($this->targetUsers) > 0
        ) {
            // Process user targets.
            $insertion_string = '';
            $users_sql = 'INSERT IGNORE INTO tnotification_user(id_mensaje,id_user)';
            foreach ($this->targetUsers as $user) {
                $insertion_string .= sprintf(
                    '(%d,"%s")',
                    $notification_id,
                    $user['id_user']
                );
                $insertion_string .= ',';

                if ($send_mails === true) {
                    // Send mail.
                    if (isset($user['also_mail']) && $user['also_mail'] == 1) {
                        array_push($blacklist, $user['id_user']);
                    }
                }
            }

            $insertion_string = substr($insertion_string, 0, -1);
            db_process_sql($users_sql.' VALUES '.$insertion_string);
        }

        if (is_array($this->targetGroups) === true
            && count($this->targetGroups) > 0
        ) {
            // Process group targets.
            $insertion_string = '';
            $groups_sql = 'INSERT IGNORE INTO tnotification_group(id_mensaje,id_group)';
            foreach ($this->targetGroups as $group) {
                $insertion_string .= sprintf(
                    '(%d,"%s")',
                    $notification_id,
                    $group['id_group']
                );
                $insertion_string .= ',';
            }

            $insertion_string = substr($insertion_string, 0, -1);

            db_process_sql($groups_sql.' VALUES '.$insertion_string);
        }

    }


    /**
     * Generates notifications for target users and groups.
     *
     * @param array   $data      Message to be delivered:
     *                  - boolean status (false: notify, true: do not notify)
     *                  - string title
     *                  - string message
     *                  - string url.
     * @param integer $source_id Target source_id, by default $this->sourceId.
     * @param integer $max_age   Maximum age for generated notification.
     *
     * @return void
     */
    public function notify(
        array $data,
        int $source_id=0,
        int $max_age=SECONDS_1DAY
    ) {
        // Uses 'check failed' logic.
        if (is_array($data) === false) {
            // Skip.
            return;
        }

        if ($source_id === 0) {
            $source_id = $this->sourceId;
        }

        static $_cache_targets;
        $key = $source_id.'|'.$data['type'];

        if ($_cache_targets === null) {
            $_cache_targets = [];
        }

        if (isset($_cache_targets[$key]) === true
            && $_cache_targets[$key] !== null
        ) {
            $targets = $_cache_targets[$key];
        } else {
            $targets = get_notification_source_targets(
                $source_id,
                $data['type']
            );
            $this->targetGroups = ($targets['groups'] ?? null);
            $this->targetUsers = ($targets['users'] ?? null);

            $_cache_targets[$key] = $targets;
        }

        switch ($data['type']) {
            case 'NOTIF.LICENSE.LIMITED':
                $max_age = 0;
            break;

            case 'NOTIF.FILES.ATTACHMENT':
            case 'NOTIF.FILES.DATAIN':
            case 'NOTIF.FILES.DATAIN.BADXML':
            case 'NOTIF.PHP.SAFE_MODE':
            case 'NOTIF.PHP.INPUT_TIME':
            case 'NOTIF.PHP.EXECUTION_TIME':
            case 'NOTIF.PHP.UPLOAD_MAX_FILESIZE':
            case 'NOTIF.PHP.MEMORY_LIMIT':
            case 'NOTIF.PHP.DISABLE_FUNCTIONS':
            case 'NOTIF.PHP.VERSION':
            case 'NOTIF.PHP.VERSION.SUPPORT':
            case 'NOTIF.HISTORYDB':
            case 'NOTIF.PANDORADB':
            case 'NOTIF.PANDORADB.HISTORICAL':
            case 'NOTIF.EXT.ELASTICSEARCH':
            case 'NOTIF.DOWNTIME':
            case 'NOTIF.API.ACCESS':
            case 'NOTIF.MISC.EVENTSTORMPROTECTION':
            case 'NOTIF.MISC.DEVELOPBYPASS':
            case 'NOTIF.MISC.FONTPATH':
            case 'NOTIF.SECURITY.DEFAULT_PASSWORD':
            case 'NOTIF.CRON.CONFIGURED':
            case 'NOTIF.ALLOWOVERRIDE.MESSAGE':
            case 'NOTIF.HAMASTER.MESSAGE':
            case 'NOTIF.MYSQL.VERSION':

            default:
                // NOTIF.SERVER.STATUS.
                // NOTIF.SERVER.STATUS.ID_SERVER.
                // NOTIF.SERVER.QUEUE.ID_SERVER.
                // NOTIF.SERVER.MASTER.
                // NOTIF.SERVER.STATUS.ID_SERVER.
                if (preg_match('/^NOTIF.SERVER/', $data['type']) === true) {
                    // Send notification once a day.
                    $max_age = SECONDS_1DAY;
                }

                // Else ignored.
            break;
        }

        // Get previous notification.
        $prev = db_get_row(
            'tmensajes',
            'subtype',
            $data['type'],
            false,
            false
        );

        if ($data['type'] === 'NOTIF.LOG.ALERT' && $prev !== false) {
            return;
        } else if ($prev !== false
            && (time() - $prev['timestamp']) > $max_age
        ) {
            // Clean previous notification.
            $this->cleanNotifications($data['type']);
        } else if ($prev !== false) {
            // Avoid creation. Previous notification is still valid.
            // Update message with latest information.
            $r = db_process_sql_update(
                'tmensajes',
                [
                    'mensaje' => io_safe_input($data['message']),
                    'subject' => io_safe_input($data['title']),
                ],
                ['id_mensaje' => $prev['id_mensaje']]
            );
            $this->updateTargets($prev, false);
            return;
        }

        if (isset($data['type']) === false) {
            $data['type'] = '';
        }

        // Create notification.
        $notification = [];
        $notification['timestamp'] = time();
        $notification['id_source'] = $source_id;
        $notification['mensaje'] = io_safe_input($data['message']);
        $notification['subject'] = io_safe_input($data['title']);
        $notification['subtype'] = $data['type'];
        $notification['url'] = io_safe_input($data['url']);
        if (isset($data['icon_notification']) === true) {
            $notification['icon_notification'] = $data['icon_notification'];
        }

        $id = db_process_sql_insert('tmensajes', $notification);

        if ($id === false) {
            // Failed to generate notification.
            $this->warn('Failed to generate notification');
            return;
        }

        // Update reference to update targets.
        $notification['id_mensaje'] = $id;

        $this->updateTargets($notification);

    }


    /**
     * Deletes useless notifications.
     *
     * @param string $subtype Subtype to be deleted.
     *
     * @return mixed False in case of error or invalid values passed.
     *               Affected rows otherwise
     */
    public function cleanNotifications(string $subtype)
    {
        $not_count = db_get_value_sql(
            sprintf(
                'SELECT count(*) as n
                FROM tmensajes
                WHERE subtype like "%s"',
                $subtype
            )
        );

        if ($not_count > 0) {
            return db_process_sql_delete(
                'tmensajes',
                sprintf('subtype like "%s"', $subtype)
            );
        }

        return true;
    }


    /**
     * Count files in target path.
     *
     * @param string  $path      Path to be checked.
     * @param string  $regex     Regular expression to find files.
     * @param integer $max_files Maximum number of files to find.
     *
     * @return integer Number of files in target path.
     */
    public function countFiles(
        string $path='',
        string $regex='',
        int $max_files=500
    ) {
        if (empty($path) === true) {
            return -1;
        }

        $nitems = 0;

        // Count files up to max_files.
        $dir = opendir($path);

        if ($dir !== false) {
            // Used instead of glob to avoid check directories with
            // more than 1M files.
            while (false !== ($file = readdir($dir)) && $nitems <= $max_files) {
                if ($file != '.' && $file != '..') {
                    if (empty($regex) === false) {
                        if (preg_match($regex, $file) === 1) {
                            $nitems++;
                            continue;
                        }
                    } else {
                        $nitems++;
                    }
                }
            }

            closedir($dir);
        }

        return $nitems;
    }


    /**
     * Check excesive files in attachment directory.
     *
     * @return void
     */
    public function checkAttachment()
    {
        global $config;

        if (is_writable($config['attachment_store']) !== true) {
            $this->notify(
                [
                    'type'              => 'NOTIF.WRITABLE.ATTACHMENT',
                    'title'             => __('Attachment directory is not writable'),
                    'message'           => __(
                        'Directory %s is not writable. Please, configure corresponding permissions.',
                        $config['attachment_store']
                    ),
                    'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=general',
                    'icon_notification' => self::ICON_ERROR,
                ]
            );
            return;
        } else {
            $this->cleanNotifications('NOTIF.WRITABLE.ATTACHMENT');
        }

        $filecount = $this->countFiles(
            $config['attachment_store'],
            '',
            $config['num_files_attachment']
        );

        if ($filecount > $config['num_files_attachment']) {
            $this->notify(
                [
                    'type'    => 'NOTIF.FILES.ATTACHMENT',
                    'title'   => __('There are too many files in attachment directory'),
                    'message' => __(
                        'There are more than %d files in attachment, consider cleaning up attachment directory manually.',
                        $config['num_files_attachment']
                    ),
                    'url'     => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.FILES.ATTACHMENT');
        }

    }


    /**
     * Check excesive files in data_in directory.
     *
     * @return void
     */
    public function checkDataIn()
    {
        global $config;

        $remote_config_dir = (string) io_safe_output($config['remote_config']);

        $this->cleanNotifications('NOTIF.PERMISSIONS.REMOTE_CONF%');

        $MAX_FILES_DATA_IN = 1000;
        $MAX_BADXML_FILES_DATA_IN = 150;

        $filecount = 0;

        $agentId = db_get_value('id_agente', 'tagente', 'nombre', 'pandora.internals');
        if ($agentId !== false) {
            $agent = new Agent($agentId);

            $moduleId = $agent->searchModules(
                ['nombre' => 'Data_in_files'],
                1
            )->toArray()['id_agente_modulo'];

            if ($moduleId > 0) {
                $filecount = (int) modules_get_last_value($moduleId);
            }
        }

        // If cannot open directory, count is '-1', skip.
        if ($filecount > $MAX_FILES_DATA_IN) {
            $this->notify(
                [
                    'type'              => 'NOTIF.FILES.DATAIN',
                    'title'             => __('There are too many files in spool').'.',
                    'message'           => __(
                        'There are more than %d files in %s. Consider checking DataServer performance',
                        $MAX_FILES_DATA_IN,
                        $remote_config_dir
                    ),
                    'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.FILES.DATAIN');
        }

        $filecount = $this->countFiles(
            $remote_config_dir,
            '/^.*BADXML$/',
            $MAX_BADXML_FILES_DATA_IN
        );
        // If cannot open directory, count is '-1', skip.
        if ($filecount > $MAX_BADXML_FILES_DATA_IN) {
            $this->notify(
                [
                    'type'              => 'NOTIF.FILES.DATAIN.BADXML',
                    'title'             => __('There are too many BADXML files in spool'),
                    'message'           => __(
                        'There are more than %d files in %s. Consider checking software agents.',
                        $MAX_BADXML_FILES_DATA_IN,
                        $remote_config_dir
                    ),
                    'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.FILES.DATAIN.BADXML');
        }
    }


    /**
     * Check growing queues in servers.
     *
     * @return void
     */
    public function checkServers()
    {
        global $config;

        include_once $config['homedir'].'/include/functions_servers.php';

        $idx_file = $config['attachment_store'].'/.cron.supervisor.servers.idx';

        $MAX_QUEUE = 1500;
        $total_modules = servers_get_total_modules();

        $queue_state = [];
        $previous = [];
        $new = [];

        if (file_exists($idx_file) === true) {
            // Read previous values from file.
            $previous = json_decode(file_get_contents($idx_file), true);
        }

        // DataServer queue status.
        $queue_state = db_get_all_rows_sql(
            'SELECT id_server,name,server_type,queued_modules,status
            FROM tserver ORDER BY 1'
        );

        $time = time();
        if (is_array($queue_state) === true) {
            foreach ($queue_state as $queue) {
                $key = $queue['id_server'];
                $type = $queue['server_type'];
                $new_data[$key] = $queue['queued_modules'];
                $max_grown = 0;

                if (is_array($total_modules)
                    && isset($total_modules[$queue['server_type']])
                ) {
                    $max_grown = ($total_modules[$queue['server_type']] * 0.40);
                }

                if ($total_modules[$queue['server_type']] < self::MIN_PERFORMANCE_MODULES) {
                    $this->cleanNotifications('NOTIF.SERVER.QUEUE.'.$key);
                    // Skip.
                    continue;
                }

                // Compare queue increments in a not over 900 seconds.
                if (empty($previous[$key]['modules'])
                    || ($time - $previous[$key]['utime']) > 900
                ) {
                    $previous[$key]['modules'] = 0;
                }

                $modules_queued = ($queue['queued_modules'] - $previous[$key]['modules']);

                // 40% Modules queued since last check. If any.
                if ($max_grown > 0
                    && $modules_queued > $max_grown
                ) {
                    $msg = 'Queue has grown %d modules. Total %d';
                    if ($modules_queued <= 0) {
                        $msg = 'Queue is decreasing in %d modules. But there are %d queued.';
                        $modules_queued *= -1;
                    }

                    $this->notify(
                        [
                            'type'              => 'NOTIF.SERVER.QUEUE.'.$key,
                            'title'             => __(
                                '%s (%s) is lacking performance.',
                                servers_get_server_string_name($type),
                                $queue['name']
                            ),
                            'message'           => __(
                                $msg,
                                $modules_queued,
                                $queue['queued_modules']
                            ),
                            'url'               => '__url__/index.php?sec=gservers&sec2=godmode/servers/modificar_server&refr=60',
                            'icon_notification' => self::ICON_HEADSUP,
                        ]
                    );
                } else {
                    $this->cleanNotifications('NOTIF.SERVER.QUEUE.'.$key);
                }

                $new[$key]['modules'] = $queue['queued_modules'];
                $new[$key]['utime'] = $time;
            }

            // Update file content.
            file_put_contents($idx_file, json_encode($new));
        } else {
            // No queue data, ignore.
            unlink($idx_file);

            // Clean notifications.
            $this->cleanNotifications('NOTIF.SERVER.QUEUE.%');
        }
    }


    /**
     * Check Pandora component statuses.
     *
     * @return void
     */
    public function checkPandoraServers()
    {
        global $config;

        $types_sql = sprintf(
            ' AND (
                `server_type` != %d AND 
                `server_type` != %d
            )',
            SERVER_TYPE_AUTOPROVISION,
            SERVER_TYPE_MIGRATION
        );
        

        $servers = db_get_all_rows_sql(
            sprintf(
                'SELECT id_server,
                    `name`,
                    server_type,
                    server_keepalive,
                    `status`,
                    unix_timestamp() - unix_timestamp(keepalive) as downtime
                FROM tserver
                WHERE unix_timestamp() - unix_timestamp(keepalive) > server_keepalive
                    %s',
                $types_sql
            )
        );

        if ($servers === false) {
            $nservers = db_get_value_sql(
                'SELECT count(*) as nservers
                 FROM tserver'
            );
            if ($nservers == 0) {
                $url = 'https://pandoraopen.io/';
                if ($config['language'] == 'es') {
                    $url = 'https://pandoraopen.io/';
                }

                $this->notify(
                    [
                        'type'              => 'NOTIF.SERVER.STATUS',
                        'title'             => __('No servers available.'),
                        'message'           => __('There are no servers registered in this console. Please, check installation guide.'),
                        'url'               => $url,
                        'icon_notification' => self::ICON_ERROR,
                    ]
                );
            }

            // At this point there's no servers with issues.
            $this->cleanNotifications('NOTIF.SERVER.STATUS%');
            return;
        } else {
            // Clean notifications. Only show notif for down servers
            // ONLY FOR RECOVERED ONES.
            $servers_working = db_get_all_rows_sql(
                'SELECT
                    id_server,
                    name,
                    server_type,
                    server_keepalive,
                    status,
                    unix_timestamp() - unix_timestamp(keepalive) as downtime
                FROM tserver
                WHERE 
                    unix_timestamp() - unix_timestamp(keepalive) <= server_keepalive
                    AND status = 1'
            );
            if (is_array($servers_working) === true) {
                foreach ($servers_working as $server) {
                    $this->cleanNotifications(
                        'NOTIF.SERVER.STATUS.'.$server['id_server']
                    );
                }
            }
        }

        foreach ($servers as $server) {
            $icon_notification = self::ICON_QUESTION;
            if ($server['status'] == 1) {
                // Fatal error. Component has die.
                $msg = __(
                    '%s (%s) has crashed.',
                    servers_get_server_string_name($server['server_type']),
                    $server['name']
                );

                $description = __(
                    '%s (%s) has crashed, please check log files.',
                    servers_get_server_string_name($server['server_type']),
                    $server['name']
                );

                $icon_notification = self::ICON_ERROR;
            } else {
                // Non-fatal error. Controlated exit. Component is not running.
                $msg = __(
                    '%s (%s) is not running.',
                    servers_get_server_string_name($server['server_type']),
                    $server['name']
                );
                $description = __(
                    '%s (%s) is not running. Please, check configuration file or remove this server from server list.',
                    servers_get_server_string_name($server['server_type']),
                    $server['name']
                );
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.SERVER.STATUS.'.$server['id_server'],
                    'title'             => $msg,
                    'message'           => $description,
                    'url'               => '__url__/index.php?sec=gservers&sec2=godmode/servers/modificar_server&refr=60',
                    'icon_notification' => $icon_notification,
                ]
            );
        }
    }


    /**
     * Checks if there's at last one server running in master mode.
     *
     * @return void
     */
    public function checkPandoraServerMasterAvailable()
    {
        global $config;

        $n_masters = db_get_value_sql(
            'SELECT
                count(*) as n
            FROM tserver
            WHERE 
                unix_timestamp() - unix_timestamp(keepalive) <= server_keepalive
                AND master > 0
                AND status = 1'
        );

        if ($n_masters === false) {
            // Failed to retrieve server list.
            return;
        }

        if ($n_masters <= 0) {
            // No server running in master.
            $url = 'https://pandoraopen.io/';
            if ($config['language'] == 'es') {
                $url = 'https://pandoraopen.io/';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.SERVER.MASTER',
                    'title'             => __('No master servers found.'),
                    'message'           => __('At least one server must be defined to run as master. Please, check documentation.'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_INFORMATION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.SERVER.MASTER%');
        }

    }


    /**
     * Checks PHP settings to be correct. Generates system notifications if not.
     *
     * @return void
     */
    public function checkPHPSettings()
    {
        global $config;

        $PHPupload_max_filesize = config_return_in_bytes(
            ini_get('upload_max_filesize')
        );

        $PHPpost_max_size = config_return_in_bytes(
            ini_get('post_max_size')
        );

        // PHP configuration.
        $PHPmax_input_time = ini_get('max_input_time');
        $PHPmemory_limit = config_return_in_bytes(ini_get('memory_limit'));
        $PHPmax_execution_time = ini_get('max_execution_time');
        $PHPsafe_mode = ini_get('safe_mode');
        $PHPdisable_functions = ini_get('disable_functions');
        $PHPupload_max_filesize_min = config_return_in_bytes('800M');
        $PHPpost_max_size_min = config_return_in_bytes('800M');
        $PHPmemory_limit_min = config_return_in_bytes('800M');
        $PHPSerialize_precision = ini_get('serialize_precision');


        // PHP version checks.
        $php_version = phpversion();
        $php_version_array = explode('.', $php_version);

        if ($PHPsafe_mode === '1') {
            $url = 'http://php.net/manual/en/features.safe-mode.php';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/features.safe-mode.php';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.SAFE_MODE',
                    'title'             => __('PHP safe mode is enabled. Some features may not work properly'),
                    'message'           => __('To disable it, go to your PHP configuration file (php.ini) and put safe_mode = Off (Do not forget to restart apache process after changes)'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.SAFE_MODE');
        }

        if ($PHPmax_input_time !== '-1') {
            $url = 'http://php.net/manual/en/info.configuration.php#ini.max-input-time';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/info.configuration.php#ini.max-input-time';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.INPUT_TIME',
                    'title'             => sprintf(
                        __('%s value in PHP configuration is not recommended'),
                        'max_input_time'
                    ),
                    'message'           => sprintf(
                        __('Recommended value is %s'),
                        '-1 ('.__('Unlimited').')'
                    ).'<br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator (Do not forget to restart Apache process after)'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_INFORMATION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.INPUT_TIME');
        }

        if ((int) $PHPmax_execution_time !== 0) {
            $url = 'http://php.net/manual/en/info.configuration.php#ini.max-execution-time';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/info.configuration.php#ini.max-execution-time';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.EXECUTION_TIME',
                    'title'             => sprintf(
                        __("Not recommended '%s' value in PHP configuration"),
                        'max_execution_time'
                    ),
                    'message'           => sprintf(
                        __('Recommended value is: %s'),
                        '0 ('.__('Unlimited').')'
                    ).'<br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator (Dont forget restart apache process after changes)'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_INFORMATION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.EXECUTION_TIME');
        }

        if ($PHPupload_max_filesize < $PHPupload_max_filesize_min) {
            $url = 'http://php.net/manual/en/ini.core.php#ini.upload-max-filesize';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/ini.core.php#ini.upload-max-filesize';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.UPLOAD_MAX_FILESIZE',
                    'title'             => sprintf(
                        __("Not recommended '%s' value in PHP configuration"),
                        'upload_max_filesize'
                    ),
                    'message'           => sprintf(
                        __('Recommended value is: %s'),
                        sprintf(__('%s or greater'), '800M')
                    ).'<br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator (Dont forget restart apache process after changes)'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_INFORMATION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.UPLOAD_MAX_FILESIZE');
        }

        if ($PHPmemory_limit < $PHPmemory_limit_min && (int) $PHPmemory_limit !== -1) {
            $url = 'http://php.net/manual/en/ini.core.php#ini.memory-limit';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/ini.core.php#ini.memory-limit';
            }

            $recommended_memory = '800M';
            

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.MEMORY_LIMIT',
                    'title'             => sprintf(
                        __("Not recommended '%s' value in PHP configuration"),
                        'memory_limit'
                    ),
                    'message'           => sprintf(
                        __('Recommended value is: %s'),
                        sprintf(__('%s or greater'), $recommended_memory)
                    ).'<br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_INFORMATION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.MEMORY_LIMIT');
        }

        if (preg_match('/system/', $PHPdisable_functions) || preg_match('/exec/', $PHPdisable_functions)) {
            $url = 'http://php.net/manual/en/ini.core.php#ini.disable-functions';
            if ($config['language'] == 'es') {
                $url = 'http://php.net/manual/es/ini.core.php#ini.disable-functions';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.DISABLE_FUNCTIONS',
                    'title'             => __('Problems with disable_functions in php.ini'),
                    'message'           => __('The variable disable_functions contains functions system() or exec() in PHP configuration file (php.ini)').'<br /><br />'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator (Dont forget restart apache process after changes)'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.DISABLE_FUNCTIONS');
        }

        if ($php_version_array[0] < 8) {
            $url = 'https://pandoraopen.io/';
            if ($config['language'] == 'es') {
                $url = 'https://pandoraopen.io/';
            }

            if ($config['language'] == 'ja') {
                $url = 'https://pandoraopen.io/';
            }

            $this->notify(
                [
                    'type'    => 'NOTIF.PHP.VERSION',
                    'title'   => __('PHP UPDATE REQUIRED'),
                    'message' => __('For a correct operation of PandoraFMS, PHP must be updated to version 8.0 or higher.').'<br>'.__('Otherwise, functionalities will be lost.').'<br>'."<ol><li class='color_67'>".__('Report download in PDF format').'</li>'."<li class='color_67'>".__('Emails Sending').'</li><li class="color_67">...</li></ol>',
                    'url'     => $url,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.VERSION');
        }

        if ($PHPSerialize_precision != -1) {
            $url = 'https://www.php.net/manual/en/ini.core.php#ini.serialize-precision';
            if ($config['language'] == 'es') {
                $url = 'https://www.php.net/manual/es/ini.core.php#ini.serialize-precision';
            }

            $this->notify(
                [
                    'type'    => 'NOTIF.PHP.SERIALIZE_PRECISION',
                    'title'   => sprintf(
                        __("Not recommended '%s' value in PHP configuration"),
                        'serialize_precision'
                    ),
                    'message' => sprintf(
                        __('Recommended value is: %s'),
                        sprintf('-1')
                    ).'<br><br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator'),
                    'url'     => $url,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.SERIALIZE_PRECISION');
        }

        // If PHP_VERSION is lower than 8.0.27 version_compare() returns 1.
        if (version_compare('8.0.27', PHP_VERSION) === 1) {
            $url = 'https://www.php.net/supported-versions.php';
            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.VERSION.SUPPORT',
                    'title'             => __('PHP UPDATE REQUIRED'),
                    'message'           => __('You should update your PHP version because it will be out of official support').'<br>'.__('Current PHP version').': '.PHP_VERSION,
                    'url'               => $url,
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.VERSION.SUPPORT');
        }

        if ($PHPpost_max_size < $PHPpost_max_size_min && (int) $PHPpost_max_size !== -1) {
            $url = 'https://www.php.net/manual/en/ini.core.php#ini.post-max-size';
            $this->notify(
                [
                    'type'              => 'NOTIF.PHP.POST_MAX_SIZE',
                    'title'             => __('PHP POST MAX SIZE'),
                    'message'           => sprintf(
                        __('Recommended value is: %s'),
                        sprintf(__('%sM or greater'), ($PHPpost_max_size_min / 1024 / 1024))
                    ).'<br>'.__('Please, change it on your PHP configuration file (php.ini) or contact with administrator'),
                    'url'               => $url,
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PHP.POST_MAX_SIZE');
        }

    }


    /**
     * Checks if MYSQL version is supported.
     *
     * @return void
     */
    public function checkMYSQLSettings()
    {
        global $config;

        $mysql_version = $config['dbconnection']->server_info;
        if (version_compare('8.0', $mysql_version) >= 0) {
            $url = 'https://www.mysql.com/support/eol-notice.html';
            $this->notify(
                [
                    'type'              => 'NOTIF.MYSQL.VERSION',
                    'title'             => __('MYSQL UPDATE REQUIRED'),
                    'message'           => __('You should update your MYSQL version because it will be out of official support').'<br>'.__('Current MYSQL version: %s', $mysql_version),
                    'url'               => $url,
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.MYSQL.VERSION');
        }
    }


    /**
     * Check if pandora_db is running in all available DB instances.
     * Generating notifications.
     *
     * @return void
     */
    public function checkPandoraDBMaintenance()
    {
        global $config;

        // Main DB db_maintenance value.
        $db_maintance = db_get_value(
            'value',
            'tconfig',
            'token',
            'db_maintance'
        );

        // If never was executed, it means we are in the first Pandora FMS execution. Set current timestamp.
        if (empty($db_maintance)) {
            config_update_value('db_maintance', date('U'));
        }

        $last_maintance = (date('U') - $db_maintance);

        // Limit 48h.
        if ($last_maintance > 172800) {
            $this->notify(
                [
                    'type'              => 'NOTIF.PANDORADB',
                    'title'             => __('Database maintenance problem'),
                    'message'           => __(
                        'Your database hasn\'t been through maintenance for 48hrs. Please, check documentation on how to perform this maintenance process on %s and enable it as soon as possible.',
                        io_safe_output(get_product_name())
                    ),
                    'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PANDORADB');
        }

        if (isset($config['history_db_enabled'])
            && $config['history_db_enabled'] == 1
        ) {
            // History DB db_maintenance value.
            $db_maintenance = db_get_value(
                'value',
                'tconfig',
                'token',
                'db_maintenance',
                true
            );

            // History db connection is supossed to be enabled since we use
            // db_get_value, wich initializes target db connection.
            if (empty($db_maintance)) {
                $sql = sprintf(
                    'UPDATE tconfig SET `value`=%d WHERE `token`="%s"',
                    date('U'),
                    'db_maintenance'
                );
                $affected_rows = db_process_sql(
                    $sql,
                    $rettype = 'affected_rows',
                    $dbconnection = $config['history_db_connection']
                );

                if ($affected_rows == 0) {
                        // Failed to update. Maybe the row does not exist?
                        $sql = sprintf(
                            'INSERT INTO tconfig(`token`,`value`) VALUES("%s",%d)',
                            'db_maintenance',
                            date('U')
                        );

                        $affected_rows = db_process_sql(
                            $sql,
                            $rettype = 'affected_rows',
                            $dbconnection = $config['history_db_connection']
                        );
                }
            }

            $last_maintance = (date('U') - $db_maintance);

            // Limit 48h.
            if ($last_maintance > 172800) {
                $this->notify(
                    [
                        'type'              => 'NOTIF.PANDORADB.HISTORY',
                        'title'             => __(
                            'Historical database maintenance problem.'
                        ),
                        'message'           => __('Your historical database hasn\'t been through maintenance for 48hrs. Please, check documentation on how to perform this maintenance process on %s and enable it as soon as possible.', get_product_name()),
                        'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=perf',
                        'icon_notification' => self::ICON_ERROR,
                    ]
                );
            } else {
                // Historical db working fine.
                $this->cleanNotifications('NOTIF.PANDORADB.HISTORY');
            }
        } else {
            // Disabled historical db.
            $this->cleanNotifications('NOTIF.PANDORADB.HISTORY');
        }
    }


    /**
     * Check if elasticsearch is available.
     *
     * @return void
     */
    public function checkExternalComponents()
    {
        global $config;

        // Cannot check selenium, configuration is only available from server.
        if (isset($config['log_collector'])
            && $config['log_collector'] == 1
        ) {
            $elasticsearch = @fsockopen(
                $config['elasticsearch_ip'],
                $config['elasticsearch_port'],
                $errno,
                $errstr,
                5
            );

            if ($elasticsearch === false) {
                $this->notify(
                    [
                        'type'              => 'NOTIF.EXT.ELASTICSEARCH',
                        'title'             => __('Log collector cannot connect to OpenSearch'),
                        'message'           => __('OpenSearch is not available using current configuration.'),
                        'url'               => '__url__/index.php?sec=general&sec2=godmode/setup/setup&section=log',
                        'icon_notification' => self::ICON_ERROR,
                    ]
                );
            } else {
                fclose($elasticsearch);
                $this->cleanNotifications('NOTIF.EXT.ELASTICSEARCH');
            }
        } else {
            $this->cleanNotifications('NOTIF.EXT.ELASTICSEARCH');
        }

    }


    /**
     * Check if there are any incoming scheduled downtime in less than 15d.
     *
     * @return void
     */
    public function checkDowntimes()
    {
        // 15 Days.
        $THRESHOLD_SECONDS = (15 * 3600 * 24);

        // Check first if any planned runtime is running.
        $currently_running = (int) db_get_value_sql(
            'SELECT count(*) as "n" FROM tplanned_downtime
            WHERE executed = 1'
        );

        if ($currently_running > 0) {
            $this->notify(
                [
                    'type'    => 'NOTIF.DOWNTIME',
                    'title'   => __('Scheduled downtime running.'),
                    'message' => __('A scheduled downtime is running. Some monitoring data won\'t be available while downtime is taking place.'),
                    'url'     => '__url__/index.php?sec=gagente&sec2=godmode/agentes/planned_downtime.list',
                ]
            );
            return;
        } else {
            // Retrieve downtimes.
            $downtimes = db_get_all_rows_sql(
                'SELECT * FROM tplanned_downtime
                WHERE 
                (type_execution="once" AND date_from > now())
                OR type_execution!="once" ORDER BY `id` DESC'
            );

            // Initialize searchers.
            $next_downtime_begin = PHP_INT_MAX;
            $now = time();

            if ($downtimes === false) {
                $this->cleanNotifications('NOTIF.DOWNTIME');
                return;
            }

            $weekdays = [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ];

            foreach ($downtimes as $dt) {
                if ($dt['type_execution'] == 'once'
                    && ($dt['date_from'] - $now) < $THRESHOLD_SECONDS
                ) {
                    if ($next_downtime_begin > $dt['date_from']) {
                        // Store datetime for next downtime.
                        $next_downtime_begin = $dt['date_from'];
                        $next_downtime_end = $dt['date_to'];
                    }
                } else if ($dt['type_periodicity'] == 'monthly') {
                    $schd_time_begin = explode(
                        ':',
                        $dt['periodically_time_from']
                    );
                    $schd_time_end = explode(
                        ':',
                        $dt['periodically_time_to']
                    );

                    $begin = mktime(
                        // Hour.
                        $schd_time_begin[0],
                        // Minute.
                        $schd_time_begin[1],
                        // Second.
                        $schd_time_begin[2],
                        // Month.
                        date('n', $now),
                        // Day.
                        $dt['periodically_day_from'],
                        // Year.
                        date('Y', $now)
                    );

                    $end = mktime(
                        // Hour.
                        $schd_time_end[0],
                        // Minute.
                        $schd_time_end[1],
                        // Second.
                        $schd_time_end[2],
                        // Month.
                        date('n', $now),
                        // Day.
                        $dt['periodically_day_to'],
                        // Year.
                        date('Y', $now)
                    );

                    if ($next_downtime_begin > $begin) {
                        $next_downtime_begin = $begin;
                        $next_downtime_end = $end;
                    }
                } else if ($dt['type_periodicity'] == 'weekly') {
                    // Always applies.
                    $current_week_day = date('N', $now);

                    $schd_time_begin = explode(
                        ':',
                        $dt['periodically_time_from']
                    );
                    $schd_time_end = explode(
                        ':',
                        $dt['periodically_time_to']
                    );

                    $i = 0;
                    $max = 7;
                    while ($dt[$weekdays[(($current_week_day + $i) % 7)]] != 1
                    && $max-- >= 0
                    ) {
                        // Calculate day of the week matching downtime
                        // definition.
                        $i++;
                    }

                    if ($max < 0) {
                        // No days set.
                        continue;
                    }

                    // Calculate utimestamp.
                    $begin = mktime(
                        // Hour.
                        $schd_time_begin[0],
                        // Minute.
                        $schd_time_begin[1],
                        // Second.
                        $schd_time_begin[2],
                        // Month.
                        date('n', $now),
                        // Day.
                        (date('j', $now) + $i + 1),
                        // Year.
                        date('Y', $now)
                    );

                    $end = mktime(
                        // Hour.
                        $schd_time_end[0],
                        // Minute.
                        $schd_time_end[1],
                        // Second.
                        $schd_time_end[2],
                        // Month.
                        date('n', $now),
                        // Day.
                        (date('j', $now) + $i + 1),
                        // Year.
                        date('Y', $now)
                    );

                    if ($next_downtime_begin > $begin) {
                        $next_downtime_begin = $begin;
                        $next_downtime_end = $end;
                    }
                }
            }

            if ($next_downtime_begin != PHP_INT_MAX) {
                $this->notify(
                    [
                        'type'    => 'NOTIF.DOWNTIME',
                        'title'   => __('Downtime scheduled soon.'),
                        'message' => __(
                            'A scheduled downtime is going to be executed from %s to %s. Some monitoring data won\'t be available while downtime is taking place.',
                            date('M j, G:i:s ', $next_downtime_begin),
                            date('M j, G:i:s ', $next_downtime_end)
                        ),
                        'url'     => '__url__/index.php?sec=gagente&sec2=godmode/agentes/planned_downtime.list',
                    ]
                );
                return;
            } else {
                $this->cleanNotifications('NOTIF.DOWNTIME');
            }
        }
    }


    /**
     * Check if has access to the API
     *
     * @return void
     */
    public function checkApiAccess()
    {
        global $config;

        $server_name = db_get_value_filter(
            'name',
            'tserver',
            [ 'server_type' => '1' ]
        );
        if (verify_api() === false) {
            $this->notify(
                [
                    'type'              => 'NOTIF.API.ACCESS',
                    'title'             => __('Cannot access the Pandora FMS API'),
                    'message'           => __('Please check the configuration, some components may fail due to this misconfiguration in '.$server_name.' ('.$config['public_url'].')'),
                    'icon_notification' => self::ICON_ERROR,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.API.ACCESS');
        }
    }


    /**
     * Check if user 'admin' is enabled and using default password.
     *
     * @return void
     */
    public function checkDefaultPassword()
    {
        global $config;
        // Check default password for "admin".
        $admin_with_default_pass = db_get_value_sql(
            'SELECT count(*) FROM tusuario
            WHERE
                id_user="admin"
                AND (password="1da7ee7d45b96d0e1f45ee4ee23da560" OR
                     password="$2y$10$Wv/xoxjI2VAkthJhk/PzeeGIhBKYU/K.TMgUdmW7fEP2NQkdWlB9K")
                AND is_admin=1
                and disabled!=1'
        );

        if ($admin_with_default_pass > 0) {
            $this->notify(
                [
                    'type'              => 'NOTIF.SECURITY.DEFAULT_PASSWORD',
                    'title'             => __('Default password for "Admin" user has not been changed'),
                    'message'           => __('Please, change the default password since it is a commonly reported vulnerability.'),
                    'url'               => '__url__/index.php?sec=gusuarios&sec2=godmode/users/user_list',
                    'icon_notification' => self::ICON_HEADSUP,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.SECURITY.DEFAULT_PASSWORD');
        }
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function checkFont()
    {
        global $config;

        $fontpath = io_safe_output($config['fontpath']);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows.
            $fontpath = $config['homedir'].'\include\fonts\\'.$fontpath;
        } else {
            $home = str_replace('\\', '/', $config['homedir']);
            $fontpath = $home.'/include/fonts/'.$fontpath;
        }

        if (($fontpath == '')
            || (file_exists($fontpath) === false)
        ) {
            $this->notify(
                [
                    'type'    => 'NOTIF.MISC.FONTPATH',
                    'title'   => __('Default font doesn\'t exist'),
                    'message' => __('Your defined font doesn\'t exist or is not defined. Please, check font parameters in your config'),
                    'url'     => '__url__/index.php?sec=gsetup&sec2=godmode/setup/setup&section=vis',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.MISC.FONTPATH');
        }
    }


    /**
     * Checks if develop_bypass is enabbled.
     *
     * @return void
     */
    public function checkDevelopBypass()
    {
        global $develop_bypass;

        if ($develop_bypass == 1) {
            $this->notify(
                [
                    'type'    => 'NOTIF.MISC.DEVELOPBYPASS',
                    'title'   => __('Developer mode is enabled'),
                    'message' => __(
                        'Your %s has the "develop_bypass" mode enabled. This is a developer mode and should be disabled in a production environment. This value is located in the main index.php file',
                        get_product_name()
                    ),
                    'url'     => '__url__/index.php',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.MISC.DEVELOPBYPASS');
        }
    }


    /**
     * Check if event storm protection is enabled.
     *
     * @return void
     */
    public function checkEventStormProtection()
    {
        global $config;
        if ($config['event_storm_protection']) {
            $this->notify(
                [
                    'type'    => 'NOTIF.MISC.EVENTSTORMPROTECTION',
                    'title'   => __('Event storm protection is enabled.'),
                    'message' => __('Some events may get lost while this mode is enabled. The server must be restarted after altering this setting.'),
                    'url'     => '__url__/index.php?sec=gsetup&sec2=godmode/setup/setup&section=general',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.MISC.EVENTSTORMPROTECTION');
        }
    }


    /**
     * Check if CRON utility has been configured.
     *
     * @return void
     */
    public function checkCronRunning()
    {
        global $config;

        // Check if DiscoveryCronTasks is running. Warn user if not.
        if ($config['cron_last_run'] == 0
            || (get_system_time() - $config['cron_last_run']) > SECONDS_10MINUTES
        ) {
            $message_conf_cron = __('DiscoveryConsoleTasks is not running properly');
            if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
                $message_conf_cron .= __('Discovery relies on an appropriate cron setup.');
                $message_conf_cron .= '. '.__('Please, add the following line to your crontab file:');
                $message_conf_cron .= '<b><pre class=""ui-dialog>* * * * * &lt;user&gt; wget -q -O - --no-check-certificate --load-cookies /tmp/cron-session-cookies --save-cookies /tmp/cron-session-cookies --keep-session-cookies ';
                $message_conf_cron .= ui_get_full_url(false).'cron.php &gt;&gt; </pre>';

                $message_conf_cron .= $config['homedir'].'/log/cron.log</pre>';
            }

            if (isset($config['cron_last_run']) === true) {
                $message_conf_cron .= __('Last execution').': ';
                $message_conf_cron .= date('Y/m/d H:i:s', $config['cron_last_run']);
                $message_conf_cron .= __('Please, make sure process is not locked.');
            }

            $url = '__url__/index.php?sec=gservers&sec2=godmode/servers/discovery&wiz=tasklist';
            

            $this->notify(
                [
                    'type'              => 'NOTIF.CRON.CONFIGURED',
                    'title'             => __('DiscoveryConsoleTasks is not configured.'),
                    'message'           => __($message_conf_cron),
                    'url'               => $url,
                    'icon_notification' => self::ICON_QUESTION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.CRON.CONFIGURED');
        }

    }


    /**
     * Check if AllowOveride is None or All.
     *
     * @return void
     */
    public function checkAllowOverrideEnabled()
    {
        global $config;

        $message = 'If AllowOverride is disabled, .htaccess will not works.';
        if (PHP_OS == 'FreeBSD') {
            $message .= '<pre>Please check /usr/local/etc/apache24/httpd.conf to resolve this problem.';
        } else {
            $message .= '<pre>Please check /etc/httpd/conf/httpd.conf to resolve this problem.';
        }

	// Get content file.
	if (PHP_OS == 'FreeBSD') {
    		$path = '/usr/local/etc/apache24/httpd.conf';
	} else {
    		$path = '/etc/httpd/conf/httpd.conf';
	}

	if (is_readable($path)) {
    		$file = file_get_contents($path);
	} else {
    		$file = ""; 
	}
	
        $file_lines = preg_split("#\r?\n#", $file, -1, PREG_SPLIT_NO_EMPTY);
        $is_none = false;

        $i = 0;
        foreach ($file_lines as $line) {
            $i++;

            // Check Line and content.
            if (preg_match('/ AllowOverride/', $line) && $i === 311) {
                $result = explode(' ', $line);
                if ($result[5] == 'None') {
                    $is_none = true;
                    $this->notify(
                        [
                            'type'              => 'NOTIF.ALLOWOVERRIDE.MESSAGE',
                            'title'             => __('AllowOverride is disabled'),
                            'message'           => __($message),
                            'url'               => '__url__/index.php',
                            'icon_notification' => self::ICON_HEADSUP,
                        ]
                    );
                }
            }
        }

        // Cleanup notifications if AllowOverride is All.
        if (!$is_none) {
            $this->cleanNotifications('NOTIF.ALLOWOVERRIDE.MESSAGE');
        }

    }


    /**
     * Check if Pandora console log file remains in old location.
     *
     * @return void
     */
    public function checkPandoraConsoleLogOldLocation()
    {
        global $config;

        if (file_exists($config['homedir'].'/pandora_console.log')) {
            $title_pandoraconsole_old_log = __(
                'Pandora FMS console log file changed location',
                $config['homedir']
            );
            $message_pandoraconsole_old_log = __(
                'Pandora FMS console log file has been moved to new location %s/log. Currently you have an outdated and inoperative version of this file at %s. Please, consider deleting it.',
                $config['homedir'],
                $config['homedir']
            );

            $url = 'https://pandoraopen.io/';
            if ($config['language'] == 'es') {
                $url = 'https://pandoraopen.io/';
            }

            $this->notify(
                [
                    'type'              => 'NOTIF.PANDORACONSOLE.LOG.OLD',
                    'title'             => __($title_pandoraconsole_old_log),
                    'message'           => __($message_pandoraconsole_old_log),
                    'url'               => $url,
                    'icon_notification' => self::ICON_QUESTION,
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.PANDORACONSOLE.LOG.OLD');
        }
    }


    /**
     * Check if audit log file remains in old location.
     *
     * @return void
     */
    public function checkAuditLogOldLocation()
    {
        global $config;

        if (file_exists($config['homedir'].'/audit.log')) {
            $title_audit_old_log = __(
                'Pandora FMS audit log file changed location',
                $config['homedir']
            );
            $message_audit_old_log = __(
                'Pandora FMS audit log file has been moved to new location %s/log. Currently you have an outdated and inoperative version of this file at %s. Please, consider deleting it.',
                $config['homedir'],
                $config['homedir']
            );

            $this->notify(
                [
                    'type'    => 'NOTIF.AUDIT.LOG.OLD',
                    'title'   => __($title_audit_old_log),
                    'message' => __($message_audit_old_log),
                    'url'     => '#',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.AUDIT.LOG.OLD');
        }
    }


    /**
     * Check if the total number of modules in Pandora is greater than 80000.
     *
     * @return void
     */
    public function checkTotalModules()
    {
        $total_modules = db_get_num_rows('select * from tagente_modulo');
        if ($total_modules > 80000) {
            $this->notify(
                [
                    'type'              => 'NOTIF.MODULES.ALERT',
                    'title'             => __('Your system has a total of %s modules', $total_modules),
                    'message'           => __('This is higher than the recommended maximum 80,000 modules per node. This may result in poor performance of your system.'),
                    'icon_notification' => self::ICON_HEADSUP,
                    'url'               => '__url__index.php?sec=gagente&sec2=godmode/agentes/modificar_agente',
                ]
            );
        } else {
            $this->cleanNotifications('NOTIF.MODULES.ALERT');
        }
    }


    /**
     * Check if the total number of modules by agent is greater than 200
     *
     * @return void
     */
    public function checkTotalModulesByAgent()
    {
        $modules_by_agent = db_process_sql(
            'SELECT count(*) AS count
            FROM tagente a
            LEFT JOIN tagente_modulo m ON a.id_agente = m.id_agente
            WHERE m.disabled = 0
            GROUP BY m.id_agente'
        );

        $show_warning = false;

        if ($modules_by_agent !== false) {
            $agents = count($modules_by_agent);
            $modules = array_sum(array_column($modules_by_agent, 'count'));

            $ratio = ($modules / $agents);
            $ratio = round($ratio, 2);
        }

        if ($ratio > 200) {
            $this->notify(
                [
                    'type'              => 'NOTIF.MODULES_AGENT.ALERT',
                    'title'             => __('Your system has an average of %s modules per agent', $ratio),
                    'message'           => __('This is higher than the recommended maximum (200). This may result in poor performance of your system.'),
                    'icon_notification' => self::ICON_HEADSUP,
                    'url'               => '__url__index.php?sec=gagente&sec2=godmode/agentes/modificar_agente',
                ]
            );
            $show_warning = true;
        }

        if ($show_warning === false) {
            $this->cleanNotifications('NOTIF.MODULES_AGENT.ALERT');
        }
    }


}
