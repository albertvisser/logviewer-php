<html>
<head>
    <title>Albert's Simple Log Viewer</title>
    <script type="text/javascript">
        function settop(x) {
            document.getElementById('top_line').value = x;
        }
    </script>
    <?php
    include('readlog.php');
    // lees de input variabelen
    // print_r($_POST);
    $logdir = '/var/log/nginx';
    if (array_key_exists('logfile',$_POST))
        $_logfile = $_POST["logfile"];
    else
        $_logfile = '';
    if (array_key_exists('entries',$_POST))
        $_entries= $_POST["entries"];
    else
        $_entries= '10';
    if (array_key_exists('current',$_POST))
        $_first = $_POST["current"];
    else
        $_first = '0';
    if (array_key_exists('total',$_POST))
        $_total = $_POST["total"];
    else
        $_total = '0';
    if (array_key_exists('top_line',$_POST))
        $_next = $_POST["top_line"];
    else
        $_next = 'refresh';
    if (array_key_exists('order',$_POST))
        $_order = $_POST["order"];
    else
        $_order = 'desc';
    $numentries = array('5','10','15','20','25','30');
    $files = array();
    $times = array();
    foreach (glob($logdir . '/*.log') as $name) {
            $files[] = basename($name);
            $times[] = filemtime($name);
    }
    array_multisort($times, SORT_DESC, $files);
    $iserrorlog = strpos($_logfile,'error');
    if (substr($_logfile, 0, 5) == 'error')
        $iserrorlog = true;
    ?>
</head>
<body style="color: white; background-color: black">
    <!-- <div style="colour: black; background-color: orange"><h2>Albert's Simple Log Viewer</h2></div> -->
    <form action="viewlogs.php" method='post'>
    <div>
        <span style="float: left; width:20%">Kies een log bestand:</span>
        <span>
            <select name="logfile" id="logfile" onchange="settop('refresh');submit()"><?php
            foreach ($files as $entry) {
            if (! is_dir($entry)) {
                ?><option
                <?php if ($entry == $_logfile) echo ' selected="selected"'; ?>
                >
                <?php echo $entry; ?></option><?php
            }} ?>
            </select>
        </span>
        <span>
            <input type="submit" value="verversen" onclick="settop('refresh');return true"/>
            <input type="button" value="begin" onclick="settop('first');submit();return true"/>
            <input type="button" value="vorige" onclick="settop('prev');submit();return true"/>
            <input type="button" value="volgende" onclick="settop('next');submit();return true"/>
            <input type="button" value="eind" onclick="settop('last');submit();return true"/>
        </span>
    </div>
    <div>
        <span style="float: left; width:20%">Aantal entries per pagina:</span>
        <span>
            <select name="entries" id="entries" onchange="submit()"><?php
            foreach ($numentries as $num) {
                ?>
                <option
                <?php if ($num == $_entries) echo ' selected="selected"'; ?>
                >
                <?php echo $num; ?></option><?php
            }?>
            </select>
        </span>
        <span style="padding-left: 212px">Volgorde:</span>
        <span>
            <input type="radio" name="order" value="desc" onclick="settop('refresh');submit()" <?php
            if ($_order == "desc") echo 'checked="checked"';?>/>&nbsp;nieuwste eerst&nbsp;
            <input type="radio" name="order" value="asc" onclick="settop('refresh');submit()" <?php
            if ($_order == "asc") echo 'checked="checked"';?>/>&nbsp;nieuwste laatst&nbsp;
        </span>
    </div><?php
    if ($_logfile != '') {
        $f = $logdir.'/'.$_logfile;
        if ($_next == 'refresh') {
           $_total = rereadlog($f, $_order);
           print_r($_total); echo "\n";
           $_next = 'first';
        }
    }
    $mld = '';
    switch ($_next) {
        case 'first':
            $_current = 1;
            break;
        case 'next':
            $newtop += $entries;
            if ($newtop <= $total)
                $_current = $newtop;
            else
                $mld = 'Geen volgende pagina';
            break;
        case 'prev':
            $newtop -= $_entries;
            if ($newtop > 0)
                $_current = $newtop;
            else
                $mld = 'Geen vorige pagina';
            break;
        case 'last':
            $current = ($total / $_entries - 1) * $_entries;
            break;
    }
    ?><div style="text-align: center"><?php echo($mld) ?>&nbsp;</div>
    <div>
        <table>
            <tr>
                <th>When</th><th>What</th><th>Where</th>
            </tr><?php
        if ($_logfile != '')
            $number = readlines($_current, $_entries);
        else
            $number = 0;
        while ($number <= $_entries) {
            if ($iserrorlog) {
            ?><tr><td><textarea style="font-size: 8pt" rows="4" cols="25"></textarea></td><td><textarea style="font-size: 8pt" rows="4" cols="70"></textarea></td><td><textarea style="font-size: 8pt" rows="4" cols="35"></textarea></td></tr><?php
            }
            else {
            ?><tr><td><textarea rows="2" cols="25"></textarea></td><td><textarea rows="2" cols="60"></textarea></td><td><textarea rows="2" cols="25"></textarea></td></tr><?php
            }
            $number++;
        }
        ?></table>
    </div>
    <div>
        <input type="hidden" name="current"  id="current" value="?php echo $_current ?>"/>
        <input type="hidden" name="total"      id="total"      value="?php echo $_total ?>"/>
        <input type="hidden" name="top_line" id="top_line" value="current"/>
    </div>
    </form>
</body>
</html>
