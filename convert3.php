<?php
/**
 * Convert database schema in an Excel file to MySQL query
 *
 * @author     Komputronika <infokomputronika@gmail.com>
 * @link       https://github.com/komputronika/ExcelToSQL
 *
 */

require './library/excel-reader/php-excel-reader/excel_reader2.php';
require './library/excel-reader/SpreadsheetReader.php';

set_time_limit(0);
error_reporting(0 & ~E_WARNING & ~E_STRICT & ~E_NOTICE);
//error_reporting(E_ALL);



$errors = array();

if (isset($_FILES['file']))
{

    $file_name = $_FILES['file']['name'];
    $file_size = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = $_FILES['file']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['file']['name'])));

    $extensions = array("xls", "xlsx");

    if (in_array($file_ext, $extensions) === false)
    {
        $errors[] = "Extension not allowed, please choose an <b>.xls</b> or <b>.xlsx</b> file.";
    }

    if (empty($errors) == true)
    {
        move_uploaded_file($file_tmp, "./upload/" . $file_name);

    }
    else
    {
        foreach ($errors as $err)
        {
            print "$err<br>";
        }
        die();
    }

}
else
{

    die("Please choose Excel file first (.xls/.xlsx).");
}

$use_moment = $_POST['moment'];
$id_type = $_POST['idcol'];
$generate_fk = $_POST['fk'];
$insert_data = $_POST['insert'];
$drop_db = strval($_POST['drop']);

$buff = new SpreadsheetReader("./upload/" . $file_name);

$tbl = "";
$Sheets = $buff->Sheets();
reset($Sheets);
foreach ($Sheets as $Index => $Name)
{

    if (strtolower($Name) == "database" ||
        strtolower($Name) == "@@@" ||
        strtolower($Name) == "catatan" ||
        strtolower($Name) == "notes" ||
        strtolower($Name) == "memo")
    {
        continue;
    }

    $tbl .= '# -----------------------------' . PHP_EOL;
    $tbl .= '# DATA: ' . $Name . PHP_EOL;
    $tbl .= '# -----------------------------' . PHP_EOL;

    $buff->ChangeSheet($Index);

    $n = 0;
    $col = array();
    $passcol = -1;
    $cntr = 0;
    foreach ($buff as $Key => $Row)
    {
        $n++;
        if ($n == 1)
        {
            $col = $Row;

            $cn = "";
            $ccc = 0;
            foreach ($col as $c)
            {
                $c = trim($c);
                $cn .= "`$c`, ";
                if ($c == 'password')
                {
                    $passcol = $ccc;
                }

                $ccc++;
            }
            $cn = substr($cn, 0, -2);
            continue;
        }

        $val = "";
        $ccc = 0;
        foreach ($Row as $v)
        {
            $sf = trim(strtolower($v));
            $v = "'$v'";
            if ($sf == '@id')
            {

                if ($id_type == 'uuid')
                {
                    $v = str_replace("-", "", UUIDv4());

                    //$v = $cntr;

                    $v = "'" . UUIDv4() . "'";
                    //$v = " UNHEX('$v')";
                    //$v = " CONV( REPLACE('$v', '-', ''), 16, 2) ";

                }
                else
                {
                    $v = $cntr;
                }

            }
            if ($sf == '@date')
            {
                $v = "'" . date("Y-m-d") . "'";}
            if ($sf == '@datetime')
            {
                $v = "'" . date("Y-m-d H:i:s") . "'";}
            if ($sf == '@rd')
            {
                $tt = intval(date("Y"));
                $yy = ($tt - 10) - rand(6, 9);
                $mm = rand(1, 12);
                $dd = rand(1, 28);

                $v = "'" . date("$yy-$mm-$dd") . "'";
            }
            //if ($ccc == $passcol) { $v = md5($v); }

            $val .= "$v, ";
            $ccc++;

        }
        $val = substr($val, 0, -2);
        $tbl .= "INSERT INTO `$Name` ($cn) ";
        $tbl .= "VALUES($val);" . PHP_EOL;
        $cntr++;
    }

    $tbl .= '# -----------------------------' . PHP_EOL . PHP_EOL;
}


