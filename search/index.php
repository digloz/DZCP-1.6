<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 */

## OUTPUT BUFFER START ##
include("../inc/buffer.php");

## INCLUDES ##
include(basePath."/inc/debugger.php");
include(basePath."/inc/config.php");
include(basePath."/inc/bbcode.php");

## SETTINGS ##
$dir = "search";
$where = /*_search_head*/_forum_search_head;
$title = $pagetitle." - ".$where."";

## SECTIONS ##
switch ($action):
    default:
        $search_forum = true;

        //check $_GET var
        if($_GET['area'] == 'topic')
            $acheck2 = 'checked="checked"';
        else
            $acheck1 = 'checked="checked"';

        if($_GET['type'] == 'autor')
            $tcheck2 = 'checked="checked"';
        else
            $tcheck1 = 'checked="checked"';

        $i=0; $strkat = ''; $getstr = '';
        for(reset($_GET); list($key,$value)=each($_GET);$i++) {
            $key = trim($key);
            if($i == 0)
                $sep = '?';
            else
                $sep = '&';

            $getstr .= $sep.$key.'='.$value;
            if(preg_match("#k_#",$key))
                $strkat .= $key.'|';
        }

        if(permission("intforum")) {
            $qry = db("SELECT `id`,`name`,`intern` FROM `".$db['f_kats']."` ORDER BY `kid`;");
        } else {
            $qry = db("SELECT `id`,`name`,`intern` FROM `".$db['f_kats']."` WHERE `intern` = 0 ORDER BY `kid`;");
        }

		$fkats = '';
        while($get = _fetch($qry)) {
            $fkats .= '<li><label class="searchKat" style="text-align:center">'.re($get['name']).'</label></li>';
            $showt = "";
            $qrys = db("SELECT `id`,`kattopic` FROM `".$db['f_skats']."` WHERE `sid` = ".$get['id']." ORDER BY `kattopic`;");
            while($gets = _fetch($qrys)) {
                $intF = db("SELECT `id` FROM `".$db['f_access']."` WHERE `user` = ".$userid." AND `forum` = ".$gets['id'].";",true);
                if(!$get['intern'] || (($get['intern'] && $intF) || $chkMe == 4)) {
                    if(preg_match("#k_".$gets['id']."\|#",$strkat))
                        $kcheck = 'checked="checked"';
                    else
                        $kcheck = '';

                    $fkats .= '<li><label class="search" for="k_'.$gets['id'].'"><input type="checkbox" class="chksearch" name="k_'.$gets['id'].'" id="k_'.$gets['id'].'" '.$kcheck.' onclick="DZCP.hideForumFirst()" value="true" />&nbsp;&nbsp;'.re($gets['kattopic']).'</label></li>';
                }
            }
        } unset($get,$gets,$qry,$qrys,$intF,$kcheck);

        //Auswertung
        if($do == 'search' && !empty($_GET['search']) && $_GET['search'] != _search_word) {
            $maxfsearch = 20;
            $_SESSION['search_con'] = $_GET['con']; //see function hl()
            $dosearch = '';
            if($_GET['type'] == 'autor') { //AUTOR
                $_SESSION['search_type'] = 'autor';
                if($_SESSION['search_con'] == 'or') {
                    $suche = explode(" ",$_GET['search']); $z=0;
                    for($x=0;$x<count($suche);$x++) {
                        $qryu = db("SELECT `id` FROM `".$db['users']."` WHERE `nick` LIKE '%".up(trim($suche[$x]))."%'");
                        if(_rows($qryu)) {
                            while($getu = _fetch($qryu)) {
                                $c = (!$z ? 'WHERE (' : 'OR ');
                                $dosearch .= $c."s1.`t_reg` = ".intval($getu['id'])." OR s2.`reg` = ".intval($getu['id'])." ";
                            } //while
                            $z++;
                        }
                    } //for

                    $suche = explode(" ",$_GET['search']);
                    for($x=0;$x<count($suche);$x++) {
                        $b = (!$z ? 'WHERE (' : 'OR ');
                        $dosearch .= $b."s1.`t_nick` LIKE '%".up(trim($suche[$x]))."%' OR s2.`nick` LIKE '%".up(trim($suche[$x]))."%'";
                    } //for
                } else { //search_con=AND
                    $qryu = db("SELECT `id` FROM `".$db['users']."` WHERE `nick` LIKE '%".up(trim($_GET['search']))."%';"); $x=0;
                    if(_rows($qryu)) {
                        while($getu = _fetch($qryu)) {
                            $c = (!$x ? 'WHERE (' : 'OR ');
                            $dosearch .= $c."s1.`t_reg` = ".intval($getu['id'])." OR s2.`reg` = ".intval($getu['id'])." ";
                            $x++;
                        } //while
                    }

                    $c = (!$x ? 'WHERE (' : 'OR ');
                    $dosearch .= $c."s1.`t_nick` LIKE '%".up(trim($_GET['search']))."%' OR s2.`nick` LIKE '%".up(trim($_GET['search']))."%'";
                }

                $dosearch .= ')';
            } else { //search_type = text
                $_SESSION['search_type'] = 'text';
                if($_SESSION['search_con'] == 'or') {
                    $suche = explode(" ",$_GET['search']);
                    for($x=0;$x<count($suche);$x++) {
                        if($x == 0)
                            $c = 'WHERE (';
                        else
                            $c = 'OR ';

                        if($_GET['area'] != 'topic')
                            $dosearch .= $c." s1.`t_text` LIKE '%".up(trim($suche[$x]))."%' OR s2.`text` LIKE '%".
                                up(trim($suche[$x]))."%' OR s1.`topic` LIKE '%".up(trim($suche[$x]))."%' ";
                        else
                            $dosearch .= $c." s1.`topic` LIKE '%".up(trim($suche[$x]))."%' ";
                    }
                } else { //AND
                    if($_GET['area'] != 'topic')
                        $dosearch .= "WHERE (s1.`t_text` LIKE '%".up(trim($_GET['search']))."%' OR s2.`text` LIKE '%".
                            up(trim($_GET['search']))."%' OR s1.`topic` LIKE '%".up(trim($_GET['search']))."%'";
                    else
                        $dosearch .= "WHERE (s1.`topic` LIKE '%".up(trim($_GET['search']))."%'";
                }

                $dosearch .= ')';
            } unset($c,$x,$suche,$z,$qryu,$getu);

            if(!empty($strkat)) {
                $dosearch .= ' AND (';
                $kat = explode("|",$strkat);
                for($y=0;$y<count($kat)-1;$y++) {
                    $d = (!$y ? '' : 'OR ');
                    $k = $kat[$y];
                    $k = str_replace("k_","",$k);
                    $dosearch .= $d."s3.`id` = ".intval($k)." ";
                }
                $dosearch .= ')';
            } unset($strkat,$k,$y,$kat);

            //Intern
            $dosearch .= (!permission("intforum")) ? ' AND s4.`intern` = 0' : '';

            //SQL
            $qry = db("SELECT s1.`id`,s1.`topic`,s1.`kid`,s1.`t_reg`,s1.`t_email`,s1.`t_nick`,s1.`hits`,s4.`intern`,s3.`id` "
                 ."FROM `".$db['f_threads']."` AS s1 "
                 ."LEFT JOIN `".$db['f_posts']."` AS s2 "
                 ."ON s1.`id` = s2.`sid` "
                 ."LEFT JOIN `".$db['f_skats']."` AS s3 "
                 ."ON s1.`kid` = s3.`id` "
                 ."LEFT JOIN `".$db['f_kats']."` AS s4 "
                 ."ON s3.`sid` = s4.`id` "
                 .$dosearch." "
                 ."GROUP by s1.`id` "
                 ."ORDER BY s1.`lp` DESC "
                 ."LIMIT ".($page - 1)*$maxfsearch.",".$maxfsearch.";");

            $qrye = db("SELECT s1.`id` "
                ."FROM `".$db['f_threads']."` AS s1 "
                ."LEFT JOIN `".$db['f_posts']."` AS s2 "
                ."ON s1.`id` = s2.`sid` "
                ."LEFT JOIN `".$db['f_skats']."` AS s3 "
                ."ON s2.`kid` = s3.`id` "
                ."AND s1.`kid` = s3.`id` "
                ."LEFT JOIN `".$db['f_kats']."` AS s4 "
                ."ON s3.`sid` = s4.`id` "
                 .$dosearch." "
                 ."GROUP by s1.`id`;");

            $entrys = _rows($qrye); $results = '';
            while($get = _fetch($qry)) {
                $intF = db("SELECT `id` FROM `".$db['f_access']."` WHERE `user` = ".$userid." AND `forum` = ".$get['id'].";",true);
                if(($get['intern'] == 1 && !$intF && $chkMe != 4)) $entrys--;
                if(!$get['intern'] || (($get['intern'] == 1 && $intF) || $chkMe == 4)) {
                    $sticky = $get['sticky'] ? _forum_sticky : '';
                    $closed = $get['closed'] ? _closedicon : '';
                    $cntpage = cnt($db['f_posts'], " WHERE `sid` = ".$get['id']);
                    $pagenr = $cntpage >= 1 ? ceil($cntpage/config('m_ftopics')) : 1;
                    $qrylp = db("SELECT `date`,`nick`,`reg`,`email` FROM `".$db['f_posts']."` WHERE `sid` = ".$get['id']." ORDER BY date DESC;");
                    $lpost = "-"; $lpdate = "";
                    if(_rows($qrylp)) {
                        $getlp = _fetch($qrylp);
                        $lpost = show(_forum_thread_lpost, array("nick" => autor($getlp['reg'], '', $getlp['nick'], re($getlp['email'])),
                                "date" => date("d.m.y H:i", $getlp['date'])._uhr));
                        $lpdate = $getlp['date'];
                    } unset($getlp,$qrylp);

                    $threadlink = show(_forum_thread_search_link, array("topic" => cut(re($get['topic']),config('l_forumtopic'),true,false),
                                                                            "id" => $get['id'],
                                                                            "sticky" => $sticky,
                                                                            "hl" => $_GET['search'],
                                                                            "closed" => $closed,
                                                                            "lpid" => $cntpage+1,
                                                                            "page" => $pagenr));

                    $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst"; $color++;
                    $results .= show($dir."/forum_search_results", array("new" => check_new($get['lp']),
                                                                             "topic" => $threadlink,
                                                                             "subtopic" => cut(re($get['subtopic']),config('l_forumsubtopic'),true,false),
                                                                             "hits" => $get['hits'],
                                                                             "replys" => cnt($db['f_posts'], " WHERE sid = '".$get['id']."'"),
                                                                             "class" => $class,
                                                                             "lpost" => $lpost,
                                                                             "autor" => autor($get['t_reg'], '', re($get['t_nick']), re($get['t_email']))));
                }
            }

            if(empty($results)) {
                $results = show(_search_no_entrys_yet, array("colspan" => "5"));
            }

            $nav = nav($entrys,$maxfsearch,$getstr);
            $show = show($dir."/forum_search_show", array("head" => _forum_search_results,
                                                              "autor" => _autor,
                                                              "thread" => _forum_thread,
                                                              "lpost" => _forum_lpost,
                                                              "nav" => $nav,
                                                              "results" => $results,
                                                              "replys" => _forum_replys,
                                                              "hits" => _hits));
        }

        //Diverse Abfragen
		$chk_con = ''; $all_board = '';
        if(isset($_GET['searchplugin'])) {
            $onclick = 'onclick="more(1)" style="cursor:pointer"';
            $img = '<img id="img1" src="../inc/images/expand.gif" alt="" />';
            $style = 'style="display:none"';

            if(empty($strkat)) $all_board = 'checked="checked"';
            if($_GET['con'] == 'or') $chk_con = 'selected="selected"';
        } else {
            $all_board = 'checked="checked"';
            $style = '';
            $onclick = '';
            $img = '';
        }

        $index = show($dir."/search", array("head" => _forum_search_head,
                                                "searchwords" => _search_word,
                                                "board" => _forum,
                                                "fkats" => $fkats,
                                                "show" => $show,
                                                "search" => ($_GET['search'] != _search_word ? $_GET['search'] : ''),
                                                "searchin" => _search_in,
                                                "onclick" => $onclick,
                                                "img" => $img,
                                                "con_and" => _search_con_and,
                                                "con_or" => _search_con_or,
                                                "chkcon" => $chk_con,
                                                "style" => $style,
                                                "all_board" => $all_board,
                                                "acheck1" => $acheck1,
                                                "acheck2" => $acheck2,
                                                "tcheck1" => $tcheck1,
                                                "tcheck2" => $tcheck2,
                                                "value" => _button_value_search1,
                                                "autor" => _search_type_autor,
                                                "searcharea" => _search_for_area,
                                                "text" => _search_type_text,
                                                "type" => _search_type,
                                                "hint" => _search_forum_hint,
                                                "all" => _search_forum_all,
                                                "full" => _search_type_full,
                                                "intitle" => _search_type_title,
        ));
        break;
    case 'site';
    if(!empty($_GET['searchword']) && $_GET['searchword'] != _search_word) {
        //Suche in News
        $qry = db("SELECT `id`,`titel` FROM `" . $db['news'] . "` WHERE (`titel` LIKE '%" . up($_GET['searchword']) . "%' AND `titel` != '')"
            . " OR (`text` LIKE '%" . up($_GET['searchword']) . "%' AND `text` != '') ORDER BY `titel` ASC;");
        $color = 0; $shownews = '';
        while ($get = _fetch($qry)) {
            $class = ($color % 2) ? "contentMainFirst" : "contentMainSecond";
            $color++;
            $shownews .= show($dir . "/search_show", array("class" => $class,
                                                               "type" => 'news',
                                                               "href" => '../news/index.php?action=show&amp;id=' . $get['id'],
                                                               "titel" => re($get['titel'])));
        } unset($get, $qry);

        //Suche in Artikel
        $qry = db("SELECT `id`,`titel` FROM `" . $db['artikel'] . "` WHERE (`titel` LIKE '%" . up($_GET['searchword']) . "%' AND `titel` != '')"
            . " OR (`text` LIKE '%" . up($_GET['searchword']) . "%' AND `text` != '') ORDER BY `titel` ASC;");
        $color = 0; $showartikel = '';
        while ($get = _fetch($qry)) {
            $class = ($color % 2) ? "contentMainFirst" : "contentMainSecond";
            $color++;
            $showartikel .= show($dir . "/search_show", array("href" => '../artikel/index.php?action=show&amp;id=' . $get['id'],
                                                                  "class" => $class,
                                                                  "type" => 'artikel',
                                                                  "titel" => re($get['titel'])));
        } unset($get, $qry);

        //Suche in Seiten
        $qry = db("SELECT `id`,`titel` FROM `" . $db['sites'] . "` WHERE (`titel` LIKE '%" . up($_GET['searchword']) . "%' AND `titel` != '')"
            . " OR (`text` LIKE '%" . up($_GET['searchword']) . "%' AND `text` != '') ORDER BY `titel` ASC;");
        $color = 0; $showsites = '';
        while ($get = _fetch($qry)) {
            $class = ($color % 2) ? "contentMainFirst" : "contentMainSecond";
            $color++;
            $showsites .= show($dir . "/search_show", array("href" => '../sites/?show=' . $get['id'],
                                                                 "class" => $class,
                                                                 "type" => 'site',
                                                                 "titel" => re($get['titel'])));
        } unset($get, $qry, $color, $class);

        if (!empty($shownews))
            $shownews = '<tr><td class="contentMainTop"><b>' . _news . '</b></td></tr>' . $shownews;

        if (!empty($showartikel))
            $showartikel = '<tr><td class="contentMainTop"><b>' . _artikel . '</b></td></tr>' . $showartikel;

        if (!empty($showsites))
            $showsites = '<tr><td class="contentMainTop"><b>' . _search_sites . '</b></td></tr>' . $showsites;
    }

    if(empty($shownews) && empty($showartikel) && empty($showsites)) {
        $shownews = show(_search_no_entrys_yet, array("colspan" => "1"));
    }

    $index = show($dir."/search_global", array("shownews" => $shownews,
                                                    "showartikel" => $showartikel,
                                                    "showsites" => $showsites,
                                                    "results" => _search_results));
    break;
endswitch;

## INDEX OUTPUT ##
page($index, $title, $where);