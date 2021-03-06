<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 * Menu: Teamspeak
 */
function teamspeak($js = 0) {
    global $db, $language, $cache;

    header('Content-Type: text/html; charset=utf-8');
    if(!fsockopen_support()) return _fopen;

    if(empty($js)) {
        $teamspeak = '
          <div id="navTeamspeakServer">
            <div style="width:100%;padding:10px 0;text-align:center"><img src="../inc/images/ajax_loading.gif" alt="" /></div>
            <script language="javascript" type="text/javascript">
              <!--
                DZCP.initTeamspeakServer();
              //-->
            </script>
          </div>';

    } else {
        $ts_ip = settings('ts_ip');
        $ts_sport = settings('ts_sport');
        $ts_port = settings('ts_port');
        if(!empty($ts_ip) && !empty($ts_sport) && !empty($ts_port)) {
            $teamspeak = $cache->get('nav_teamspeak_'.$language);
            if(is_null($teamspeak)) {
                $teamspeak = teamspeakViewer();
                $cache->set('nav_teamspeak_'.$language, $teamspeak, config('cache_teamspeak'));
            }
        } else {
            $teamspeak = '<br /><center>'._no_ts.'</center><br />';
        }
    }

    return $teamspeak;
}