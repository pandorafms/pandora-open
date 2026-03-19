<?php
// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
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
// Begin.
global $config;

// Necessary classes for extends.
require_once $config['homedir'].'/include/class/HTML.class.php';
/**
 * Class EventSound
 */
class EventSound extends HTML
{

    /**
     * Allowed methods to be called using AJAX request.
     *
     * @var array
     */
    public $AJAXMethods = ['draw'];

    /**
     * Ajax page.
     *
     * @var string
     */
    private $ajaxController;

    /**
     * Table id.
     *
     * @var mixed
     */
    private $tableId;


    /**
     * Class constructor
     *
     * @param string $ajaxController Ajax controller.
     */
    public function __construct(string $ajaxController)
    {
        global $config;

        check_login();

        if (check_acl($config['id_user'], 0, 'PM') === false
            && is_user_admin($config['id_user']) === true
        ) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access Event Sound'
            );
            include 'general/noaccess.php';
            return;
        }

        // Set the ajax controller.
        $this->ajaxController = $ajaxController;
    }


    /**
     * Run view
     *
     * @return void
     */
    public function run()
    {
        global $config;
        $tab = get_parameter('tab', '');
        $action = get_parameter('action', '');
        $message_ok = 0;
        $error_msg = __('Name already exist');
        $ok_msg = __('Successfully created');

        if ($action == 'create') {
            $name = get_parameter('name', '');
            $sound = get_parameter('file', '');

            $exist = db_get_all_rows_sql(sprintf('SELECT * FROM tevent_sound WHERE name = "%s"', $name));

            if ($exist === false) {
                $uploadMaxFilesize = config_return_in_bytes(ini_get('upload_max_filesize'));

                $upload_status = get_file_upload_status('file');
                $upload_result = translate_file_upload_status($upload_status);
                if ($uploadMaxFilesize < $sound['size']) {
                    $error_msg = __('File is too large to upload. Check the configuration in php.ini.');
                } else {
                    $pathname = $config['homedir'].'/include/sounds/';
                    $nameSound = str_replace(' ', '_', $_FILES['file']['name']);
                    $target_file = $pathname.basename($nameSound);

                    if (file_exists($target_file)) {
                        $error_msg = __('Sound already are exists.');
                    } else {
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                            $insert = db_process_sql_insert(
                                'tevent_sound',
                                [
                                    'name'  => $name,
                                    'sound' => $nameSound,
                                ]
                            );
                            $ok_msg = __('Successfully created');
                        } else {
                            $error_msg = __('Fail uploading the sound');
                        }
                    }
                }

                if ($insert > 0) {
                    $tab = '';
                    $message_ok = 1;
                }
            } else {
                $error_msg = __('Sound already are exists');
            }
        } else if ($action == 'change_action') {
            $id = get_parameter('id', '');
            $new_action = (int) get_parameter('set_action', '1');

            $exist = db_get_all_rows_sql(sprintf('SELECT * FROM tevent_sound WHERE id = "%s"', $id));

            if ($exist !== false) {
                $result = db_process_sql_update(
                    'tevent_sound',
                    ['active' => $new_action],
                    ['id' => $id]
                );
                if (false === (bool) $result) {
                    $error_msg = __('Error on update status');
                } else {
                    $message_ok = 1;
                }
            } else {
                $error_msg = __('Sound not exist');
            }
        }

        if ($action) {
            ui_print_result_message(
                $message_ok,
                $ok_msg,
                $error_msg,
                '',
                false
            );
        }

        $base_url = 'index.php?sec=eventos&sec2=godmode/events/configuration_sounds';
        $setup_url = $base_url.'&tab=add';
        $tabs = [
            'list'    => [
                'text'   => '<a href="'.$base_url.'">'.html_print_image(
                    'images/see-details@svg.svg',
                    true,
                    [
                        'title' => __('Sounds'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>',
                'active' => (bool) ($tab != 'add'),
            ],
            'options' => [
                'text'   => '<a href="'.$setup_url.'">'.html_print_image(
                    'images/edit.svg',
                    true,
                    [
                        'title' => __('Create'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>',
                'active' => (bool) ($tab == 'add'),
            ],
        ];

        if ($tab === 'add') {
            $helpHeader  = '';
            $titleHeader = __('Add new sound');
        } else {
            $helpHeader  = 'servers_ha_clusters_tab';
            $titleHeader = __('Acoustic console sound list');
        }

        // Header.
        ui_print_standard_header(
            $titleHeader,
            'images/gm_servers.png',
            false,
            $helpHeader,
            false,
            $tabs,
            [
                [
                    'link'  => '',
                    'label' => __('Admin tools'),
                ],
                [
                    'link'  => '',
                    'label' => __('Acoustic console setup'),
                ],
            ]
        );

        // Javascript.
        ui_require_jquery_file('pandora');
        // CSS.
        ui_require_css_file('wizard');
        ui_require_css_file('discovery');

        if ($tab === 'add') {
            echo '<form method="post"  enctype="multipart/form-data" action="index.php?sec=eventos&sec2=godmode/events/configuration_sounds&tab=add&action=create"
            class="max_floating_element_size">';
            $table = new stdClass();
            $table->width = '100%';

            $table->class = 'databox filters filter-table-adv';
            $table->data = [];
            $table->size[0] = '50%';
            $table->size[1] = '50%';

            $table->data[0][0] = html_print_label_input_block(
                __('Name:'),
                html_print_input_text(
                    'name',
                    '',
                    '',
                    80,
                    100,
                    true,
                    false,
                    true
                )
            );

            $table->data[0][1] = html_print_label_input_block(
                __('WAV Sound'),
                html_print_input_file(
                    'file',
                    true,
                    [
                        'required' => true,
                        'accept'   => 'audio/*',
                    ]
                )
            );

            html_print_table($table);

            html_print_action_buttons(
                html_print_submit_button(
                    __('Create'),
                    'save_sound',
                    false,
                    ['icon' => 'wand'],
                    true
                )
            );
            echo '</form>';

            // Load own javascript file.
            echo $this->loadJS();
        } else {
            // Datatables list.
            try {
                $columns = [
                    'name',
                    'sound',
                    [
                        'text'  => 'options',
                        'class' => 'action_buttons mw120px',
                    ],
                ];

                $column_names = [
                    __('Name'),
                    __('Sound'),
                    __('Options'),
                ];

                $this->tableId = 'event_sounds';

                

                // Load datatables user interface.
                ui_print_datatable(
                    [
                        'id'                  => $this->tableId,
                        'class'               => 'info_table',
                        'style'               => 'width: 100%',
                        'columns'             => $columns,
                        'column_names'        => $column_names,
                        'ajax_url'            => $this->ajaxController,
                        'ajax_data'           => ['method' => 'draw'],
                        'no_sortable_columns' => [-1],
                        'order'               => [
                            'field'     => 'id',
                            'direction' => 'asc',
                        ],
                        'search_button_class' => 'sub filter',
                        'form'                => [
                            'inputs' => [
                                [
                                    'label' => __('Free search').ui_print_help_tip(__('Search filter by Name or Sound fields content'), true),
                                    'type'  => 'text',
                                    'class' => 'w70p',
                                    'id'    => 'filter_text',
                                    'name'  => 'filter_text',
                                ],
                                [
                                    'label'  => __('Active'),
                                    'type'   => 'select',
                                    'fields' => [
                                        ''  => __('All'),
                                        '0' => __('No'),
                                        '1' => __('Yes'),
                                    ],
                                    'class'  => 'w100p',
                                    'id'     => 'active',
                                    'name'   => 'active',
                                ],
                            ],
                        ],
                        'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar ',
                    ]
                );
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            

            html_print_action_buttons('');
            // Load own javascript file.
            echo $this->loadJS();
        }
    }


    /**
     * Get the data for draw the table.
     *
     * @return void.
     */
    public function draw()
    {
        global $config;
        // Initialice filter.
        $filter = '1=1';
        // Init data.
        $data = [];
        // Count of total records.
        $count = 0;
        // Catch post parameters.
        $start              = get_parameter('start', 0);
        $length             = get_parameter('length', $config['block_size']);
        // There is a limit of (2^32)^2 (18446744073709551615) rows in a MyISAM table, show for show all use max nrows.
        $length = ($length != '-1') ? $length : '18446744073709551615';
        $order              = get_datatable_order();
        $filters            = get_parameter('filter', []);
        $filterText   = $filters['filter_text'];
        $filterActive   = $filters['active'];

        if (empty($filterText) === false) {
            $filter .= sprintf(
                " AND (name LIKE '%%%s%%' OR sound LIKE '%%%s%%')",
                $filterText,
                $filterText
            );
        }

        if (in_array($filterActive, [0, 1])) {
            $filter .= sprintf(
                ' AND active = %s',
                $filterActive,
            );
        }

        $count = (int) db_get_value_sql(sprintf('SELECT COUNT(*) as "total" FROM tevent_sound WHERE %s', $filter));

        $sql = sprintf(
            'SELECT *
			FROM tevent_sound
			WHERE %s
			ORDER BY %s
            LIMIT %d, %d',
            $filter,
            $order,
            $start,
            $length
        );
        $data = db_get_all_rows_sql($sql);

        if ($data !== false) {
            foreach ($data as $key => $row) {
                if ($row['active'] === '1') {
                    $img = 'images/lightbulb.png';
                    $action = __('Disable sound');
                    $new_action = 0;
                } else {
                    $img = 'images/lightbulb_off.png';
                    $action = __('Enable sound');
                    $new_action = 1;
                }

                $options = '<a href="index.php?sec=eventos&sec2=godmode/events/configuration_sounds';
                $options .= '&action=change_action&id='.$row['id'].'&set_action='.$new_action.'">';
                $options .= html_print_image(
                    $img,
                    true,
                    [
                        'title' => $action,
                        'class' => 'main_menu_icon invert_filter',
                    ]
                );
                $options .= '</a>';

                $data[$key]['options'] = $options;
            }
        }

        echo json_encode(
            [
                'data'            => $data,
                'recordsTotal'    => $count,
                'recordsFiltered' => $count,
            ]
        );
    }


    /**
     * Checks if target method is available to be called using AJAX.
     *
     * @param string $method Target method.
     *
     * @return boolean True allowed, false not.
     */
    public function ajaxMethod(string $method)
    {
        return in_array($method, $this->AJAXMethods);
    }


    /**
     * Load Javascript code.
     *
     * @return string.
     */
    public function loadJS()
    {
        // Nothing for this moment.
        ob_start();

        // Javascript content.
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#file-sound').change(function(){
                    var ext = $('#file-sound').val().split('.').pop().toLowerCase();
                    if($.inArray(ext, ['wav']) == -1) {
                        alert('<?php __('Invalid extension'); ?>');
                        $('#file-sound').val('');
                    }
                });

                $('#submit-save_sound').click(function(){
                    console.log("a");
                });
            });
        </script>
        <?php
        // EOF Javascript content.
        return ob_get_clean();
    }


}