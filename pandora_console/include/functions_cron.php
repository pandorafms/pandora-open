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

// Begin.
global $config;

require_once $config['homedir'].'/include/functions_db.php';


/**
 * Update the execution interval of the given module
 *
 * @param integer $module_id Id of module to update.
 * @param string  $cron      String with the Linux cron configuration.
 *
 * @return boolean Return number of rows affected.
 */
function cron_update_module_interval($module_id, $cron)
{
    // Check for a valid cron.
    if (!cron_check_syntax($cron)) {
        return false;
    }

    $module_interval = db_get_value(
        'module_interval',
        'tagente_modulo',
        'id_agente_modulo',
        $module_id
    );

    if ($cron === '* * * * *') {
        return db_process_sql(
            'UPDATE tagente_estado SET current_interval = '.$module_interval.' WHERE id_agente_modulo = '.(int) $module_id
        );
    } else {
        return db_process_sql(
            'UPDATE tagente_estado SET current_interval = '.cron_next_execution($cron, $module_interval, $module_id).' WHERE id_agente_modulo = '.(int) $module_id
        );
    }

}


/**
 * Get the number of seconds left to the next execution of the given cron entry.
 *
 * @param string  $cron            String with the Linux cron configuration.
 * @param integer $module_interval Module interval. Minimum increased time.
 * @param integer $module_id       Module id.
 *
 * @return integer Time to next execution time.
 */
function cron_next_execution($cron, $module_interval, $module_id)
{
    // Get day of the week and month from cron config.
    $cron_array = explode(' ', $cron);
    $wday = $cron_array[4];

    // Get last execution time.
    $last_execution = db_get_value(
        'utimestamp',
        'tagente_estado',
        'id_agente_modulo',
        $module_id
    );

    $cron_elems = explode(' ', $cron);

    if (isset($cron_elems[4]) === true) {
        $cron_elems[4] = '*';
    }

    $cron = implode(' ', $cron_elems);

    $cur_time = ($last_execution !== false) ? $last_execution : time();
    $nex_time = cron_next_execution_date($cron, $cur_time, $module_interval);
    $nex_wday = (int) date('w', $nex_time);

    // Check the wday values to avoid infinite loop.
    $wday_int = cron_get_interval($wday);
    if ($wday_int['down'] !== '*' && ($wday_int['down'] > 6 || ($wday_int['up'] !== false && $wday_int['up'] > 6))) {
        $wday = '*';
    }

    // Check week day.
    while (!cron_check_interval($nex_wday, $wday)) {
        // If it does not acomplish the day of the week, go to the next day.
        $nex_time += SECONDS_1DAY;
        $nex_wday = (int) date('w', $nex_time);
    }

    $nex_time = cron_next_execution_date($cron, $nex_time, 0);

    return ($nex_time - $cur_time);
}


/**
 * Get the next execution date for the given cron entry in seconds since epoch.
 *
 * @param string  $cron            String with the Linux cron configuration.
 * @param integer $cur_time        Current time in utimestamp.
 * @param integer $module_interval Module interval. Minimum increased time.
 *
 * @return integer Next execution timestamp seing the cron configuration.
 */