$Sheets = $buff -> Sheets();

$dbsheet = 0;
foreach ($Sheets as $Index => $Name)
{
    if (strtolower($name)=="database") {
        $dbsheet=$Index;
        exit;
    }
}

$buff->ChangeSheet($dbsheet);
$data = array();
$i = 0;
foreach ($buff as $a)
{
    $i++;
    if ($i == 1)
    {
        $dbname = $a[0] . "_" . date("ymd");
        $engine = $a[1];
        $charset = $a[2];
        continue;
    }

    // Skip header rows in Excel file
    if ($i < 3)
    {
        continue;
    }

    $table_name = trim($a[0]);
    if (
        !empty($table_name) and
        substr($table_name,0,1)!='#'
       )
    {
        $cb = 0;
        for ($i = 1; $i <= 8; $i++)
        {
            if (empty($a[$i]))
            {
                $cb++;
            }

        }
        // print("<pre>");
        // print_r($a);
        if ($cb < 8)
        {
            $data["$table_name"][] = array("colname" => trim($a[1]),
            //$data["$table_name"][] = array("colname" => trim($a[1]),
                "comment" => trim(str_replace('"', '', $a[2])),
                "type" => trim($a[3]),
                "size" => str_replace('* ', '', trim($a[4])),
                "notnull" => trim($a[5]),
                "default" => trim($a[6]),
                "ai" => trim($a[7]),
                "start" => trim($a[8]),
                "primary" => trim($a[9]),
                "key" => trim($a[10]),
                "fkref" => trim($a[11]),
                "fkcol" => trim($a[12]),
            );

            //$data["$table_name"]["desc"] = trim($a[7]);

        }
        /*if ($cb<8) {

    // [0] => item
    // [1] => branch_id
    // [2] => Branch that owns
    // [3] => int
    // [4] =>
    // [5] => yes
    // [6] =>
    // [7] => yes
    // [8] =>
    // [9] =>
    // [10] => * 100.00
    // [11] => yes
    // [12] => branch
    // [13] => branch_id

    $data["$table_name"][] = array("colname" => trim($a[1]),
    "comment" => trim(str_replace('"','',$a[2])),
    "type"    => trim($a[3]),
    "size"    => str_replace('* ','',trim($a[4])),
    "notnull" => trim($a[5]),
    "default" => trim($a[6]),
    "ai"      => trim($a[7]),
    "start"   => trim($a[8]),
    "primary" => trim($a[9]),
    "key"     => trim($a[11]),
    "fkref"   => trim($a[12]),
    "fkcol"   => trim($a[13])
    );
    }*/

    }
}
unset($buff);

$sql  = "# ===============================================================\n";
$sql .= "# DATABASE     : {$dbname}\n";
$sql .= "# GENERATED BY : Excel2SQL\n";
$sql .= "# LINK         : https://github.com/komputronika/ExcelToSQL\n";
$sql .= "# DATE         : " . date("Y-m-d H:i:s") . "\n";
$sql .= "# AUTHOR       : " . $_POST['author'] . "\n";
$sql .= "# ===============================================================\n\n";

$sql .= "# To prevent the real database from being deleted with this query,\n";
$sql .= "# I have added the underscore '_' after the database name. \n";
$sql .= "# You can test this SQL first, then rename it later.\n\n";

//$sql = "SET SQL_MODE  = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql .= "SET time_zone = \"" . $_POST["timezone"] . "\";\n\n";

if ($drop_db == '1')
{
    $sql .= "DROP DATABASE IF EXISTS `{$dbname}`;\n";
    $sql .= "CREATE DATABASE `{$dbname}`;\n";
    $sql .= "USE `{$dbname}`;\n\n";
}

/*$sql.= "/* ============================= ".PHP_EOL;
$sql.= "/* FUNCTION ".PHP_EOL;
$sql.= "/* ============================= ".PHP_EOL;
$sql.= "DELIMITER ;;\n";
$sql.= "CREATE FUNCTION MY_UUID() RETURNS BINARY(16)\n";
$sql.= "BEGIN\n";
$sql.= "    DECLARE bindata BINARY(16);\n";
$sql.= "    SET bindata = UNHEX( REPLACE( UUID() , '-' , '' ) );\n";
$sql.= "RETURN bindata;\n";

$sql.= "END\n";
$sql.= ";;\n\n\n";*/

