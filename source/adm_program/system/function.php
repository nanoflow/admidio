<?php
/******************************************************************************
 * Allgemeine Funktionen
 *
 * Copyright    : (c) 2004 - 2009 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

$arrMonth    = array('Monat', 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
$arrDay      = array('Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
$arrDayShort = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');

// Funktion prueft, ob ein User die uebergebene Rolle besitzt
// $role_name - Name der zu pruefenden Rolle
// $user_id   - Id des Users, fuer den die Mitgliedschaft geprueft werden soll

function hasRole($role_name, $user_id = 0)
{
    global $g_current_user, $g_current_organization, $g_db;

    if($user_id == 0)
    {
        $user_id = $g_current_user->getValue('usr_id');
    }
    elseif(is_numeric($user_id) == false)
    {
        return -1;
    }

    $sql    = 'SELECT *
                 FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                WHERE mem_usr_id = '.$user_id.'
                  AND mem_begin <= "'.DATE_NOW.'"
                  AND mem_end    > "'.DATE_NOW.'"
                  AND mem_rol_id = rol_id
                  AND rol_name   = "'.$role_name.'"
                  AND rol_valid  = 1 
                  AND rol_cat_id = cat_id
                  AND (  cat_org_id = '. $g_current_organization->getValue('org_id'). '
                      OR cat_org_id IS NULL ) ';
    $result = $g_db->query($sql);

    $user_found = $g_db->num_rows($result);

    if($user_found == 1)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

// Funktion prueft, ob der uebergebene User Mitglied in einer Rolle der Gruppierung ist

function isMember($user_id)
{
    global $g_current_organization, $g_db;
    
    if(is_numeric($user_id) && $user_id > 0)
    {
        $sql    = 'SELECT COUNT(*)
                     FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                    WHERE mem_usr_id = '.$user_id.'
                      AND mem_begin <= "'.DATE_NOW.'"
                      AND mem_end    > "'.DATE_NOW.'"
                      AND mem_rol_id = rol_id
                      AND rol_valid  = 1 
                      AND rol_cat_id = cat_id
                      AND (  cat_org_id = '. $g_current_organization->getValue('org_id'). '
                          OR cat_org_id IS NULL ) ';
        $result = $g_db->query($sql);

        $row = $g_db->fetch_array($result);
        $row_count = $row[0];

        if($row_count > 0)
        {
            return true;
        }
    }
    return false;
}

// Funktion prueft, ob der angemeldete User Leiter einer Gruppe /Kurs ist
// Optionaler Parameter role_id prueft ob der angemeldete User Leiter der uebergebenen Gruppe / Kurs ist

function isGroupLeader($user_id, $role_id = 0)
{
    global $g_current_organization, $g_db;

    if(is_numeric($user_id) && $user_id >  0
    && is_numeric($role_id))
    {
        $sql    = 'SELECT *
                     FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                    WHERE mem_usr_id = '.$user_id.'
                      AND mem_begin <= "'.DATE_NOW.'"
                      AND mem_end    > "'.DATE_NOW.'"
                      AND mem_leader = 1
                      AND mem_rol_id = rol_id
                      AND rol_valid  = 1 
                      AND rol_cat_id = cat_id
                      AND (  cat_org_id = '. $g_current_organization->getValue('org_id').'
                          OR cat_org_id IS NULL ) ';
        if ($role_id > 0)
        {
            $sql .= '  AND mem_rol_id = '.$role_id;
        }
        $result = $g_db->query($sql);

        $edit_user = $g_db->num_rows($result);

        if($edit_user > 0)
        {
            return true;
        }
    }
    return false;
}

// diese Funktion gibt eine Seitennavigation in Anhaengigkeit der Anzahl Seiten zurueck
// Teile dieser Funktion sind von generatePagination aus phpBB2
// Beispiel:
//              Seite: < Vorherige 1  2  3 ... 9  10  11 Naechste >
// Uebergaben:
// base_url   : Basislink zum Modul (auch schon mit notwendigen Uebergabevariablen)
// num_items  : Gesamtanzahl an Elementen
// per_page   : Anzahl Elemente pro Seite
// start_item : Mit dieser Elementnummer beginnt die aktuelle Seite
// add_prevnext_text : Links mit "Vorherige" "Naechste" anzeigen

function generatePagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true)
{
    global $g_root_path, $g_l10n;

    if ( $num_items == 0)
    {
    	return '';
    }
    
    $total_pages = ceil($num_items/$per_page);

    if ( $total_pages <= 1 )
    {
        return '';
    }

    $on_page = floor($start_item / $per_page) + 1;

    $page_string = '';
    if ( $total_pages > 7 )
    {
        $init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

        for($i = 1; $i < $init_page_max + 1; $i++)
        {
            $page_string .= ( $i == $on_page ) ? '<span class="selected">'. $i. '</span>' : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
            if ( $i <  $init_page_max )
            {
                $page_string .= "&nbsp;&nbsp;";
            }
        }

        if ( $total_pages > 3 )
        {
            if ( $on_page > 1  && $on_page < $total_pages )
            {
                $page_string .= ( $on_page > 5 ) ? ' ... ' : '&nbsp;&nbsp;';

                $init_page_min = ( $on_page > 4 ) ? $on_page : 5;
                $init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;

                for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
                {
                    $page_string .= ($i == $on_page) ? '<span class="selected">'. $i. '</span>' : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
                    if ( $i <  $init_page_max + 1 )
                    {
                        $page_string .= '&nbsp;&nbsp;';
                    }
                }

                $page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : '&nbsp;&nbsp;';
            }
            else
            {
                $page_string .= ' ... ';
            }

            for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
            {
                $page_string .= ( $i == $on_page ) ? '<span class="selected">'. $i. '</span>'  : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
                if( $i <  $total_pages )
                {
                    $page_string .= "&nbsp;&nbsp;";
                }
            }
        }
    }
    else
    {
        for($i = 1; $i < $total_pages + 1; $i++)
        {
            $page_string .= ( $i == $on_page ) ? '<span class="selected">'. $i. '</span>' : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
            if ( $i <  $total_pages )
            {
                $page_string .= '&nbsp;&nbsp;';
            }
        }
    }

    if ( $add_prevnext_text )
    {
        if ( $on_page > 1 )
        {
            $page_string = '<a href="' . $base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) . '"><img 
                                class="navigationArrow" src="'. THEME_PATH. '/icons/back.png" alt="'.$g_l10n->get('SYS_BACK').'" /></a>
                            <a href="' . $base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) . '">'.$g_l10n->get('SYS_BACK').'</a>&nbsp;&nbsp;' . $page_string;
        }

        if ( $on_page < $total_pages )
        {
            $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ( $on_page * $per_page ) . '">'.$g_l10n->get('SYS_NEXT').'</a>
                            <a class="navigationArrow" href="' . $base_url . "&amp;start=" . ( $on_page * $per_page ) . '"><img 
                                 src="'. THEME_PATH. '/icons/forward.png" alt="'.$g_l10n->get('SYS_NEXT').'" /></a>';
        }

    }

    $page_string = '<div class="pageNavigation">'.$g_l10n->get('SYS_PAGE').':&nbsp;&nbsp;' . $page_string. '</div>';

    return $page_string;
}

// Diese Funktion erzeugt eine Combobox mit allen Rollen, die der Benutzer sehen darf
// Die Rollen werden dabei nach Kategorie gruppiert
//
// Uebergaben:
// default_role : Id der Rolle die markiert wird
// field_id     : Id und Name der Select-Box
// show_mode    : Modus der bestimmt, welche Rollen angezeigt werden
//          = 0 : Alle Rollen, die der Benutzer sehen darf
//          = 1 : Alle sicheren Rollen, so dass der Benutzer sich kein "Rollenzuordnungsrecht" 
//                dazuholen kann, wenn er es nicht schon besitzt
//          = 2 : Alle nicht aktiven Rollen auflisten
// visitors = 1 : weiterer Eintrag um auch Besucher auswaehlen zu koennen

function generateRoleSelectBox($default_role = 0, $field_id = '', $show_mode = 0, $visitors = 0)
{
    global $g_current_user, $g_current_organization, $g_db, $g_l10n;
    
    if(strlen($field_id) == 0)
    {
        $field_id = 'rol_id';
    }

    // SQL-Statement entsprechend dem Modus zusammensetzen
    $condition = '';
    $active_roles = 1;
    if($show_mode == 1 && $g_current_user->assignRoles() == false)
    {
        // keine Rollen mit Rollenzuordnungsrecht anzeigen
        $condition .= ' AND rol_assign_roles = 0 ';
    }
    elseif($show_mode == 1 && $g_current_user->isWebmaster() == false)
    {
        // Webmasterrolle nicht anzeigen
        $condition .= ' AND rol_name <> "'.$g_l10n->get('SYS_WEBMASTER').'" ';
    }
    elseif($show_mode == 2)
    {
        $active_roles = 0;
    }
    
    $sql = 'SELECT * FROM '. TBL_ROLES. ', '. TBL_CATEGORIES. '
             WHERE rol_valid   = '.$active_roles.'
               AND rol_visible = 1
               AND rol_cat_id  = cat_id
               AND (  cat_org_id  = '. $g_current_organization->getValue('org_id'). '
                   OR cat_org_id IS NULL )
                   '.$condition.'
             ORDER BY cat_sequence, rol_name';
    $result_lst = $g_db->query($sql);

    // Selectbox mit allen selektierten Rollen zusammensetzen
    $act_category = '';
    $selectBoxHtml = '
    <select size="1" id="'.$field_id.'" name="'.$field_id.'">
        <option value="0" ';
        if($default_role == 0)
        {
            $selectBoxHtml .= ' selected="selected" ';
        }
        $selectBoxHtml .= '>- '.$g_l10n->get('SYS_PLEASE_CHOOSE').' -</option>';

        if($visitors == 1)
        {
            $selectBoxHtml .= '<option value="-1" ';
            if($default_role == -1)
            {
                $selectBoxHtml .= ' selected="selected" ';
            }
            $selectBoxHtml .= '>'.$g_l10n->get('SYS_ALL').' ('.$g_l10n->get('SYS_ALSO_VISITORS').')</option>';
        }

        while($row = $g_db->fetch_object($result_lst))
        {
            if($g_current_user->viewRole($row->rol_id))
            {
                if($act_category != $row->cat_name)
                {
                    if(strlen($act_category) > 0)
                    {
                        $selectBoxHtml .= '</optgroup>';
                    }
                    $selectBoxHtml .= '<optgroup label="'.$row->cat_name.'">';
                    $act_category = $row->cat_name;
                }
                // wurde eine Rollen-Id uebergeben, dann Combobox mit dieser vorbelegen
                $selected = "";
                if($row->rol_id == $default_role)
                {
                    $selected = ' selected="selected" ';
                }
                $selectBoxHtml .= '<option '.$selected.' value="'.$row->rol_id.'">'.$row->rol_name.'</option>';
            }
        }
        $selectBoxHtml .= '</optgroup>
    </select>';
    return $selectBoxHtml;
}

// Teile dieser Funktion sind von get_backtrace aus phpBB3
// Return a nicely formatted backtrace (parts from the php manual by diz at ysagoon dot com)

function getBacktrace()
{
    //global $phpbb_root_path;

    $output = '<div style="font-family: monospace;">';
    $backtrace = debug_backtrace();
    //$path = phpbb_realpath($phpbb_root_path);
    $path = SERVER_PATH;

    foreach ($backtrace as $number => $trace)
    {
        // We skip the first one, because it only shows this file/function
        if ($number == 0)
        {
            continue;
        }

        // Strip the current directory from path
        if (empty($trace['file']))
        {
            $trace['file'] = '';
        }
        else
        {
            $trace['file'] = str_replace(array($path, '\\'), array('', '/'), $trace['file']);
            $trace['file'] = substr($trace['file'], 1);
        }
        $args = array();

        // If include/require/include_once is not called, do not show arguments - they may contain sensible information
        if (!in_array($trace['function'], array('include', 'require', 'include_once')))
        {
            unset($trace['args']);
        }
        else
        {
            // Path...
            if (!empty($trace['args'][0]))
            {
                $argument = htmlentities($trace['args'][0]);
                $argument = str_replace(array($path, '\\'), array('', '/'), $argument);
                $argument = substr($argument, 1);
                $args[] = "'{$argument}'";
            }
        }

        $trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
        $trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

        $output .= '<br />';
        $output .= '<b>FILE:</b> ' . htmlentities($trace['file']) . '<br />';
        $output .= '<b>LINE:</b> ' . ((!empty($trace['line'])) ? $trace['line'] : '') . '<br />';

        $output .= '<b>CALL:</b> ' . htmlentities($trace['class'] . $trace['type'] . $trace['function']) . '(' . ((sizeof($args)) ? implode(', ', $args) : '') . ')<br />';
    }
    $output .= '</div>';
    return $output;
}

//Berechnung der Maximalerlaubten Dateiuploadgröße in Byte
function maxUploadSize()
{
    $post_max_size = trim(ini_get('post_max_size'));
    switch(admStrToLower(substr($post_max_size,strlen($post_max_size/1),1)))
    {
        case 'g':
            $post_max_size *= 1024;
        case 'm':
            $post_max_size *= 1024;
        case 'k':
            $post_max_size *= 1024;
    }
    $upload_max_filesize = trim(ini_get('upload_max_filesize'));
    switch(admStrToLower(substr($upload_max_filesize,strlen($upload_max_filesize/1),1)))
    {
        case 'g':
            $upload_max_filesize *= 1024;
        case 'm':
            $upload_max_filesize *= 1024;
        case 'k':
            $upload_max_filesize *= 1024;
    }
    if($upload_max_filesize < $post_max_size)
    {
        return $upload_max_filesize;    
    }
    else
    {
        return $post_max_size; 
    }
}

//Funktion gibt die maximale Pixelzahl zurück die der Speicher verarbeiten kann
function processableImageSize()
{
    $memory_limit = trim(ini_get('memory_limit'));
    //falls in php.ini nicht gesetzt
    if($memory_limit=="")
    {
       $memory_limit=="8M";
    }
    //falls in php.ini abgeschaltet
    if($memory_limit==-1)
    {
       $memory_limit=="128M";
    }
    switch(admStrToLower(substr($memory_limit,strlen($memory_limit/1),1)))
    {
     case 'g':
         $memory_limit *= 1024;
     case 'm':
         $memory_limit *= 1024;
     case 'k':
         $memory_limit *= 1024;
    }
    //Für jeden Pixel werden 3Byte benötigt (RGB)
    //der Speicher muss doppelt zur Verfügung stehen
    //nach ein paar tests hat sich 2,5Fach als sichrer herausgestellt
    return $memory_limit/(3*2.5); 
}

// Funktion zur Versendung von Benachrichtigungs-Emails (bei neuen Einträgen)
function EmailNotification($receiptian, $reference, $message, $sender_name, $sender_mail)
{
//Konfiguration Mail
$empfaenger = $receiptian;
$betreff = utf8_decode($reference);
$nachricht = utf8_decode($message);
$absender = utf8_decode($sender_name);
$absendermail = $sender_mail;

mail($empfaenger, $betreff, $nachricht, "From: $absender <$absendermail>");
//echo "Empfänger: $empfaenger<br>Betreff: $betreff<br>Nachricht: $nachricht<br>Absender Name: $absender<br>Absender Mail: $absendermail";
}
?>