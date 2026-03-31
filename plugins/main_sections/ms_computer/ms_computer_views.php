<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

function show_computer_menu($computer_id) {
    global $protectedGet;
    $menu_serializer = new XMLMenuSerializer();
    $menu = $menu_serializer->unserialize(file_get_contents(CD_CONFIG_DIR . 'menu.xml'));

    $menu_renderer = new ComputerMenuRenderer($computer_id, $_SESSION['OCS']['url_service']);


    echo "<div class='left-menu col col-md-2'>";
    echo "<ul class='nav nav-pills nav-stacked navbar-left' data-spy='affix'>";


    foreach ($menu->getChildren() as $menu_elem) {

        $url = $menu_elem->getUrl();
        $label = $menu_renderer->getLabel($menu_elem);
        echo "<li ";
        if (isset($protectedGet['cat']) && $protectedGet['cat'] == explode('=',$url)[1]) {
            echo "class='active'";
        }
        echo " ><a href=' ".$menu_renderer->getUrl($menu_elem) ."'>" . $label . "</a></li>";
    }

	echo '</ul>';
	echo '</div>';
}

function show_computer_title($computer) {
    global $l;

    $name = preg_replace("/[^A-Za-z0-9-_\.]/", "", $computer->NAME);
    $ip = isset($computer->IPADDR) ? $computer->IPADDR : 'N/A';
    $os = isset($computer->OSNAME) ? $computer->OSNAME : 'N/A';
    $last = isset($computer->LASTCOME) ? $computer->LASTCOME : 'N/A';

    echo '<div class="panel" style="background: linear-gradient(to right, var(--ocs-secondary), #35384e); color: white; border: none !important;">';
    echo '<div class="panel-body" style="display:flex; align-items:center;">';
    echo '<i class="fa fa-desktop" style="font-size: 40px; color: var(--ocs-primary); margin-right: 25px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px;"></i>';
    echo '<div style="flex-grow: 1;">';
    echo '<h2 style="margin: 0 0 5px 0; font-weight: 700; color:white;">' . $name . '</h2>';
    echo '<div style="font-size: 14px; opacity: 0.8;">';
    if ($ip != 'N/A') echo '<span style="margin-right:15px;"><i class="fa fa-globe"></i> ' . $ip . '</span>';
    if ($os != 'N/A') echo '<span style="margin-right:15px;"><i class="fa fa-windows"></i> ' . $os . '</span>';
    if ($last != 'N/A') echo '<span><i class="fa fa-clock-o"></i> ' . $last . '</span>';
    echo '</div>';
    echo '</div>';
    echo '<div style="text-align: right;">';
}

function show_computer_actions($computer){
    global $protectedGet;
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
        echo '<button class="btn btn-default" style="background: rgba(255,255,255,0.1); color:white; border:none; margin-bottom:5px; margin-right:5px;" onclick=\'location.href="index.php?' . PAG_INDEX . '=' . $urls->getUrl('ms_export_ocs') . '&no_header=1&systemid=' . $computer->ID . '";\' target="_blank"><i class="fa fa-download"></i> ' . $l->g(1304) . '</button>';
    }

    if ($_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO" && isset($protectedGet['cat']) && $protectedGet['cat'] == 'admin') {
        echo "<button class='btn btn-default' style='background: rgba(255,255,255,0.1); color:white; border:none; margin-bottom:5px; margin-right:5px;' OnClick='confirme(\"\",\"WOL\",\"bandeau\",\"WOL\",\"" . $l->g(1283) . "\");'><i class='fa fa-bolt'></i> WOL</button>";
    }

    if ($_SESSION['OCS']['profile']->getConfigValue('ARCHIVE_COMPUTERS') == "YES" && isset($protectedGet['cat']) && $protectedGet['cat'] == 'admin') {
        $archive = new ArchiveComputer();
        if (mysqli_num_rows($archive->isArchived($computer->ID)) != 0) {
            $archive_action = $l->g(1552);
            $color = '#4CAF50';
            $icon = 'fa-undo';
        } else {
            $archive_action = $l->g(1551);
            $color = '#e53935';
            $icon = 'fa-archive';
        }
        echo "<button class='btn btn-danger' style='background: ".$color."; border:none; margin-bottom:5px;' OnClick='confirme(\"\",\"". $archive_action ."\",\"bandeau\",\"ARCHIVE\",\"Do you want to ". strtolower($archive_action) ." this computer ?\");'><i class='fa ".$icon."'></i> ". strtoupper($archive_action)."</button>";
    }
    
    echo '</div>'; // ferme la div des boutons
    echo '</div></div>'; // ferme panel-body et panel
}