$col_create = array("colname" => "created_at",
    "comment" => "Waktu insert @create", // Jangan hapus @create
    "type" => "datetime",
    "size" => "",
    "notnull" => "yes",
    "default" => "",
    "ai" => "",
    "start" => "",
    "primary" => "",
    "key" => "yes",
    "fkref]" => "");

$col_update = array("colname" => "updated_at",
    "comment" => "Waktu update @update", // Jangan hapus @update, ini penting
    "type" => "timestamp",
    "size" => "",
    "notnull" => "yes",
    "default" => "",
    "ai" => "",
    "start" => "",
    "primary" => "",
    "key" => "yes",
    "fkref]" => "");

$col_delete = array("colname" => "deleted_at",
    "comment" => "Waktu delete",
    "type" => "datetime",
    "size" => "",
    "notnull" => "no",
    "default" => "",
    "ai" => "",
    "start" => "",
    "primary" => "",
    "key" => "yes",
    "fkref]" => "");

$col_isdel = array("colname" => "is_deleted",
    "comment" => "Apakah dihapus",
    "type" => "tinyint",
    "size" => "1",
    "notnull" => "yes",
    "default" => "0",
    "ai" => "",
    "start" => "",
    "primary" => "",
    "key" => "yes",
    "fkref]" => "");

