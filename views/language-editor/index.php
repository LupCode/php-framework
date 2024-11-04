<?php

    $selectedLang = isset($_GET['l']) ? strtolower($_GET['l']) : DEFAULT_LANGUAGE_CODE;

    // save updated translations
    if(isset($_POST['save'])){
        $json = array();
        foreach($_POST as $k=>$v){
            if(substr($k, 0, 4) !== 'txt-') continue;
            $json[substr($k, 4)] = $v;
        }
        ksort($json); // sort by key
        $saveJson = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if($saveJson) file_put_contents(TRANSLATIONS.$selectedLang.'.json', $saveJson);
    }

    // create new language
    else if(isset($_POST['new-lang']) && !empty($_POST['new-lang'])){
        $lang = strtolower($_POST['new-lang']);
        $LANG = strtoupper($lang);
        $SEL_LANG = strtoupper($selectedLang); $SEL_LANG_LEN = strlen($SEL_LANG);
        if($selectedLang === 'globals') $selectedLang = DEFAULT_LANGUAGE_CODE;
        // duplicate language file
        $content = file_get_contents(TRANSLATIONS.$selectedLang.'.json');
        if($content) file_put_contents(TRANSLATIONS.$lang.'.json', $content);
        // update globals.json
        $inputJson = json_decode(file_get_contents(TRANSLATIONS.'globals.json'), true);
        if($inputJson){
            $outputJson = array();
            foreach($inputJson as $k=>$v){
                $outputJson[$k] = $v;
                if(str_ends_with($k, $SEL_LANG)){
                    $nk = substr($k, 0, -$SEL_LANG_LEN).$LANG;
                    if(!array_key_exists($nk, $inputJson)) $outputJson[$nk] = $v;
                }
            }
            ksort($outputJson); // sort by key
            $saveJson = json_encode($outputJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if($saveJson) file_put_contents(TRANSLATIONS.'globals.json', $saveJson);
        }
        header("Location: ?l=".$_POST['new-lang']); exit(0);
    }

    // delete language
    else if(isset($_POST['delete'])){
        if($selectedLang !== 'globals'){
            // delete language file
            unlink(TRANSLATIONS.$selectedLang.'.json');
            // update globals.json
            $SEL_LANG = strtoupper($selectedLang);
            $inputJson = json_decode(file_get_contents(TRANSLATIONS.'globals.json'), true);
            if($inputJson){
                $outputJson = array();
                foreach($inputJson as $k=>$v) if(!str_ends_with($k, $SEL_LANG)) $outputJson[$k] = $v;
                ksort($outputJson); // sort by key
                $saveJson = json_encode($outputJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if($saveJson) file_put_contents(TRANSLATIONS.'globals.json', $saveJson);
            }
        }
        header("Location: ?"); exit(0);
    }

?><!DOCTYPE html>
<?php

echo '<script type="text/javascript">';
echo    'const TEXT_NOT_SAVED_YET="'.TEXT['NotSavedYet'].'";';
echo '</script>';

?>
<script>
    let PREVENT_CLOSING = false;

    // called when tab should get closed
    window.addEventListener("beforeunload", function(event){
        if(PREVENT_CLOSING){
            event.preventDefault();
            return event.returnValue = PREVENT_CLOSING;
        }
    });

    // check if 'STRG + S' gets clicked
    window.addEventListener('keydown', function(event){
        if(event.ctrlKey && event.code === "KeyS"){
            let but = document.getElementById("save");
            if(but){
                event.preventDefault();
                but.click();
                return false;
            }
        }
    });

    function languageEditorChanged(){
        PREVENT_CLOSING = true;
        let select = document.getElementById("select");
        if(select) select.disabled = true;
        let save = document.getElementById("save");
        if(save) save.disabled = false;
        let cancel = document.getElementById("cancel");
        if(cancel) cancel.disabled = false;
        let del = document.getElementById("delete");
        if(del) del.disabled = true;
        let newLang = document.getElementById("new-lang");
        if(newLang){ newLang.disabled = true; newLang.value=""; }
        let create = document.getElementById("create");
        if(create) create.disabled = true;
    }

    function languageEditorUnchanged(){
        PREVENT_CLOSING = false;
        let select = document.getElementById("select");
        if(select) select.disabled = false;
        let save = document.getElementById("save");
        if(save) save.disabled = true;
        let cancel = document.getElementById("cancel");
        if(cancel) cancel.disabled = true;
        let del = document.getElementById("delete");
        if(del) del.disabled = false;
        let newLang = document.getElementById("new-lang");
        if(newLang) newLang.disabled = false; 
        let create = document.getElementById("create");
        if(create) create.disabled = false;
        let form = document.getElementById("form");
        if(form) form.reset();
    }
</script>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>
        <title><?php echo TEXT['pageLanguageEditorTitle']; ?></title>
        <?php include(SCRIPTS.'metatags.php'); ?>
        <meta name="robots" content="noindex">
    </head>
    <body style="font-family:sans-serif">
        <form id="form" method="POST"></form>
        <h2><?php echo TEXT['SelectedLanguage']; ?>: <select id="select" onchange="window.location.href='?l='+this.value;"><?php
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
        
            echo '</select>'.($currentShowWarning ? '<span class="warning-sign" title="'.TEXT['SomeFileSomethingMissing'].'">!</span>' : '');
        ?>
            <button type="submit" id="save" name="save" form="form" onclick="PREVENT_CLOSING = undefined;" disabled><?php echo TEXT['Save']; ?></button>
            <button type="reset" id="cancel" form="form" disabled onclick="languageEditorUnchanged();"><?php echo TEXT['Cancel']; ?></button>
            <button type="submit" id="delete" name="delete" form="form" onclick="languageEditorUnchanged();" style="color:#c00"><?php echo TEXT['Delete']; ?></button>
        </h2>
        <i><?php echo TEXT['SelectLanguageAndEditText']; ?></i>
        <table>
            <tr><th><?php echo TEXT['Key']; ?></th><th><?php echo TEXT['Text']; ?></th></tr>
            <?php

                $vals = $values[$selectedLang];
                if(!is_array($vals)) $vals = array();
                if($selectedLang === 'globals') $keys = is_array($vals) ? array_keys($vals) : array();

                foreach($keys as $k){
                    $v = (array_key_exists($k, $vals) ? $vals[$k] : '');
                    echo '<tr><td'.(empty($v) ? ' style="color:#d00"' : '').'><span>'.$k.'</span></td>';
                    echo '<td><textarea name="txt-'.$k.'" form="form" onkeyup="languageEditorChanged();">'.$v.'</textarea></td></tr>';
                }
            
            ?>
        </table>
        <br /><br /><br />
        <h2><?php echo TEXT['CreateNewLanguage']; ?>: 
            <input id="new-lang" type="text" name="new-lang" form="form" placeholder="<?php echo TEXT['NewLanguageCode']; ?>" size="14"></input>
            <button id="create" type="submit" form="form"><?php echo TEXT['Create']; ?></button>
        </h2>
        <i><?php echo TEXT['CreatesNewLanguageByCloning']; ?></i>
    </body>
</html>
<style>
    h2 select, h2 input {
        margin-left: 0.5em;
        font-size: 0.95em;
    }
    select {
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