function cron_next_execution_date($cron, $cur_time=false, $module_interval=300)
{
    // Get cron configuration.
    $cron_array = explode(' ', $cron);

    // REMARKS: Months start from 1 in php (different to server)
    // Get current time.
    if ($cur_time === false) {
        $cur_time = time();
    }

    $nex_time = ($cur_time + $module_interval);
    $nex_time_array = explode(' ', date('i H d m Y', $nex_time));
    if (cron_is_in_cron($cron_array, $nex_time_array)) {
        return $nex_time;
    }

    // Update minutes.
    $nex_time_array[0] = cron_get_next_time_element($cron_array[0]);

    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time >= $cur_time) {
        if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
            return $nex_time;
        }
    }

    // Check if next hour is in cron.
    $nex_time_array[1]++;
    $nex_time = cron_valid_date($nex_time_array);

    if ($nex_time === false) {
        // Update the month day if overflow.
        $nex_time_array[1] = 0;
        $nex_time_array[2]++;
        $nex_time = cron_valid_date($nex_time_array);
        if ($nex_time === false) {
            // Update the month if overflow.
            $nex_time_array[2] = 1;
            $nex_time_array[3]++;
            $nex_time = cron_valid_date($nex_time_array);
            if ($nex_time === false) {
                // Update the year if overflow.
                $nex_time_array[3] = 1;
                $nex_time_array[4]++;
                $nex_time = cron_valid_date($nex_time_array);
            }
        }
    }

    // Check the hour.
    if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
        return $nex_time;
    }

    // Update the hour if fails.
    $nex_time_array[1] = cron_get_next_time_element($cron_array[1]);

    // When an overflow is passed check the hour update again.
    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time >= $cur_time) {
        if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
            return $nex_time;
        }
    }

    // Check if next day is in cron.
    $nex_time_array[2]++;
    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time === false) {
        // Update the month if overflow.
        $nex_time_array[2] = 1;
        $nex_time_array[3]++;
        $nex_time = cron_valid_date($nex_time_array);
        if ($nex_time === false) {
            // Update the year if overflow.
            $nex_time_array[3] = 1;
            $nex_time_array[4]++;
            $nex_time = cron_valid_date($nex_time_array);
        }
    }

    // Check the day.
    if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
        return $nex_time;
    }

    // Update the day if fails.
    $nex_time_array[2] = cron_get_next_time_element($cron_array[2]);

    // When an overflow is passed check the hour update in the next execution.
    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time >= $cur_time) {
        if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
            return $nex_time;
        }
    }

    // Check if next month is in cron.
    $nex_time_array[3]++;
    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time === false) {
        // Update the year if overflow.
        $nex_time_array[3] = 1;
        $nex_time_array[4]++;
        $nex_time = cron_valid_date($nex_time_array);
    }

    // Check the month.
    if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
        return $nex_time;
    }

    // Update the month if fails.
    $nex_time_array[3] = cron_get_next_time_element($cron_array[3]);

    // When an overflow is passed check the hour update in the next execution.
    $nex_time = cron_valid_date($nex_time_array);
    if ($nex_time >= $cur_time) {
        if (cron_is_in_cron($cron_array, $nex_time_array) && $nex_time) {
            return $nex_time;
        }
    }

    // Update the year.
    $nex_time_array[4]++;
    $nex_time = cron_valid_date($nex_time_array);

    return ($nex_time !== false) ? $nex_time : $module_interval;
}


/**
 * Get the next tentative time for a cron value or interval in case of overflow.
 *
 * @param string $cron_array_elem Cron element.
 *
 * @return integer The tentative time. Ex:
 *      * shold returns 0.
 *      5 should returns 5.
 *      10-55 should returns 10.
 *      55-10 should retunrs 0.
 */
function cron_get_next_time_element($cron_array_elem)
{
    $interval = cron_get_interval($cron_array_elem);
    $value = ($interval['down'] == '*' || ($interval['up'] !== false && $interval['down'] > $interval['up'] )) ? 0 : $interval['down'];
    return $value;
}


/**
 * Get an array with the cron interval.
 *
 * @param string $element String with the elemen cron configuration.
 *
 * @return array With up and down elements.
 *      If there is not an interval, up element will be false.
 */
function cron_get_interval($element)
{
    // Not a range.
    if (!preg_match('/(\d+)\-(\d+)/', $element, $capture)) {
        return [
            'down' => $element,
            'up'   => false,
        ];
    }

    return [
        'down' => $capture[1],
        'up'   => $capture[2],
    ];
}


/**
 * Returns if a date is in a cron. Recursive.
 *
 * @param array   $elems_cron      Cron configuration in array format.
 * @param integer $elems_curr_time Time to check if is in cron.
 *
 * @return boolean Returns true if is in cron. False if it is outside.
 */
function cron_is_in_cron($elems_cron, $elems_curr_time)
{
    $elem_cron = array_shift($elems_cron);
    $elem_curr_time = array_shift($elems_curr_time);

    // If there is no elements means that is in cron.
    if ($elem_cron === null || $elem_curr_time === null) {
        return true;
    }

    // Go to last element if current is a wild card.
    if (cron_check_interval($elem_curr_time, $elem_cron) === false) {
        return false;
    }

    return cron_is_in_cron($elems_cron, $elems_curr_time);
}