$counter = 1;
foreach ($data as $table_name => $col)
//foreach ($data["coldef"] as $table_name => $col)
{


    $table_comment = "";

    if ($_POST["moment"] == "1")
    {
        $col[] = $col_create;
        $col[] = $col_update;
        $col[] = $col_delete;
        $col[] = $col_isdel;
    }

    $sql .= "# =============================\n";
    $sql .= "# ($counter) TABLE: $table_name\n";
    $counter++;
    $sql .= "# =============================\n";
    $sql .= "CREATE TABLE `$table_name` (\n";

    $pk = NULL;
    $start = NULL;
    $key = array();
    $fk = array();

    $nc = 1;
    $total = count($col);
    $id_col = "";
    foreach ($col as $c)
    {

        if ($nc == 1)
        {
            $id_col = $c["colname"];
        }

        $type = strtoupper(trim($c["type"]));

        if ($type == "INT")
        {
            //if ($type=="INT" and empty($c["size"])){

            if (strtolower($c["ai"]) == "yes" and $_POST["idcol"] == "uuid")
            {

                $type = "VARCHAR(36)";

            }
            else
            {

                if (!empty($c["size"]))
                {
                    $type = "INT(" . $c["size"] . ")";
                }
                else
                {
                    $type = "INT";
                }

                if (strtolower($c["ai"]) == "yes" or
                    strtolower($c["key"]) == "yes")
                {
                    $type .= " UNSIGNED";
                }
            }

        }

        if ($type == "TINYINT" and empty($c["size"]))
        {
            $type = "TINYINT(4)";
        }
        if ($type == "TINYINT" and !empty($c["size"]))
        {
            $type = "TINYINT(" . $c["size"] . ")";
        }

        //--- 12/02/2021
        if ($type == "DEC" and !empty($c["size"]))
        {
            //$c["size"] = str_replace("/",",",$c["size"]);
            $type = "DEC(" . $c["size"] . ")";
        }



        $at = array("CHAR", "VARCHAR");

        if (in_array($type, $at))
        {
            if (!empty($c["size"]))
            {
                $type = "$type(" . $c["size"] . ")";
            }
            else
            {
                print "</pre><div style='font-family:arial;'>
                       Tabel: <b>$table_name</b><br>
                       Kolom: <b>" . $c["colname"] . "</b>
                       <P style='color:red !important; font-weight:bold !important'>
                       UKURAN VARCHAR BELUM DITENTUKAN!</P></div>";
                die(0);
            }
        }

        if ($type == "ENUM" and !empty($c["size"]))
        {
            $enum_val = explode(",", $c["size"]);
            $enum_str = "";
            foreach ($enum_val as $val)
            {
                $enum_str .= "'$val',";
            }
            $type = "ENUM(" . substr($enum_str, 0, -1) . ")";
        }

        $comment = NULL;
        if (!empty($c["comment"]))
        {
            $comment = "COMMENT '" . $c["comment"] . "'";
        }

        $comma = ($nc < $total ? NULL : ",");

        if (strtolower($c["notnull"]) == "yes")
        {
            $not_null = "NOT NULL";
        }
        else
        {
            //$not_null = "NULL";
            $not_null = "";
        }

        //if (!empty($c["default"])){
        if ($c["default"] != "")
        {
            if (substr($c["default"], 0, 1) == "@")
            {
                $default = "DEFAULT " . substr($c["default"], 1);
            }
            else
            {
                $default = "DEFAULT '" . $c["default"] . "'";
                //$not_null = "NOT NULL";
            }
        }
        else
        {
            $default = NULL;
        }

        if (strtolower($c["ai"]) == "yes")
        {

            if ($_POST["idcol"] == "ai")
            {
                $ai = "AUTO_INCREMENT";
                $start = $c["start"];
            }

            $pk = "PRIMARY KEY (`" . $c["colname"] . "`)";

            $table_comment = $c["comment"];

        }
        else
        {
            $ai = NULL;
        }

        if (strtolower($c["key"]) == "yes")
        {
            $key[] = "INDEX idx_" . $c["colname"] . " (`" . $c["colname"] . "`)";
            $not_null = "NOT NULL";
        }

        if (!empty($c["fkref"]))
        {
            $refname = $c["fkref"];
            $fk[] = //"CONSTRAINT const_{$table_name}_$refname ".
            "CONSTRAINT `FK_" . $c["colname"] . "` " .
            //"FOREIGN KEY fk_{$table_name}_$refname(".$c["colname"].") ".

            "FOREIGN KEY fk_{$table_name}(`" . $c["colname"] . "`) " .
            //"FOREIGN KEY `" . $c["colname"] . "` " .


            //"FOREIGN KEY (".$c["colname"].") ".
            "REFERENCES $refname(`" . $c["fkcol"] . "`)";
            //"REFERENCES $refname(`".$c["colname"]."`)";
            //FOREIGN KEY fk_category(category_id) REFERENCES categories(category_id)
            //CONSTRAINT `FK_id` FOREIGN KEY (`id`) REFERENCES `table-A` (`id`)

            $fk = array();

            /* ALTER TABLE Orders
            ADD CONSTRAINT FK_PersonOrder
            FOREIGN KEY (PersonID) REFERENCES Persons(PersonID); */

            $a = array();
            $a["table"] = $table_name;
            $a["refname"] = $refname;
            $a["fkcol"] = $c["fkcol"];
            $a["colname"] = $c["colname"];

            $alter_fk[] = $a;

        }

        if (strtolower($c["primary"]) == "yes" and empty($pk))
        {
            $pk = "PRIMARY KEY (`" . $c["colname"] . "`)";
            $not_null = "NOT NULL";
        }

        $comma = NULL;

        if ($nc < $total or !empty($pk))
        {
            $comma = ",";
        }

        //$sql.= trim("'".$c['colname']."' $type $comment $not_null $default $ai $comma")."\n";
        $sql .= "`" . $c['colname'] . "` $type";
        if (!empty($not_null))
        {
            $sql .= " " . $not_null;
        }

        preg_match('/@update/', $comment, $result);
        if ($result[0] == "@update")
        {
            $sql .= " DEFAULT NOW() ON UPDATE NOW()";
        }

        preg_match('/@create/', $comment, $result);
        if ($result[0] == "@create")
        {
            $sql .= " DEFAULT NOW()";
        }

        if (!empty($default))
        {
            $sql .= " " . $default;
        }

        if (!empty($ai))
        {
            $sql .= " " . $ai;
        }

        if (!empty($comment))
        {
            $sql .= " " . $comment;
        }

        if (!empty($comma))
        {
            $sql .= $comma;
        }

        $sql .= "\n";

        $nc++;
    }
    $sql = substr($sql, 0, -1);

    if (!empty($pk))
    {
        $sql .= "\n" . $pk;
    }

    if ($totkey = count($key))
    {
        //if (empty($pk)) {
        $sql .= ",\n";
        //}
        $nk = 1;
        foreach ($key as $k)
        {
            $sql .= $k;
            if ($nk < $totkey)
            {
                $sql .= ",\n";
            }

            $nk++;
        }
    }

    if ($totfk = count($fk))
    {
        //if (empty($pk)) {
        $sql .= ",\n";
        //}
        $nk = 1;
        foreach ($fk as $k)
        {
            $sql .= $k;
            if ($nk < $totfk)
            {
                $sql .= ",\n";
            }

            $nk++;
        }
    }

    $sql .= "\n) ENGINE=" . trim($engine);
    if (!empty($start))
    {
        $sql .= " AUTO_INCREMENT=$start";
    }

    
    $sql .= " DEFAULT CHARSET=" . trim($charset);
    $sql .= "\nCOMMENT='".$table_comment."'";

    //============================
    // Partition 
    //============================

    // $sql .= "\nPARTITION BY HASH($id_col)\n";
    // $sql .= "PARTITIONS 20";

    $sql .= ";\n";


    // $sql .= "\nPARTITION BY RANGE (".$id_col.")\n";
    // $sql .= "(\n";

    // $base = 5000000;
    // $partition="";
    // for($i=1;$i<=15;$i++) {
    //     $partition .= "  PARTITION p$i VALUES LESS THAN (".$i*$base."),\n";
    // }    
    // $sql .= substr($partition,0,-2)."\n";
    // $sql .= ");\n\n";

    // End Partition

    if ($_POST["idcol"] == "uuid")
    {
        $sql .= "# === TRIGGER =================\n";
        $sql .= "DELIMITER ;;\n";
        $sql .= "CREATE TRIGGER before_insert_$table_name\n";
        $sql .= "BEFORE INSERT ON `$table_name`\n";
        $sql .= "FOR EACH ROW\n";
        $sql .= "BEGIN\n";
        $sql .= "  IF new.$id_col IS NULL OR new.$id_col='' THEN\n";
        //$sql .= "    SET new.$id_col = UNHEX( REPLACE( UUID() , '-' , '' ) );\n";
        $sql .= "    SET new.$id_col = UUID();\n";
        $sql .= "  END IF;\n";
        $sql .= "END\n";
        $sql .= ";\n\n";
    }
    $sql .= "\n";
}