function show_computer_summary($computer) {
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    $labels = array(
        'SYSTEM' => array(
            'USERID' => $l->g(24),
            'OSNAME' => $l->g(274),
            'OSVERSION' => $l->g(275),
            'ARCH' => $l->g(1247),
            'OSCOMMENTS' => $l->g(286),
            'DESCRIPTION' => $l->g(53),
            'WINCOMPANY' => $l->g(51),
            'WINOWNER' => $l->g(348),
            'WINPRODID' => $l->g(111),
            'WINPRODKEY' => $l->g(553),
            'VMTYPE' => $l->g(1267),
            'ASSET' => $l->g(2132),
        ),
        'NETWORK' => array(
            'WORKGROUP' => $l->g(33),
            'USERDOMAIN' => $l->g(557),
            'IPADDR' => $l->g(34),
            'NAME_RZ' => $l->g(304),
        ),
        'HARDWARE' => array(
            'SWAP' => $l->g(50),
            'MEMORY' => $l->g(26),
            'UUID' => $l->g(1268),

        ),
        'AGENT' => array(
            'USERAGENT' => $l->g(357),
            'LASTDATE' => $l->g(46),
            'LASTCOME' => $l->g(820),
        ),
    );

    $cat_labels = array(
        'SYSTEM' => $l->g(1387),
        'NETWORK' => $l->g(1388),
        'HARDWARE' => $l->g(1389),
        'AGENT' => $l->g(1390),
    );

    $link = array();

    foreach ($labels as $cat) {
        foreach ($cat as $key => $lbl) {
            $computer_info = isset($computer->$key) ? addslashes($computer->$key) : '';
            if ($key == "MEMORY") {
                $sqlMem = "SELECT SUM(capacity) AS 'capa' FROM memories WHERE hardware_id=%s";
                $argMem = $computer->ID;
                $resMem = mysql2_query_secure($sqlMem, $_SESSION['OCS']["readServer"], $argMem);
                $valMem = mysqli_fetch_array($resMem);

                if ($valMem["capa"] > 0) {
                    $memory = $valMem["capa"];
                } else {
                    $memory = $computer_info;
                }
                $data[$key] = $memory;
            } elseif ($key == "LASTDATE" || $key == "LASTCOME") {
                $data[$key] = dateTimeFromMysql($computer_info);
            } elseif ($key == "NAME_RZ") {
                $data[$key] = "";
                $data_RZ = subnet_name($computer->ID);

                if($data_RZ != null) {
                    $nb_val = count($data_RZ);
                }

                if (isset($nb_val) && $nb_val == 1) {
                    $data[$key] = $data_RZ[0];
                } elseif (isset($data_RZ) && is_array($data_RZ)) {
                    foreach ($data_RZ as $index => $value) {
                        $data[$key] .= $index . " => " . $value . "<br>";
                    }
                }
            } elseif ($key == "VMTYPE" && $computer->UUID != '') {
                $sqlVM = "select vm.hardware_id,vm.vmtype, h.name from virtualmachines vm left join hardware h on vm.hardware_id=h.id where vm.uuid='%s' order by h.name DESC";
                $argVM = $computer->UUID;
                $resVM = mysql2_query_secure($sqlVM, $_SESSION['OCS']["readServer"], $argVM);
                $valVM = mysqli_fetch_array($resVM);
                $data[$key] = $valVM['vmtype'] ?? '';
                $link_vm = "<a href='index.php?" . PAG_INDEX . "=" . $urls->getUrl('ms_computer') . "&head=1&systemid=" . ($valVM['hardware_id'] ?? '') . "'  target='_blank'><font color=red>" . ($valVM['name'] ?? '') . "</font></a>";
                $link[$key] = true;

                if ($data[$key] != '') {
                    msg_info($l->g(1266) . "<br>" . $l->g(1269) . ': ' . $link_vm);
                }
            } elseif ($key == "IPADDR" && $_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO") {
                $data[$key] = $computer->$key;
                $link[$key] = true;
            } elseif ($computer_info != '') {
                $data[$key] = $computer_info;
            } elseif ($key == "ASSET") {
                $sqlAsset = "SELECT CATEGORY_NAME FROM assets_categories LEFT JOIN hardware AS h ON h.CATEGORY_ID = assets_categories.ID WHERE h.ID = %s";
                $argAsset = array($computer->ID);
                $resAsset = mysql2_query_secure($sqlAsset, $_SESSION['OCS']["readServer"], $argAsset);
                $asset = mysqli_fetch_array($resAsset);
                $data[$key] = $asset['CATEGORY_NAME'] ?? "";
            }
        }
    }

    echo open_form("bandeau", '', '', 'form-horizonal');

    show_summary($data, $labels, $cat_labels, $link);
    echo "<input type='hidden' id='WOL' name='WOL' value=''>";
    echo "<input type='hidden' id='ARCHIVE' name='ARCHIVE' value=''>";

    echo close_form();
}

function show_summary($data, $labels, $cat_labels, $links = array()) {

    $nb_col = 2;
    $i = 0;

    foreach ($labels as $cat_key => $cat) {
        if ($i % $nb_col == 0) {
            echo '<div class="row">';
        }

        echo '<div class="col col-md-6">';
        echo '<div class="plugin-frame">';
        echo '<h4 style="margin-top:0; margin-bottom:15px; font-weight:600; color:var(--ocs-text-main);"><i class="fa fa-info-circle text-muted"></i> ' . mb_strtoupper($cat_labels[$cat_key]) . '</h4>';
        echo '<table class="summary">';
        
        $col_index = 0;
        foreach ($cat as $name => $label) {
            $value = isset($data[$name])? $data[$name] : '';

            if (trim($value) != '') {
                if (!array_key_exists($name, $links)) {
                    $value = strip_tags_array($value);
                }
                if ($name == "IPADDR") {
                    $value = preg_replace('/([x0-9])\//', '$1 / ', $value);
                }

                if ($col_index % 2 == 0) echo '<tr class="summary-row">';
                
                echo '<td class="summary-cell">';
                echo '<span class="summary-header text-left">' . $label . ' :</span>';
                echo '<span class="summary-value text-left">' . $value . '</span>';
                echo '</td>';
                
                if ($col_index % 2 == 1) echo '</tr>';
                $col_index++;
            }
        }
        if ($col_index > 0 && $col_index % 2 != 0) echo '<td class="summary-cell"></td></tr>'; // ferme la ligne impaire
        
        echo '</table>';
        echo '</div>'; // ferme plugin-frame
        echo '</div>'; // ferme col-md-6

        $i++;
        if ($i % $nb_col == 0) {
            echo '</div>';
        }
    }
}

?>