/**
 * Check if an element is inside the cron interval or not.
 *
 * @param integer $elem_curr_time Integer that represents the time to check.
 * @param string  $elem_cron      Cron interval (splitted by hypen)
 *            or cron single value (a number).
 *
 * @return boolean True if is in interval.
 */
function cron_check_interval($elem_curr_time, $elem_cron)
{
    // Go to last element if current is a wild card.
    if ($elem_cron === '*') {
        return true;
    }

    $elem_s = cron_get_interval($elem_cron);
    // Check if there is no a range.
    if (($elem_s['up'] === false) && ($elem_s['down'] != $elem_curr_time)) {
        return false;
    }

    // Check if there is on the range.
    if ($elem_s['up'] !== false && (int) $elem_s['up'] === (int) $elem_curr_time) {
        return true;
    }

    if ($elem_s['down'] < $elem_s['up']) {
        if ($elem_curr_time < $elem_s['down'] || $elem_curr_time > $elem_s['up']) {
            return false;
        }
    } else {
        if ($elem_curr_time > $elem_s['down'] || $elem_curr_time < $elem_s['up']) {
            return false;
        }
    }

    return true;
}


/**
 * Check if a date is correct or not.
 *
 * @param array $da Date in array format [year, month, day, hour, minutes].
 *
 * @return integer Utimestamp. False if date is incorrect.
 */
function cron_valid_date($da)
{
    $st = sprintf(
        '%04d:%02d:%02d %02d:%02d:00',
        $da[4],
        $da[3],
        $da[2],
        $da[1],
        $da[0]
    );
    $time = strtotime($st);
    return $time;
}


/**
 * Check if cron is properly constructed.
 *
 * @param string $cron String with the Linux cron configuration.
 *
 * @return boolean True if is well formed. False otherwise.
 */
function cron_check_syntax($cron)
{
    return preg_match(
        '/^[\d|\*].* .*[\d|\*].* .*[\d|\*].* .*[\d|\*].* .*[\d|\*]$/',
        $cron
    );
}


/**
 * GetNextExecutionCron give string and return datetime with the date of the next execution
 *
 * @param string $cron String with cron.
 *
 * @return DateTime Datetime with the next execution.
 */
function GetNextExecutionCron($cron)
{
    // Split cron.
    $cronsplit = preg_split('/\s+/', $cron);
    // Set dates to use.
    $current_day = new DateTime();
    $next_execution = new DateTime();

    // Monthly schedule.
    if ($cronsplit[2] !== '*') {
        $next_execution->setDate($current_day->format('Y'), $current_day->format('m'), $cronsplit[2]);
        $next_execution->setTime($cronsplit[1], $cronsplit[0]);
        if ($next_execution->format('Y-m-d H:i') <= $current_day->format('Y-m-d H:i')) {
            $next_execution->setDate($current_day->format('Y'), ($current_day->format('m') + 1), $cronsplit[2]);
        }

        return $next_execution;
    }

    // Weekly schedule.
    if ($cronsplit[4] !== '*') {
        $next_execution->setISODate($current_day->format('Y'), $current_day->format('W'), $cronsplit[4]);
        $next_execution->setTime($cronsplit[1], $cronsplit[0]);
        if ($next_execution->format('Y-m-d H:i') <= $current_day->format('Y-m-d H:i')) {
            $next_execution->setISODate($current_day->format('Y'), ($current_day->format('W') + 1), $cronsplit[4]);
        }

        return $next_execution;
    }

    // Daily schedule.
    if ($cronsplit[2] === '*' && $cronsplit[3] === '*' && $cronsplit[4] === '*') {
        $next_execution->setTime($cronsplit[1], $cronsplit[0]);
        if ($next_execution->format('Y-m-d H:i') <= $current_day->format('Y-m-d H:i')) {
            $next_execution->setDate($current_day->format('Y'), $current_day->format('m'), ($current_day->format('d') + 1));
        }

        return $next_execution;
    }

    return $next_execution;
}