/* $fk[] = //"CONSTRAINT const_{$table_name}_$refname ".
            "CONSTRAINT `FK_" . $c["colname"] . "` " .
            //"FOREIGN KEY fk_{$table_name}_$refname(".$c["colname"].") ".

            "FOREIGN KEY fk_{$table_name}(`" . $c["colname"] . "`) " .
            //"FOREIGN KEY `" . $c["colname"] . "` " .


            //"FOREIGN KEY (".$c["colname"].") ".
            "REFERENCES $refname(`" . $c["fkcol"] . "`)"; */

if ($generate_fk=='1' and count($alter_fk)) {
    $sql.= "# =============================\n";
    $sql.= "# Alter table for FK\n";
    $sql.= "# =============================\n";

    foreach($alter_fk as $kel => $val) {
        $o = (object) $val;
        $sql.= "ALTER TABLE $o->table\n";
        $sql.= "ADD CONSTRAINT `FK_$o->table"."_$o->colname`\n";
        $sql.= "FOREIGN KEY fk_$o->table(`$o->colname`)\n";
        $sql.= "REFERENCES $o->refname(`$o->fkcol`); \n\n";
    }
}

//$all = $sql . $tbl;
$all = $sql;
if ($insert_data == '1') {
    $all.= $tbl;
}

$all .= "# === END OF SQL ==============";

file_put_contents("$dbname.sql", $all);
echo str_replace("{{content}}", $all, file_get_contents("template.html"));

function UUIDv4()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function UUIDv4_bin()
{

    return hex2bin(str_replace("-", "", UUIDv4()));

}

//--- END OF FILE
