<?php
$db = new SQLite3('./private/lfbiby4o870rv83yr.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

function insertURL($lurl,$db){
        $url = NULL;
        $exists = getURL($lurl,$db);
        if ($exists){
                return $exists[0];
        }else{
                $sql = "INSERT INTO links VALUES (:lurl)";
                $statement = $db->prepare($sql);
                $statement->bindValue(':lurl', $lurl);
                $statement->execute();
                return $db->lastInsertRowid();
        }
}

function getURL($lurl,$db){
        $SQL = "SELECT rowid,lurl from links where lurl = :lurl";
        $statement = $db->prepare($SQL);
        $statement->bindValue(':lurl',$lurl);
        $value = $statement->execute();
        $url = array();
        $url = $value->fetchArray(SQLITE3_NUM);
        $value->finalize();
        if (is_null($url) === 0){
                $url = array_map('htmlspecialchars', $url, array_fill(0, count($url), ENT_QUOTES));
        }
        return $url;
}

function gotoURL($rowid,$db){
        $SQL = "SELECT rowid,lurl from links where rowid = :rowid";
        $statement = $db->prepare($SQL);
        $statement->bindValue(':rowid',$rowid);
        $value = $statement->execute();
        $url = $value->fetchArray(SQLITE3_NUM);
        $value->finalize();
        return $url;
}

if(isset($_POST["url"]) && !empty($_POST["url"]) OR (isset($_GET["url"]) && !empty($_GET["url"]))){

        if (isset($_GET['url'])){
                $url  = $_GET["url"];
        }else{
                $url  = $_POST["url"];
        }
        if ($url[0] == ">"){
                $slug = explode(">", $url);
                $eurl = gotoURL(end($slug),$db);
                $loc = 'Location: ' . (string)$eurl[1];
                if ($loc){
                        header($loc);
                        exit();
                } else {
                        # But we didnt
                        echo "No Long URL Found for Short URL, sad times";
                }
        }
        elseif (is_numeric($url)){
                $eurl = gotoURL($url,$db);
                $loc = 'Location: ' . (string)$eurl[1];
                if ($loc){
                        header($loc);
                        exit();
                } else {
                        # But we didnt
                        echo "No Long URL Found for Short URL, sad times";
                }
        } elseif ($url[0] == "@") {
                $slug = explode("@", $url);
                $loc = 'Location: https://twitter.com/' . (string)end($slug);
                header($loc);
                exit();
        } elseif  ($url[0] == "~"){
                $slug = explode("~", $url);
                $eurl = gotoURL(end($slug),$db);
                # https://stackoverflow.com/questions/19047119/href-security-prevent-xss-attack/19047533
                $allowed = ['http', 'https','ftp'];
                $scheme = parse_url($eurl[1], PHP_URL_SCHEME);
                if ($scheme === false) {
                        echo "You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'WHERE URL = $1' and status=1 ORDER BY pref ASC' at line 1";
                        exit();
                }
                else if (!in_array($scheme, $allowed, true)) {
                        echo "You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'WHERE URL = $1' and status=1 ORDER BY pref ASC' at line 1";
                        exit();
                }
                else {
                        # nothing to do as we are allowed to run 
                }
                if ($eurl){
                        echo "<a href ='".htmlspecialchars($eurl[1])."'>Non Auto Redirecting Link</a>";
                        echo "<p> Raw Link text : ".htmlspecialchars($eurl[1])."</p>";
                        exit();
                } else {
                        # But we didnt
                        echo "No Long URL Found for Short URL, sad times";
                }
        } elseif  ($url[0] == "+"){
                # But we didnt
                echo "No Long URL Found for Short URL, sad times";
        } elseif  (($url[0] == "'") and isset($url[1]) and ($url[1] == "'")){
                echo "No Long URL Found for Short URL, sad times";
        } elseif  ($url[0] == "'"){
                echo "You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'WHERE URL = $1' and status=1 ORDER BY pref ASC' at line 1";
        }else{
                # Not us
                $iurl = insertURL($url,$db);
                if ($iurl){
                        echo $iurl;
                }else{
                        echo "ERROR : Unable to insert URL!";
                }
        }
}else{
        echo '
                <form action="/3/index.php" method="post">
                        <input name="url" type="text">
                        <input type="submit" value="Submit">
                </form>
        ' ;
}

?>
