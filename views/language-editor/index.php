<?php

    $selectedLang = isset($_GET['l']) ? $_GET['l'] : DEFAULT_LANGUAGE_CODE;

    if(!empty($_POST)){
        $saveJson = json_encode($_POST);
        if($saveJson){
            file_put_contents(TRANSLATIONS.$selectedLang.'.json', $saveJson);
        }
    }

?><!DOCTYPE html>
<script>
    function changed(){
        let select = document.getElementById("select");
        if(select) select.disabled = true;
        let save = document.getElementById("save");
        if(save) save.disabled = false;
        let cancel = document.getElementById("cancel");
        if(cancel) cancel.disabled = false;
    }
    function unchanged(){
        let select = document.getElementById("select");
        if(select) select.disabled = false;
        let save = document.getElementById("save");
        if(save) save.disabled = true;
        let cancel = document.getElementById("cancel");
        if(cancel) cancel.disabled = true;
        let form = document.getElementById("form");
        if(form) form.reset();
    }
</script>
<html>
    <head>
        <title>Language Editor - PHP Framework by LupCode</title>
        <?php include(SCRIPTS.'metatags.php'); ?>
        <meta name="robots" content="noindex">
    </head>
    <body style="font-family:sans-serif">
        <form id="form" method="POST"></form>
        <h2>Selected Language: <select id="select" onchange="window.location.href='?l='+this.value;"><?php
            $keys = array();
            $values = array(); // lang: array()
            $currentShowWarning = false;

            foreach(scandir(TRANSLATIONS) as $file){
                if($file === '.' || $file === '..' || !str_ends_with($file, '.json')) continue;
                $lang = substr($file, 0, -5);
                
                $json = json_decode(file_get_contents(TRANSLATIONS.$lang.'.json'), true);
                $values[$lang] = $json;
                if($lang !== 'globals' && $json)
                    foreach($json as $k=>$v) if(!in_array($k, $keys)) array_push($keys, $k);
            }

            sort($keys);
            $keysLen = count($keys);

            foreach($values as $lang=>$vals){
                $ok = true;
                if($lang !== 'globals'){
                    $ok = (count($vals) == $keysLen);
                    if($ok) foreach($values[$lang] as $k=>$v) if(!in_array($k, $keys)){ $ok=false; break; }
                }
                $currentShowWarning = $currentShowWarning || !$ok;
                echo '<option value="'.$lang.'"'.($lang===$selectedLang ? ' selected' : '').
                        ($ok ? '' : ' style="color:#d00"').'>'.strtoupper($lang).($ok ? '' : ' /!\\').'</option>';
            }
        
            echo '</select>'.($currentShowWarning ? '<span class="warning-sign" title="In some file(s) is something missing">!</span>' : '');
        ?>
            <button type="submit" id="save" form="form" disabled>Save</button>
            <button type="reset" id="cancel" form="form" disabled onclick="unchanged();">Cancel</button>
        </h2>
        <i>Select a language and edit the text fields. Don't forget to click on 'save'</i>
        <table>
            <tr><th>Key</th><th>Text</th></tr>
            <?php

                $vals = $values[$selectedLang];
                if(!is_array($vals)) $vals = array();
                if($selectedLang === 'globals') $keys = is_array($vals) ? array_keys($vals) : array();

                foreach($keys as $k){
                    $v = (array_key_exists($k, $vals) ? $vals[$k] : '');
                    echo '<tr><td'.(empty($v) ? ' style="color:#d00"' : '').'><span>'.$k.'</span></td>';
                    echo '<td><textarea name="'.$k.'" form="form" onkeyup="changed();">'.$v.'</textarea></td></tr>';
                }
            
            ?>
        </table>
    </body>
</html>
<style>
    select {
        margin-left: 0.5em;
        font-size: 1em;
        cursor: pointer;
    }
    button {
        display: inline-block;
        margin: 0px 0px 0px 1em;
        font-size: 0.85em;
        cursor: pointer;
    }
    i {
        display: inline-block;
        padding: 0px 0.5em 0.75em 0.5em;
        font-size: 0.9em;
    }
    table {
        max-width: 95%;
        font-size: 1.1rem;
        border-collapse: collapse;
    }
    table td, table th {
        padding: 0px;

        border: 1px solid #888;

        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    table td span {
        padding: 0.1em 0.25em;
    }
    textarea {
        width: 100%;
        width: calc(100% - 0.5em);
        min-width: 40ch;
        min-height: 3.5em;
        margin: 0px;
        padding: 0.1em 0.25em;
        font-size: 1em;
        border: none;
        outline: none;
    }
    .warning-sign {
        display: inline-block;
        width: 0px;
        height: 0px;
        margin: 0px 0.25em;
        padding: 0px 0.3em 0px 0px;
        font-size: 1em;
        font-weight: bold;
        color: #fff;
        
        border-left: 0.65em solid transparent;
        border-right: 0.65em solid transparent;
        border-bottom: 1.1em solid #c00;

        cursor: help;
        overflow: visible;
    }
</style>